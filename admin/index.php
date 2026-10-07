<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Tableau de bord administrateur
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$stats = ['total' => 0, 'new' => 0, 'read' => 0, 'processed' => 0, 'last7' => 0];
$recent = [];
$dbError = '';

try {
  $pdo = db();
  foreach (['total' => '1=1', 'new' => "status='new'", 'read' => "status='read'", 'processed' => "status='processed'"] as $key => $where) {
    $stats[$key] = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE ' . $where)->fetchColumn();
  }
  $stats['last7'] = (int) $pdo->query(
    'SELECT COUNT(*) FROM messages WHERE created_at >= (NOW() - INTERVAL 7 DAY)'
  )->fetchColumn();

  $recent = $pdo->query(
    'SELECT id, name, email, subject, language, status, created_at
         FROM messages ORDER BY created_at DESC LIMIT 8'
  )->fetchAll();
} catch (Throwable $e) {
  log_event('error', 'Admin dashboard: database unavailable');
  $dbError = 'La base de données est temporairement indisponible.';
}

admin_header('Tableau de bord', 'index');
?>
<?php if ($dbError !== ''): ?>
  <div class="adm-alert err"><?= e($dbError) ?></div>
<?php endif; ?>

<div class="adm-stats">
  <div class="adm-stat"><strong><?= (int) $stats['total'] ?></strong><span>Messages au total</span></div>
  <div class="adm-stat"><strong><?= (int) $stats['new'] ?></strong><span>Nouveaux</span></div>
  <div class="adm-stat"><strong><?= (int) $stats['read'] ?></strong><span>Lus</span></div>
  <div class="adm-stat"><strong><?= (int) $stats['processed'] ?></strong><span>Traités</span></div>
  <div class="adm-stat"><strong><?= (int) $stats['last7'] ?></strong><span>7 derniers jours</span></div>
</div>

<div class="adm-card" style="margin-top:18px">
  <h2 style="margin-top:0;font-size:17px;color:#0a243b">Derniers messages</h2>
  <?php if (empty($recent)): ?>
    <p class="adm-muted">Aucun message pour le moment.</p>
  <?php else: ?>
    <table class="adm-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Nom</th>
          <th>Sujet</th>
          <th>Langue</th>
          <th>Statut</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $m): ?>
          <tr>
            <td><?= e($m['created_at']) ?></td>
            <td><?= e($m['name']) ?><br><span class="adm-muted"><?= e($m['email']) ?></span></td>
            <td><?= e($m['subject'] !== null ? $m['subject'] : '—') ?></td>
            <td><?= e(strtoupper($m['language'])) ?></td>
            <td><span class="badge badge-<?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
            <td><a class="adm-btn small" href="messages.php?id=<?= (int) $m['id'] ?>">Voir</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="margin-top:16px"><a class="adm-btn secondary" href="messages.php">Tous les messages</a></p>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>