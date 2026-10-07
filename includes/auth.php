<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Authentification administrateur
 * =============================================================================
 *
 *  Sécurité :
 *    - mot de passe jamais stocké en clair (password_hash / password_verify) ;
 *    - sessions durcies (httponly, samesite, secure si HTTPS) ;
 *    - limitation des tentatives de connexion (bruteforce) ;
 *    - protection CSRF sur le formulaire de connexion ;
 *    - régénération de l'identifiant de session après connexion.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'database.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';

/**
 * Retourne le hash du mot de passe administrateur.
 *
 * Priorité au hash stocké dans .env (ADMIN_PASSWORD_HASH). Si seul
 * ADMIN_PASSWORD est fourni (mot de passe en clair), il est utilisé directement
 * pour la comparaison — à n'utiliser que pour la configuration initiale.
 */
function admin_password_hash(): string
{
    $hash = (string) env('ADMIN_PASSWORD_HASH', '');
    if ($hash !== '') {
        return $hash;
    }
    return '';
}

/**
 * Vérifie un mot de passe saisi contre la configuration.
 */
function admin_check_password(string $password): bool
{
    $hash = admin_password_hash();
    if ($hash !== '' && password_verify($password, $hash)) {
        return true;
    }
    // Repli de configuration initiale : mot de passe en clair dans .env,
    // comparé en temps constant. Il doit être remplacé par un hash ensuite.
    $plain = (string) env('ADMIN_PASSWORD', '');
    if ($plain !== '' && hash_equals($plain, $password)) {
        return true;
    }
    return false;
}

/**
 * Purge les entrées de tentative de connexion anciennes.
 */
function admin_purge_attempts(PDO $pdo): void
{
    $pdo->exec('DELETE FROM login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)');
}

/**
 * Indique si l'IP a dépassé le quota de tentatives échouées (15 / 15 min).
 */
function admin_is_rate_limited(PDO $pdo, string $ipHash): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE ip_hash = :ip AND successful = 0 AND created_at >= (NOW() - INTERVAL 15 MINUTE)'
    );
    $stmt->execute([':ip' => $ipHash]);
    return (int) $stmt->fetchColumn() >= 15;
}

/**
 * Enregistre une tentative de connexion.
 */
function admin_record_attempt(PDO $pdo, string $ipHash, bool $ok): void
{
    $pdo->prepare('INSERT INTO login_attempts (ip_hash, successful) VALUES (:ip, :ok)')
        ->execute([':ip' => $ipHash, ':ok' => $ok ? 1 : 0]);
}

/**
 * Tente la connexion. Retourne un tableau ['ok' => bool, 'error' => string].
 *
 * @return array{ok:bool,error:string}
 */
function admin_login(string $email, string $password): array
{
    start_secure_session();
    $ipHash = hash_ip(client_ip());

    try {
        $pdo = db();
        admin_purge_attempts($pdo);
        if (admin_is_rate_limited($pdo, $ipHash)) {
            return ['ok' => false, 'error' => 'rate'];
        }
    } catch (Throwable $e) {
        log_event('error', 'Admin login: database unavailable during rate check');
        return ['ok' => false, 'error' => 'error'];
    }

    $expectedEmail = (string) env('ADMIN_EMAIL', '');
    $emailOk = $expectedEmail !== '' && hash_equals(strtolower($expectedEmail), strtolower(trim($email)));
    $passwordOk = admin_check_password($password);

    try {
        admin_record_attempt($pdo, $ipHash, $emailOk && $passwordOk);
    } catch (Throwable $e) {
        log_event('error', 'Admin login: could not record attempt');
    }

    if (!$emailOk || !$passwordOk) {
        log_event('warning', 'Admin login failed', ['ip' => $ipHash]);
        return ['ok' => false, 'error' => 'invalid'];
    }

    // Connexion réussie : régénère l'identifiant de session (anti-fixation).
    session_regenerate_id(true);
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_email'] = $expectedEmail;
    $_SESSION['admin_login_time'] = time();
    $_SESSION['admin_last_seen'] = time();

    log_event('info', 'Admin login successful', ['ip' => $ipHash]);
    return ['ok' => true, 'error' => ''];
}

/**
 * Déconnecte l'administrateur et détruit proprement la session.
 */
function admin_logout(): void
{
    start_secure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Indique si l'administrateur est actuellement authentifié.
 */
function admin_logged_in(): bool
{
    start_secure_session();
    if (empty($_SESSION['admin_authenticated'])) {
        return false;
    }
    // Expiration par inactivité.
    $last = (int) ($_SESSION['admin_last_seen'] ?? 0);
    if ($last > 0 && (time() - $last) > SESSION_LIFETIME) {
        admin_logout();
        return false;
    }
    $_SESSION['admin_last_seen'] = time();
    return true;
}

/**
 * Redirige vers la page de connexion si non authentifié.
 */
function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php', true, 303);
        exit;
    }
}
