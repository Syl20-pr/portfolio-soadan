<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Connexion base de données
 * =============================================================================
 *
 *  Connexion PDO partagée (singleton). Stratégie de basculement automatique :
 *    1. Tente MySQL/MariaDB avec les paramètres DB_* du fichier .env.
 *    2. Si MySQL est indisponible ET que DB_SQLITE_FALLBACK=true (ou APP_ENV=development),
 *       bascule automatiquement sur SQLite (database/portfolio_sks.sqlite).
 *
 *  Différences SQLite vs MySQL gérées ici :
 *    - ON DUPLICATE KEY UPDATE → INSERT OR REPLACE / INSERT OR IGNORE dans le code
 *    - INTERVAL x … → les requêtes SQL de rate-limiting utilisent datetime()
 *    - Pas d'AUTO_INCREMENT MySQL → SQLite INTEGER PRIMARY KEY AUTOINCREMENT
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db_compat.php';

/**
 * Retourne la connexion PDO partagée (créée au premier appel).
 * Bascule automatiquement sur SQLite si MySQL est indisponible en développement.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // RAZ de la dernière erreur connue (à chaque nouvelle tentative).
    $GLOBALS['DB_LAST_ERROR'] = null;
    $GLOBALS['DB_LAST_ERROR_ADMIN'] = null;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    // -------------------------------------------------------------------------
    //  1. Tentative MySQL
    // -------------------------------------------------------------------------
    $host    = (string) env('DB_HOST', 'localhost');
    $port    = (string) env('DB_PORT', '3306');
    $name    = (string) env('DB_NAME', '');
    $user    = (string) env('DB_USER', '');
    $pass    = (string) env('DB_PASSWORD', '');
    $charset = (string) env('DB_CHARSET', 'utf8mb4');

    $mysqlConfigured = ($name !== '' && $user !== '');

    if ($mysqlConfigured) {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            return $pdo;
        } catch (PDOException $e) {
            // Message COMPLET pour l'admin, jamais pour le public.
            $GLOBALS['DB_LAST_ERROR'] = 'MySQL (' . $host . ':' . $port . ', base « ' . $name . '») : '
                . $e->getMessage();
            $GLOBALS['DB_LAST_ERROR_ADMIN'] = $GLOBALS['DB_LAST_ERROR']
                . ' — Vérifiez DB_HOST/DB_NAME/DB_USER/DB_PASSWORD dans .env, que MySQL est démarré,'
                . ' que l’utilisateur a les droits sur la base, et que le jeu de caractères est utf8mb4.'
                . ' Chez votre hébergeur : vérifiez que la base n’est pas suspendue (facturation),'
                . ' que l’hôte MySQL distant autorise votre serveur web (liste blanche / "remote MySQL"),'
                . ' et recréez l’utilisateur si nécessaire.';
            log_event('error', 'MySQL unavailable: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    //  2. Basculement SQLite (dev uniquement ou si DB_SQLITE_FALLBACK=true)
    // -------------------------------------------------------------------------
    $sqliteFallback = filter_var(env('DB_SQLITE_FALLBACK', 'false'), FILTER_VALIDATE_BOOLEAN);
    $isDev          = (APP_ENV === 'development' || APP_ENV === 'local');

    if ($sqliteFallback || $isDev) {
        $sqlitePath = ROOT_PATH . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'portfolio_sks.sqlite';

        if (!file_exists($sqlitePath)) {
            $GLOBALS['DB_LAST_ERROR'] = 'Base SQLite introuvable : ' . $sqlitePath;
            $GLOBALS['DB_LAST_ERROR_ADMIN'] = $GLOBALS['DB_LAST_ERROR']
                . ' — Lancez : php database/setup-sqlite.php, ou corrigez MySQL dans .env.';
            log_event('error', 'SQLite fallback: file not found at ' . $sqlitePath);
            throw new RuntimeException('Database unavailable (SQLite file missing).');
        }

        try {
            $pdo = new PDO('sqlite:' . $sqlitePath, '', '', $options);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
            log_event('info', 'Using SQLite fallback database. MySQL: ' . ($GLOBALS['DB_LAST_ERROR'] ?? 'n/a'));
            return $pdo;
        } catch (PDOException $e) {
            $GLOBALS['DB_LAST_ERROR'] = 'SQLite : ' . $e->getMessage();
            $GLOBALS['DB_LAST_ERROR_ADMIN'] = $GLOBALS['DB_LAST_ERROR']
                . ' — Vérifiez les permissions en écriture sur database/.';
            log_event('error', 'SQLite fallback failed: ' . $e->getMessage());
            throw new RuntimeException('Database unavailable (SQLite error).');
        }
    }

    // -------------------------------------------------------------------------
    //  3. Aucune DB disponible
    // -------------------------------------------------------------------------
    $GLOBALS['DB_LAST_ERROR'] = 'MySQL indisponible et repli SQLite désactivé'
        . ' (DB_SQLITE_FALLBACK=false, APP_ENV=' . APP_ENV . ').';
    $GLOBALS['DB_LAST_ERROR_ADMIN'] = $GLOBALS['DB_LAST_ERROR']
        . ' — Mettre DB_SQLITE_FALLBACK=true dans .env, ou réparer MySQL (identifiants, service démarré, droits).';
    log_event('error', 'Database connection failed — MySQL unavailable, SQLite fallback disabled.');
    throw new RuntimeException('Database connection failed.');
}

/**
 * Cause technique du dernier échec de connexion (réservé à l'ADMIN).
 * Jamais affichée aux visiteurs : elle peut contenir des détails d'infrastructure.
 */
function db_last_error(): ?string
{
    return $GLOBALS['DB_LAST_ERROR_ADMIN'] ?? ($GLOBALS['DB_LAST_ERROR'] ?? null);
}

/**
 * Message NEUTRE destiné aux visiteurs (aucun détail technique).
 */
function db_public_error(): string
{
    return 'Le site est temporairement indisponible. Merci de réessayer dans quelques instants.';
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

/**
 * Retourne le driver actif : 'mysql' | 'sqlite' | null si indisponible.
 */
function db_driver(): ?string
{
    try {
        return db()->getAttribute(PDO::ATTR_DRIVER_NAME);
    } catch (Throwable $e) {
        return null;
    }
}
