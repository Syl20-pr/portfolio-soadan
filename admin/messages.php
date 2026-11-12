<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Gestion des messages
 * =============================================================================
 *  Liste, consultation, recherche, changement de statut et suppression.
 *  Toutes les requêtes utilisent des requêtes préparées PDO.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$flash = '';
$flashType = 'ok';

// -----------------------------------------------------------------------------
//  Traitement des actions POST (statut / suppression) avec CSRF
// -----------------------------------------------------------------------------
if (is_post()) {
  if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    $flash = 'Session expirée. Merci de réessayer.';
    $flashType = 'err';
  } else {
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $allowedStatuses = ['new', 'read', 'processed'];

    try {
      $pdo = db();
      if ($id > 0 && $action === 'status') {
        $newStatus = (string) ($_POST['status'] ?? '');
        if (in_array($newStatus, $allowedStatuses, true)) {
          $pdo->prepare('UPDATE messages SET status = :status WHERE id = :id')
            ->execute([':status' => $newStatus, ':id' => $id]);
          $flash = 'Statut mis à jour.';
        }
      } elseif ($id > 0 && $action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = :id')->execute([':id' => $id]);
        $flash = 'Message supprimé.';
      }
    } catch (Throwable $e) {
      log_event('error', 'Admin messages: action failed', ['action' => $action]);
      $flash = 'L’opération a échoué.';
      $flashType = 'err';
    }
  }

  // Redirection (POST/Redirect/GET) pour éviter la re-soumission.
  $qs = $_SERVER['QUERY_STRING'] ?? '';
  header('Location: messages.php' . ($qs !== '' ? '?' . $qs : ''), true, 303);
  exit;
}

// -----------------------------------------------------------------------------
//  Lecture : détail d'un message OU liste + recherche
// -----------------------------------------------------------------------------
$viewId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$current = null;
$rows = [];
$dbError = '';

try {
  $pdo = db();

  if ($viewId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE id = :id');
    $stmt->execute([':id' => $viewId]);
    $current = $stmt->fetch();

    // Marque automatiquement comme « lu » un message encore nouveau.
    if (is_array($current) && ($current['status'] ?? '') === 'new') {
      $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = :id")->execute([':id' => $viewId]);
      $current['status'] = 'read';
    }
  } else {
    if ($search !== '') {
      $stmt = $pdo->prepare(
        'SELECT id, name, email, subject, language, status, created_at
                 FROM messages
                 WHERE name LIKE :q OR email LIKE :q OR subject LIKE :q OR message LIKE :q
                 ORDER BY created_at DESC LIMIT 200'
      );
      $stmt->execute([':q' => '%' . $search . '%']);
    } else {
      $stmt = $pdo->query(
        'SELECT id, name, email, subject, language, status, created_at
                 FROM messages ORDER BY created_at DESC LIMIT 200'
      );
    }
    $rows = $stmt->fetchAll();
  }
} catch (Throwable $e) {
  log_event('error', 'Admin messages: database unavailable');
  $dbError = 'La base de données est temporairement indisponible.';
}

admin_header('Messages', 'messages');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($dbError !== ''): ?>
  <div class="adm-alert err"><?= e($dbError) ?></div>
<?php endif; ?>

<?php if ($current): ?>
  <div class="adm-card">
    <p><a class="adm-btn secondary small" href="messages.php">&larr; Retour à la liste</a></p>
    <h2 style="margin-top:0;font-size:18px;color:#0a243b">
      <?= e($current['subject'] !== null && $current['subject'] !== '' ? $current['subject'] : 'Sans sujet') ?>
    </h2>
    <table class="adm-table" style="margin-bottom:16px">
      <tbody>
        <tr>
          <th style="width:180px">Nom</th>
          <td><?= e($current['name']) ?></td>
        </tr>
        <tr>
          <th>Email</th>
          <td><a href="mailto:<?= e($current['email']) ?>"><?= e($current['email']) ?></a></td>
        </tr>
        <tr>
          <th>Téléphone</th>
          <td><?= e($current['phone'] !== null && $current['phone'] !== '' ? $current['phone'] : '—') ?></td>
        </tr>
        <tr>
          <th>Type de demande</th>
          <td><?= e($current['collab_type'] !== null && $current['collab_type'] !== '' ? $current['collab_type'] : '—') ?>
          </td>
        </tr>
        <tr>
          <th>Langue</th>
          <td><?= e(strtoupper((string) $current['language'])) ?></td>
        </tr>
        <tr>
          <th>Reçu le</th>
          <td><?= e($current['created_at']) ?></td>
        </tr>
        <tr>
          <th>Statut</th>
          <td><span class="badge badge-<?= e($current['status']) ?>"><?= e($current['status']) ?></span></td>
        </tr>
        <tr>
          <th>Message</th>
          <td><?= nl2br(e($current['message'])) ?></td>
        </tr>
      </tbody>
    </table>

    <form method="POST" action="messages.php?id=<?= (int) $current['id'] ?>" style="display:inline-block">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $current['id'] ?>">
      <input type="hidden" name="action" value="status">
      <label for="status" class="adm-muted">Changer le statut :</label>
      <select id="status" name="status" class="adm-select" style="width:auto;display:inline-block">
        <option value="new" <?= $current['status'] === 'new' ? ' selected' : '' ?>>Nouveau</option>
        <option value="read" <?= $current['status'] === 'read' ? ' selected' : '' ?>>Lu</option>
        <option value="processed" <?= $current['status'] === 'processed' ? ' selected' : '' ?>>Traité</option>
      </select>
      <button type="submit" class="adm-btn">Enregistrer</button>
    </form>

    <form method="POST" action="messages.php" style="display:inline-block"
      onsubmit="return confirm('Supprimer définitivement ce message ?');">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $current['id'] ?>">
      <input type="hidden" name="action" value="delete">
      <button type="submit" class="adm-btn danger">Supprimer</button>
    </form>
  </div>
<?php else: ?>
  <div class="adm-card">
    <form method="GET" action="messages.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div style="flex:1 1 320px">
        <label for="q" class="adm-muted">Rechercher (nom, email, sujet, message)</label>
        <input id="q" name="q" class="adm-input" value="<?= e($search) ?>">
      </div>
      <button type="submit" class="adm-btn">Rechercher</button>
      <?php if ($search !== ''): ?>
        <a class="adm-btn secondary" href="messages.php">Réinitialiser</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="adm-card">
    <?php if (empty($rows)): ?>
      <p class="adm-muted">Aucun message<?= $search !== '' ? ' ne correspond à la recherche' : '' ?>.</p>
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
          <?php foreach ($rows as $m): ?>
            <tr>
              <td><?= e($m['created_at']) ?></td>
              <td><?= e($m['name']) ?><br><span class="adm-muted"><?= e($m['email']) ?></span></td>
              <td><?= e($m['subject'] !== null ? $m['subject'] : '—') ?></td>
              <td><?= e(strtoupper((string) $m['language'])) ?></td>
              <td><span class="badge badge-<?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
              <td><a class="adm-btn small" href="messages.php?id=<?= (int) $m['id'] ?>">Voir</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="adm-muted" style="margin-top:12px"><?= count($rows) ?> message(s) affiché(s).</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php admin_footer(); ?>