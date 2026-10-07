<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Configuration applicative
 * =============================================================================
 *
 *  Ce fichier :
 *    1. charge les variables sensibles depuis le fichier « .env » (racine) ;
 *    2. configure le mode production (aucune erreur affichée au visiteur) ;
 *    3. expose des constantes et fonctions utilitaires partagées.
 *
 *  Il ne contient AUCUN identifiant en dur : les secrets restent dans .env,
 *  qui n'est jamais versionné (voir .gitignore) ni exposé (voir .htaccess).
 * =============================================================================
 */

declare(strict_types=1);

// -----------------------------------------------------------------------------
//  1. Chargement du fichier .env (parseur minimal, sans dépendance externe)
// -----------------------------------------------------------------------------
if (!function_exists('load_env')) {
    /**
     * Lit un fichier .env et retourne les paires clé => valeur.
     * Les lignes vides et les commentaires (#) sont ignorés.
     * Les valeurs entre guillemets sont désencadrées.
     */
    function load_env(string $path): array
    {
        $vars = [];
        if (!is_readable($path)) {
            return $vars;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $vars;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            // Supprime un éventuel préfixe "export ".
            if (strpos($line, 'export ') === 0) {
                $line = substr($line, 7);
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Désencadre les valeurs entre guillemets simples ou doubles.
            $len = strlen($value);
            if ($len >= 2) {
                $first = $value[0];
                $last = $value[$len - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, $len - 2);
                }
            }

            $vars[$key] = $value;
        }

        return $vars;
    }
}

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$ENV = load_env($envPath);

/**
 * Retourne une variable d'environnement (fichier .env puis variables système).
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        global $ENV;
        if (array_key_exists($key, $ENV) && $ENV[$key] !== '') {
            return $ENV[$key];
        }
        $system = getenv($key);
        if ($system !== false && $system !== '') {
            return $system;
        }
        return $default;
    }
}

// -----------------------------------------------------------------------------
//  2. Environnement & gestion des erreurs (mode production)
// -----------------------------------------------------------------------------
define('APP_ENV', (string) env('APP_ENV', 'production'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
define('APP_URL', rtrim((string) env('APP_URL', ''), '/'));

define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);
define('LOG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'logs');

// Ne jamais afficher d'erreur au visiteur en production.
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . DIRECTORY_SEPARATOR . 'php-errors.log');

// -----------------------------------------------------------------------------
//  3. Constantes applicatives
// -----------------------------------------------------------------------------
define('CONTACT_MIN_DELAY', (int) env('CONTACT_MIN_DELAY', 3));
define('CONTACT_MAX_PER_HOUR', (int) env('CONTACT_MAX_PER_HOUR', 5));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 3600));
define('APP_KEY', (string) env('APP_KEY', 'insecure-default-key-change-me'));

// Dossier des PDF (CV) — sert au contrôle d'existence côté serveur.
define('CV_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'documents');
define('CV_FILE_FR', 'CV_SKS.pdf');
define('CV_FILE_EN', 'CV_SKS_EN.pdf');

// Charge les fonctions utilitaires partagées.
require_once INCLUDES_PATH . DIRECTORY_SEPARATOR . 'functions.php';
