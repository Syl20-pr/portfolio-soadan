<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Traitement du formulaire de contact
 * =============================================================================
 *
 *  Étapes :
 *    1. contrôle de la méthode HTTP (POST uniquement) ;
 *    2. vérification CSRF ;
 *    3. anti-spam (honeypot + délai minimal) ;
 *    4. limitation du nombre de soumissions par IP / heure ;
 *    5. validation + nettoyage serveur ;
 *    6. insertion PDO (requête préparée) ;
 *    7. emails : notification admin + confirmation visiteur.
 *
 *  Le traitement renvoie ensuite le visiteur vers la page de confirmation
 *  existante (thanks.php), en conservant strictement le design d'origine.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'database.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'mailer.php';

start_secure_session();

// -----------------------------------------------------------------------------
//  Langue + préfixe de retour (FR racine / EN sous /en/)
// -----------------------------------------------------------------------------
$lang = normalize_lang($_POST['language'] ?? current_lang());
$isEn = $lang === 'en';
// Ce script vit dans /actions/ : on remonte d'un niveau (et vers /en/ pour l'anglais).
$base = $isEn ? '../en/' : '../';

/**
 * Redirige vers la page de confirmation ou de contact avec un statut.
 */
function respond(string $base, string $lang, string $status): void
{
    $target = $status === 'success'
        ? $base . 'thanks.php'
        : $base . 'contact.php';
    $glue = strpos($target, '?') === false ? '?' : '&';
    $url = $target . $glue . 'lang=' . urlencode($lang);
    if ($status !== 'success') {
        $url .= '&status=' . urlencode($status) . '#contact-form';
    }
    header('Location: ' . $url, true, 303);
    exit;
}

// -----------------------------------------------------------------------------
//  1. Méthode HTTP
// -----------------------------------------------------------------------------
if (!is_post()) {
    respond($base, $lang, 'error');
}

// -----------------------------------------------------------------------------
//  2. CSRF
// -----------------------------------------------------------------------------
if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    log_event('warning', 'Contact form rejected: invalid CSRF token', ['ip' => hash_ip(client_ip())]);
    respond($base, $lang, 'csrf');
}

// -----------------------------------------------------------------------------
//  3. Honeypot + délai minimal (anti-robot discret, invisible pour le visiteur)
// -----------------------------------------------------------------------------
// Le champ « website » est masqué en CSS/JS : un humain ne le remplit jamais.
if (!empty($_POST['website'])) {
    log_event('warning', 'Contact form rejected: honeypot triggered');
    respond($base, $lang, 'spam');
}
// Le champ « form_time » (horodatage d'affichage) permet de rejeter les
// soumissions instantanées typiques des robots.
$formTime = isset($_POST['form_time']) ? (int) $_POST['form_time'] : 0;
if ($formTime <= 0 || (time() - $formTime) < CONTACT_MIN_DELAY) {
    log_event('warning', 'Contact form rejected: submitted too fast');
    respond($base, $lang, 'spam');
}

// -----------------------------------------------------------------------------
//  4. Limitation par IP et par heure
// -----------------------------------------------------------------------------
$ipHash = hash_ip(client_ip());

try {
    $pdo = db();

    // Purge opportuniste des anciennes entrées de limitation.
    $pdo->exec('DELETE FROM contact_rate_limit WHERE created_at < (NOW() - INTERVAL 1 DAY)');

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM contact_rate_limit WHERE ip_hash = :ip AND created_at >= (NOW() - INTERVAL 1 HOUR)'
    );
    $stmt->execute([':ip' => $ipHash]);
    $recent = (int) $stmt->fetchColumn();

    if ($recent >= CONTACT_MAX_PER_HOUR) {
        log_event('warning', 'Contact form rejected: rate limit exceeded', ['ip' => $ipHash]);
        respond($base, $lang, 'rate');
    }

    $pdo->prepare('INSERT INTO contact_rate_limit (ip_hash) VALUES (:ip)')
        ->execute([':ip' => $ipHash]);
} catch (Throwable $e) {
    log_event('error', 'Contact form: database unavailable at rate-limit stage');
    respond($base, $lang, 'error');
}

// -----------------------------------------------------------------------------
//  5. Validation + nettoyage
// -----------------------------------------------------------------------------
$validated = validate_contact_input([
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'subject' => $_POST['subject'] ?? '',
    'message' => $_POST['message'] ?? '',
    'collab_type' => $_POST['collab_type'] ?? '',
    'language' => $lang,
]);

if (!empty($validated['errors'])) {
    log_event('warning', 'Contact form rejected: validation errors', ['fields' => $validated['errors']]);
    respond($base, $lang, 'invalid');
}
$data = $validated['data'];

// -----------------------------------------------------------------------------
//  6. Insertion en base (requêtes préparées)
// -----------------------------------------------------------------------------
$userAgent = isset($_SERVER['HTTP_USER_AGENT'])
    ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255)
    : null;

try {
    $insert = $pdo->prepare(
        'INSERT INTO messages (name, email, phone, subject, message, language, collab_type, ip_hash, user_agent, status)
         VALUES (:name, :email, :phone, :subject, :message, :language, :collab_type, :ip_hash, :user_agent, :status)'
    );
    $insert->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] !== '' ? $data['phone'] : null,
        ':subject' => $data['subject'] !== '' ? $data['subject'] : null,
        ':message' => $data['message'],
        ':language' => $data['language'],
        ':collab_type' => $data['collab_type'] !== '' ? $data['collab_type'] : null,
        ':ip_hash' => $ipHash,
        ':user_agent' => $userAgent,
        ':status' => 'new',
    ]);
} catch (Throwable $e) {
    log_event('error', 'Contact form: insert failed', ['exception' => $e->getMessage()]);
    respond($base, $lang, 'error');
}

// -----------------------------------------------------------------------------
//  7. Emails (notification admin + confirmation visiteur)
// -----------------------------------------------------------------------------
// Un échec d'envoi n'empêche pas la confirmation : le message est déjà en base.
$adminOk = send_admin_notification($data);
$visitorOk = send_visitor_confirmation($data);

if (!$adminOk) {
    log_event('error', 'Contact form: admin notification email failed');
}
if (!$visitorOk) {
    log_event('warning', 'Contact form: visitor confirmation email failed');
}

log_event('info', 'Contact form: message stored', ['lang' => $data['language']]);

respond($base, $lang, 'success');
