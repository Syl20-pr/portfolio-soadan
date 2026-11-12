<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Tableau de bord administrateur
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$stats = ['total' => 0, 'new' => 0, 'read' => 0, 'processed' => 0, 'last7' => 0];
$contentStats = ['articles' => 0, 'drafts' => 0, 'projects' => 0, 'photos' => 0, 'pending' => 0, 'cv' => 0];
$recent = [];
$recentArticles = [];
$dbError = '';

try {
  $pdo = db();
  foreach (['total' => '1=1', 'new' => "status='new'", 'read' => "status='read'", 'processed' => "status='processed'"] as $key => $where) {
    $stats[$key] = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE ' . $where)->fetchColumn();
  }
  $stats['last7'] = (int) $pdo->query(
    'SELECT COUNT(*) FROM messages WHERE created_at >= (NOW() - INTERVAL 7 DAY)'
  )->fetchColumn();

  // Statistiques des nouveaux modules de contenu.
  $contentStats['articles'] = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
  $contentStats['drafts']   = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn();
  $contentStats['projects'] = (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status='published'")->fetchColumn();
  $contentStats['photos']   = (int) $pdo->query('SELECT COUNT(*) FROM gallery_photos WHERE approved = 1')->fetchColumn();
  $contentStats['pending']  = (int) $pdo->query('SELECT COUNT(*) FROM gallery_photos WHERE approved = 0')->fetchColumn();
  $contentStats['cv']       = (int) $pdo->query('SELECT COUNT(*) FROM cv_documents WHERE is_active = 1')->fetchColumn();

  $recent = $pdo->query(
    'SELECT id, name, email, subject, language, status, created_at
         FROM messages ORDER BY created_at DESC LIMIT 5'
  )->fetchAll();

  $recentArticles = $pdo->query(
    'SELECT id, title, status, published_at FROM articles ORDER BY updated_at DESC LIMIT 5'
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
  <div class="adm-stat"><strong><?= (int) $stats['new'] ?></strong><span>Nouveaux messages</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['articles'] ?></strong><span>Publications en ligne</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['drafts'] ?></strong><span>Brouillons</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['projects'] ?></strong><span>Projets dynamiques</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['photos'] ?></strong><span>Photos publiées</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['pending'] ?></strong><span>Photos en attente</span></div>
  <div class="adm-stat"><strong><?= (int) $contentStats['cv'] ?></strong><span>CV actifs</span></div>
</div>

<div class="adm-grid2" style="margin-top:18px">
  <div class="adm-card" style="margin-bottom:0">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Dernières publications</h2>
    <?php if (empty($recentArticles)): ?>
      <p class="adm-muted">Aucune publication enregistrée. <a href="articles.php">Créer la première</a>.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Titre</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentArticles as $a): ?>
          <tr>
            <td><?= e(mb_substr((string) $a['title'], 0, 60, 'UTF-8')) ?></td>
            <td><span class="badge badge-<?= $a['status'] === 'published' ? 'processed' : 'read' ?>"><?= e($a['status'] === 'published' ? 'publié' : 'brouillon') ?></span></td>
            <td><a class="adm-btn small" href="articles.php?edit=<?= (int) $a['id'] ?>&action=edit">Éditer</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:12px"><a class="adm-btn secondary" href="articles.php">Gérer les publications</a></p>
    <?php endif; ?>
  </div>
  <div class="adm-card" style="margin-bottom:0">
    <h2 style="margin-top:0;font-size:17px;color:#0a243b">Accès rapides</h2>
    <div class="adm-flex" style="gap:8px">
      <a class="adm-btn" href="articles.php?new=1">+ Publication</a>
      <a class="adm-btn" href="projects.php?new=1">+ Projet</a>
      <a class="adm-btn" href="gallery.php">Galerie</a>
      <a class="adm-btn" href="cv.php">CV</a>
      <a class="adm-btn secondary" href="../index.php" target="_blank" rel="noopener">Voir le site</a>
    </div>
    <p class="adm-help" style="margin-top:12px">Les photos ne sont visibles sur le site public qu'une fois approuvées.</p>
  </div>
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