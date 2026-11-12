<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : diagnostic de la base de données
 * =============================================================================
 *  Montre la VRAIE cause d'un problème (connexion refusée, base introuvable,
 *  table manquante) à l'administrateur uniquement. Aucune donnée sensible
 *  n'est affichée : le mot de passe est masqué, l'erreur MySQL est rapportée
 *  telle quelle mais uniquement derrière require_admin().
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$rows = [];
$mysqlState = '';
$sqliteState = '';
$recommend = [];

$cfg = [
    'DB_HOST'     => env('DB_HOST', 'localhost'),
    'DB_PORT'     => env('DB_PORT', '3306'),
    'DB_NAME'     => env('DB_NAME', ''),
    'DB_USER'     => env('DB_USER', ''),
    'DB_PASSWORD' => env('DB_PASSWORD', '') !== '' ? '•••••••• (défini)' : '(vide)',
    'DB_CHARSET'  => env('DB_CHARSET', 'utf8mb4'),
];

// 1. Test MySQL réel.
if ($cfg['DB_NAME'] !== '' && $cfg['DB_USER'] !== '') {
    try {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['DB_HOST'], $cfg['DB_PORT'], $cfg['DB_NAME'], $cfg['DB_CHARSET']);
        $t = new PDO($dsn, env('DB_USER', ''), (string) env('DB_PASSWORD', ''), [PDO::ATTR_TIMEOUT => 5]);
        $mysqlState = '✔ Connexion MySQL réussie.';
    } catch (PDOException $ex) {
        $mysqlState = '✘ MySQL : ' . $ex->getMessage();
        $msg = strtolower($ex->getMessage());
        if (str_contains($msg, 'connection refused') || str_contains($msg, 'can\'t connect')) {
            $recommend[] = 'Connexion refusée : le serveur MySQL est-il démarré ? Chez l’hébergeur, DB_HOST n’est généralement PAS localhost — utilisez l’hôte SQL fourni (ex. sql01.ton-hebergeur.tld).';
        } elseif (str_contains($msg, 'access denied')) {
            $recommend[] = 'Accès refusé : DB_USER/DB_PASSWORD incorrects, ou l’utilisateur n’a pas les droits sur la base. Recréez l’utilisateur avec ALL PRIVILEGES sur ' . $cfg['DB_NAME'] . '.*.';
        } elseif (str_contains($msg, 'unknown database')) {
            $recommend[] = 'Base introuvable : importez database/schema.sql puis migration-001, 002, 003, 004 dans phpMyAdmin.';
        }
    }
} else {
    $mysqlState = '— Non configuré (DB_NAME ou DB_USER vide dans .env).';
}

// 2. Test SQLite (repli).
try {
    db_driver();
    $driver = db_driver();
    $sqliteState = $driver === 'sqlite' ? '✔ Repli SQLite actif (le site fonctionne, en dev).' : '✔ Driver actif : ' . $driver;
} catch (Throwable $ex) {
    $sqliteState = '✘ ' . db_last_error() ?? $ex->getMessage();
}

// 3. Tables attendues, dans l'ordre des migrations.
$expected = [
    'schema.sql'     => ['messages', 'contact_rate_limit', 'login_attempts'],
    'migration-001'  => ['admin_users', 'categories', 'articles', 'tags', 'article_tags',
                        'projects', 'gallery_photos', 'cv_documents', 'experiences', 'education',
                        'skills', 'certifications', 'distinctions', 'site_settings'],
    'migration-002'  => ['cv_photo', 'cv_languages', 'cv_associations', 'cv_publications',
                        'cv_sections', 'cv_state', 'cv_personal'],
    'migration-003'  => [],
    'migration-004'  => ['media_images', 'news_items', 'news_comments', 'comment_rate_limit'],
];

try {
    $pdo = db();
    $existing = [];
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) {
        $existing[] = (string) $t;
    }
    foreach ($expected as $mig => $tables) {
        if ($tables === []) {
            continue;
        }
        foreach ($tables as $t) {
            $rows[$mig][$t] = in_array($t, $existing, true);
        }
    }
} catch (Throwable $ex) {
    // SHOW TABLES peut échouer sous SQLite : alternative.
    try {
        $pdo = db();
        $existing = [];
        foreach ($pdo->query('SELECT name FROM sqlite_master WHERE type = \'table\'')->fetchAll(PDO::FETCH_COLUMN) as $t) {
            $existing[] = (string) $t;
        }
        foreach ($expected as $mig => $tables) {
            if ($tables === []) continue;
            foreach ($tables as $t) {
                $rows[$mig][$t] = in_array($t, $existing, true);
            }
        }
    } catch (Throwable $ex2) {
        $rows = [];
    }
}

$lastErr = db_last_error();

admin_header('Base de données', 'db-status');
?>
<?php if ($lastErr): ?>
  <div class="adm-alert err"><strong>Dernière erreur enregistrée :</strong><br><?= e($lastErr) ?></div>
<?php endif; ?>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Configuration (.env)</h2>
  <table class="adm-table">
    <tbody>
      <?php foreach ($cfg as $k => $v): ?>
        <tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Connexions</h2>
  <p><strong>MySQL :</strong> <?= e($mysqlState) ?></p>
  <p><strong>Repli SQLite :</strong> <?= e($sqliteState) ?></p>
  <?php if ($recommend !== []): ?>
    <div class="adm-alert err">
      <strong>Marche à suivre :</strong>
      <ul style="margin:6px 0 0 20px">
        <?php foreach ($recommend as $r): ?><li><?= e($r) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Tables attendues (par migration)</h2>
  <?php if ($rows === []): ?>
    <p class="adm-muted">Impossible de lister les tables (base injoignable). Corrigez d'abord la connexion.</p>
  <?php else: ?>
    <?php foreach ($rows as $mig => $tables): ?>
      <p style="margin:12px 0 4px"><strong><?= e($mig) ?></strong></p>
      <p style="margin:0">
        <?php foreach ($tables as $t => $ok): ?>
          <span class="badge badge-<?= $ok ? 'processed' : 'new' ?>" style="margin:0 4px 4px 0"><?= e($t) ?> <?= $ok ? '✔' : '✘ manquante' ?></span>
        <?php endforeach; ?>
      </p>
    <?php endforeach; ?>
    <p class="adm-help" style="margin-top:10px">Une table marquée « manquante » indique une migration non importée : importez les fichiers database/migration-00X dans l'ordre 001 → 002 → 003 → 004 (tous idempotents).</p>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
