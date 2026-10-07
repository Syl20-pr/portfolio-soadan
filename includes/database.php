<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Connexion base de données
 * =============================================================================
 *
 *  Fournit une connexion PDO unique (singleton) configurée pour MySQL/MariaDB :
 *    - charset utf8mb4 ;
 *    - mode exception pour les erreurs ;
 *    - émulation des requêtes préparées désactivée (vraies requêtes préparées) ;
 *    - renvoi des colonnes sous forme de tableaux associatifs.
 *
 *  Les identifiants proviennent exclusivement du fichier .env.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

/**
 * Retourne la connexion PDO partagée (créée au premier appel).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string) env('DB_HOST', 'localhost');
    $port = (string) env('DB_PORT', '3306');
    $name = (string) env('DB_NAME', '');
    $user = (string) env('DB_USER', '');
    $pass = (string) env('DB_PASSWORD', '');
    $charset = (string) env('DB_CHARSET', 'utf8mb4');

    if ($name === '' || $user === '') {
        log_event('error', 'Database configuration incomplete (DB_NAME/DB_USER missing).');
        throw new RuntimeException('Database configuration is incomplete.');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // Le détail technique ne doit jamais atteindre le visiteur.
        log_event('error', 'Database connection failed', ['exception' => $e->getMessage()]);
        throw new RuntimeException('Database connection failed.');
    }

    return $pdo;
}

/**
 * Indique si la base de données est joignable (sans exposer d'erreur).
 */
function db_available(): bool
{
    try {
        db();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
