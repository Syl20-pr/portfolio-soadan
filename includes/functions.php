<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Fonctions utilitaires partagées
 * =============================================================================
 *
 *  Sécurité, sessions, CSRF, validation, logging, anti-spam, helpers Vue.
 *  Aucune fonction de ce fichier n'affiche d'information sensible.
 * =============================================================================
 */

declare(strict_types=1);

// -----------------------------------------------------------------------------
//  Journalisation
// -----------------------------------------------------------------------------

/**
 * Écrit une ligne dans le journal applicatif (jamais affichée au visiteur).
 */
function log_event(string $level, string $message, array $context = []): void
{
    if (!is_dir(LOG_PATH)) {
        @mkdir(LOG_PATH, 0775, true);
    }
    $line = sprintf(
        "[%s] %s: %s%s%s",
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $message,
        $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
        PHP_EOL
    );
    @error_log($line, 3, LOG_PATH . DIRECTORY_SEPARATOR . 'app.log');
}

// -----------------------------------------------------------------------------
//  Sécurité générale
// -----------------------------------------------------------------------------

/**
 * Hache une adresse IP avec la clé applicative (jamais d'IP en clair en base).
 */
function hash_ip(string $ip): string
{
    return hash('sha256', $ip . '|' . APP_KEY);
}

/**
 * Retourne l'adresse IP du visiteur (derrière proxy si présent) ou ''.
 */
function client_ip(): string
{
    $candidates = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($candidates as $key) {
        if (!empty($_SERVER[$key])) {
            $value = trim(explode(',', (string) $_SERVER[$key])[0]);
            if (filter_var($value, FILTER_VALIDATE_IP) !== false) {
                return $value;
            }
        }
    }
    return '';
}

/**
 * Échappe une valeur pour un affichage HTML sûr (protection XSS).
 *
 * @param mixed $value
 * @return string
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Indique si la requête courante est un POST.
 */
function is_post(): bool
{
    return isset($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST';
}

/**
 * Détecte si la connexion est sécurisée (HTTPS), derrière proxy inclus.
 */
function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    if (
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
    ) {
        return true;
    }
    return false;
}

// -----------------------------------------------------------------------------
//  Sessions sécurisées
// -----------------------------------------------------------------------------

/**
 * Démarre (une seule fois) une session aux paramètres durcis.
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('SKSSESSID');
    session_start();
}

// -----------------------------------------------------------------------------
//  Protection CSRF
// -----------------------------------------------------------------------------

/**
 * Retourne (en la créant au besoin) la valeur du jeton CSRF courant.
 */
function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Champ caché prêt à insérer dans un formulaire.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Vérifie en temps constant le jeton CSRF reçu.
 *
 * @param mixed $token
 * @return bool
 */
function csrf_verify($token): bool
{
    start_secure_session();
    if (empty($_SESSION['csrf_token']) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// -----------------------------------------------------------------------------
//  Localisation (FR / EN) — s'appuie sur le fonctionnement bilingue existant
// -----------------------------------------------------------------------------

/**
 * Normalise une langue reçue en 'fr' ou 'en' (défaut 'fr').
 *
 * @param mixed $lang
 * @return string
 */
function normalize_lang($lang): string
{
    $lang = is_string($lang) ? strtolower(trim($lang)) : '';
    return $lang === 'en' ? 'en' : 'fr';
}

/**
 * Détecte la langue depuis ?lang=, sinon depuis le chemin /en/, sinon 'fr'.
 */
function current_lang(): string
{
    if (isset($_GET['lang'])) {
        return normalize_lang($_GET['lang']);
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if (preg_match('#(^|/)en(/|$)#', $uri)) {
        return 'en';
    }
    return 'fr';
}

/**
 * Retourne une chaîne traduite selon la langue courante.
 *
 * @param array<string,string> $map
 */
function tr(array $map, string $lang, string $fallback = ''): string
{
    if (isset($map[$lang])) {
        return $map[$lang];
    }
    return $fallback !== '' ? $fallback : (string) reset($map);
}

// -----------------------------------------------------------------------------
//  Validation du formulaire de contact
// -----------------------------------------------------------------------------

/**
 * Valide et nettoie les données du formulaire de contact.
 *
 * @param array<string,mixed> $input
 * @return array{data:array<string,string>,errors:string[]}
 */
function validate_contact_input(array $input): array
{
    $errors = [];
    $data = [];

    // Nettoie les espaces et supprime les caractères de contrôle.
    $clean = static function ($value, int $max): string {
        $value = is_scalar($value) ? (string) $value : '';
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
        $value = trim((string) $value);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max, 'UTF-8');
        }
        return substr($value, 0, $max);
    };

    $data['name'] = $clean($input['name'] ?? '', 160);
    $data['email'] = $clean($input['email'] ?? '', 254);
    $data['phone'] = $clean($input['phone'] ?? '', 40);
    $data['subject'] = $clean($input['subject'] ?? '', 200);
    $data['message'] = $clean($input['message'] ?? '', 5000);
    $data['collab_type'] = $clean($input['collab_type'] ?? '', 80);
    $data['language'] = normalize_lang($input['language'] ?? 'fr');

    // Champs obligatoires.
    if ($data['name'] === '') {
        $errors[] = 'name';
    }
    if ($data['email'] === '' || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors[] = 'email';
    }
    if ($data['message'] === '') {
        $errors[] = 'message';
    }
    // Bornes minimales de contenu (anti-spam grossier).
    if (function_exists('mb_strlen') ? mb_strlen($data['message'], 'UTF-8') < 5 : strlen($data['message']) < 5) {
        $errors[] = 'message';
    }

    return ['data' => $data, 'errors' => array_values(array_unique($errors))];
}

// -----------------------------------------------------------------------------
//  Messages de retour (déjà prévus par le design)
// -----------------------------------------------------------------------------

/**
 * Messages d'état du formulaire, dans les deux langues (design existant).
 *
 * @return array<string,array<string,string>>
 */
function contact_messages(): array
{
    return [
        'success' => [
            'fr' => 'Merci pour votre message. Votre prise de contact a bien été transmise.',
            'en' => 'Thank you for your message. Your enquiry has been sent successfully.',
        ],
        'invalid' => [
            'fr' => 'Certains champs sont invalides. Merci de les corriger puis de réessayer.',
            'en' => 'Some fields are invalid. Please correct them and try again.',
        ],
        'csrf' => [
            'fr' => 'Votre session a expiré. Merci de recharger la page et de réessayer.',
            'en' => 'Your session has expired. Please reload the page and try again.',
        ],
        'spam' => [
            'fr' => 'Votre message a été identifié comme indésirable. Écrivez-moi directement à sylvainsoadan3@gmail.com.',
            'en' => 'Your message was flagged as spam. Please email sylvainsoadan3@gmail.com directly.',
        ],
        'rate' => [
            'fr' => 'Trop de messages envoyés récemment. Merci de réessayer plus tard.',
            'en' => 'Too many messages sent recently. Please try again later.',
        ],
        'error' => [
            'fr' => 'Le message n’a pas pu être envoyé. Écrivez-moi directement à sylvainsoadan3@gmail.com.',
            'en' => 'The message could not be sent. Please email sylvainsoadan3@gmail.com directly.',
        ],
    ];
}

/**
 * Retourne le message correspondant à une clé, dans la langue donnée.
 */
function contact_message(string $key, string $lang): string
{
    $all = contact_messages();
    if (!isset($all[$key])) {
        return '';
    }
    return tr($all[$key], $lang);
}
