<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : gestion des CV (FR / EN)
 * =============================================================================
 *  Téléversement de PDF, activation par langue, historique des versions.
 *  Les PDF d'origine (documents/CV_SKS.pdf / CV_SKS_EN.pdf) restent servis
 *  tant qu'aucun CV n'a été téléversé.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

$flash = '';
$flashType = 'ok';

try {
    $pdo = db();

    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'upload') {
                $lang = normalize_lang($_POST['lang'] ?? 'fr');
                $up = upload_handle($_FILES['cv_file'] ?? null, 'cv');
                if (!$up['ok']) {
                    $msg = [
                        'bad_type' => 'Le fichier doit être un PDF.',
                        'too_large' => 'Fichier trop volumineux (5 Mo maximum).',
                        'upload_error' => 'Échec du téléversement.',
                        'move_failed' => 'Échec d\'enregistrement sur le serveur.',
                    ][$up['error']] ?? 'Échec du téléversement.';
                    $flash = $msg;
                    $flashType = 'err';
                } else {
                    $version = (int) $pdo->query('SELECT COALESCE(MAX(version), 0) + 1 FROM cv_documents WHERE lang = ' . $pdo->quote($lang))->fetchColumn();
                    $pdo->prepare('UPDATE cv_documents SET is_active = 0 WHERE lang = :lang')->execute([':lang' => $lang]);
                    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
                    $pdo->prepare(
                        'INSERT INTO cv_documents (lang, filename, original_name, version, is_active, uploaded_by)
                         VALUES (:lang, :fn, :on, :v, 1, :uid)'
                    )->execute([
                        ':lang' => $lang,
                        ':fn' => $up['filename'],
                        ':on' => mb_substr((string) ($_FILES['cv_file']['name'] ?? ''), 0, 160, 'UTF-8'),
                        ':v' => $version,
                        ':uid' => $adminId,
                    ]);
                    $flash = 'CV ' . strtoupper($lang) . ' téléversé et activé (version ' . $version . ').';
                }
            } elseif ($action === 'activate') {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('SELECT lang FROM cv_documents WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $lang = (string) ($stmt->fetchColumn() ?: '');
                if ($lang !== '') {
                    $pdo->prepare('UPDATE cv_documents SET is_active = 0 WHERE lang = :lang')->execute([':lang' => $lang]);
                    $pdo->prepare('UPDATE cv_documents SET is_active = 1 WHERE id = :id')->execute([':id' => $id]);
                    $flash = 'Version activée.';
                }
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('SELECT filename, is_active FROM cv_documents WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if ($row) {
                    upload_delete('cv', (string) $row['filename']);
                    $pdo->prepare('DELETE FROM cv_documents WHERE id = :id')->execute([':id' => $id]);
                    $flash = $row['is_active'] ? 'Version supprimée (le CV d\'origine du site reprend le relais).' : 'Version supprimée.';
                }
            }
        }
        header('Location: cv.php', true, 303);
        exit;
    }
} catch (Throwable $e) {
    log_event('error', 'Admin cv: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$history = [];
try {
    $history = cv_history();
} catch (Throwable $e) {
    log_event('error', 'Admin cv: history unavailable');
}

admin_header('CV', 'cv');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Téléverser un CV</h2>
  <p class="adm-help">PDF uniquement, 5 Mo maximum. Le CV activé est celui téléchargé depuis le site public. Tant qu'aucun CV n'est activé, les PDF d'origine (documents/CV_SKS.pdf et CV_SKS_EN.pdf) restent en ligne.</p>
  <form method="POST" enctype="multipart/form-data" action="cv.php" class="adm-flex" style="align-items:flex-end">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
    <div class="adm-field" style="margin-bottom:0">
      <label for="cv_file">Fichier PDF</label>
      <input id="cv_file" type="file" name="cv_file" accept=".pdf,application/pdf" required>
    </div>
    <div class="adm-field" style="margin-bottom:0">
      <label for="lang">Langue</label>
      <select id="lang" name="lang" class="adm-select" style="width:auto">
        <option value="fr">Français</option>
        <option value="en">English</option>
      </select>
    </div>
    <button type="submit" class="adm-btn">Téléverser</button>
  </form>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Versions enregistrées</h2>
  <?php if (empty($history)): ?>
    <p class="adm-muted">Aucun CV téléversé pour le moment — le site sert les PDF d'origine.</p>
  <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>Langue</th><th>Version</th><th>Fichier d'origine</th><th>Ajouté le</th><th>État</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($history as $cv): ?>
          <tr>
            <td><?= e(strtoupper((string) $cv['lang'])) ?></td>
            <td>v<?= (int) $cv['version'] ?></td>
            <td><?= e($cv['original_name']) ?></td>
            <td><?= e($cv['created_at']) ?></td>
            <td><span class="badge badge-<?= $cv['is_active'] ? 'processed' : 'read' ?>"><?= $cv['is_active'] ? 'actif' : 'archivé' ?></span></td>
            <td class="adm-flex" style="gap:6px">
              <a class="adm-btn small" href="../uploads/cv/<?= e(rawurlencode((string) $cv['filename'])) ?>" target="_blank" rel="noopener">Voir</a>
              <?php if (!$cv['is_active']): ?>
                <form method="POST" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="activate">
                  <input type="hidden" name="id" value="<?= (int) $cv['id'] ?>">
                  <button type="submit" class="adm-btn small secondary">Activer</button>
                </form>
              <?php endif; ?>
              <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cette version du CV ?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $cv['id'] ?>">
                <button type="submit" class="adm-btn small danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
