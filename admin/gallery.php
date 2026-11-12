<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : galerie photographique
 * =============================================================================
 *  Upload multiple, description, lieu, date, catégorie, ordre, approbation
 *  (une photo n'est visible publiquement qu'une fois approuvée), suppression.
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
$gcats = gallery_categories();

try {
    $pdo = db();

    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'upload') {
                $files = $_FILES['photos'] ?? null;
                $cat = (string) ($_POST['category'] ?? 'conferences');
                if (!isset($gcats[$cat])) {
                    $cat = 'conferences';
                }
                $caption = trim((string) ($_POST['caption'] ?? ''));
                $location = trim((string) ($_POST['location'] ?? ''));
                $takenOn = trim((string) ($_POST['taken_on'] ?? ''));
                $takenOn = $takenOn !== '' && strtotime($takenOn) ? date('Y-m-d', strtotime($takenOn)) : null;

                // Upload multiple (names tableau).
                if (is_array($files) && isset($files['name']) && is_array($files['name'])) {
                    $okCount = 0;
                    $koCount = 0;
                    $adminId = ((int) ($_SESSION['admin_id'] ?? 0)) > 0 ? (int) $_SESSION['admin_id'] : null;
                    foreach ($files['name'] as $i => $n) {
                        if (empty($n)) continue;
                        $one = [
                            'name' => (string) $files['name'][$i],
                            'type' => (string) $files['type'][$i],
                            'tmp_name' => (string) $files['tmp_name'][$i],
                            'error' => (int) $files['error'][$i],
                            'size' => (int) $files['size'][$i],
                        ];
                        $up = upload_handle($one, 'image');
                        if ($up['ok']) {
                            $pdo->prepare(
                                'INSERT INTO gallery_photos (filename, caption, alt_text, category, location, taken_on, approved, uploaded_by)
                                 VALUES (:f, :cap, :alt, :cat, :loc, :tk, 0, :uid)'
                            )->execute([
                                ':f' => $up['filename'],
                                ':cap' => function_exists('mb_substr') ? mb_substr($caption, 0, 255, 'UTF-8') : substr($caption, 0, 255),
                                ':alt' => function_exists('mb_substr') ? mb_substr($caption, 0, 255, 'UTF-8') : substr($caption, 0, 255),
                                ':cat' => $cat,
                                ':loc' => $location,
                                ':tk' => $takenOn,
                                ':uid' => $adminId,
                            ]);
                            $okCount++;
                        } else {
                            $koCount++;
                        }
                    }
                    $flash = "$okCount photo(s) ajoutée(s) (en attente d'approbation)." .
                        ($koCount > 0 ? " $koCount rejetée(s) : format ou taille invalide." : '');
                    $flashType = $koCount > 0 && $okCount === 0 ? 'err' : 'ok';
                } else {
                    $flash = 'Aucun fichier reçu.';
                    $flashType = 'err';
                }
            } elseif ($action === 'update') {
                // Mise à jour des métadonnées d'une photo (+ options bandeau d'accueil).
                $id = (int) ($_POST['id'] ?? 0);
                $cat = (string) ($_POST['category'] ?? 'conferences');
                if (!isset($gcats[$cat])) $cat = 'conferences';
                $tk = trim((string) ($_POST['taken_on'] ?? ''));
                $pdo->prepare(
                    'UPDATE gallery_photos
                     SET caption = :cap, caption_en = :capen, alt_text = :alt, category = :cat, location = :loc,
                         taken_on = :tk, sort_order = :sort, approved = :ap, show_home = :sh
                     WHERE id = :id'
                )->execute([
                    ':cap' => mb_substr(trim((string) ($_POST['caption'] ?? '')), 0, 255, 'UTF-8'),
                    ':capen' => mb_substr(trim((string) ($_POST['caption_en'] ?? '')), 0, 255, 'UTF-8'),
                    ':alt' => mb_substr(trim((string) ($_POST['alt_text'] ?? '')), 0, 255, 'UTF-8'),
                    ':cat' => $cat,
                    ':loc' => mb_substr(trim((string) ($_POST['location'] ?? '')), 0, 160, 'UTF-8'),
                    ':tk' => $tk !== '' && strtotime($tk) ? date('Y-m-d', strtotime($tk)) : null,
                    ':sort' => (int) ($_POST['sort_order'] ?? 0),
                    ':ap' => isset($_POST['approved']) ? 1 : 0,
                    ':sh' => isset($_POST['show_home']) ? 1 : 0,
                    ':id' => $id,
                ]);
                $flash = 'Photo mise à jour.';
            } elseif ($action === 'toggle_home') {
                // Case « Afficher sur l'accueil » sans passer par le formulaire complet.
                $id = (int) ($_POST['id'] ?? 0);
                $on = (int) ($_POST['show_home'] ?? 0) === 1;
                $pdo->prepare('UPDATE gallery_photos SET show_home = :sh WHERE id = :id')
                    ->execute([':sh' => $on ? 1 : 0, ':id' => $id]);
                $flash = $on ? 'Photo ajoutée au bandeau d’accueil.' : 'Photo retirée du bandeau d’accueil.';
            } elseif ($action === 'approve') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare('UPDATE gallery_photos SET approved = 1 WHERE id = :id')->execute([':id' => $id]);
                $flash = 'Photo approuvée : elle est désormais visible sur le site.';
            } elseif ($action === 'unapprove') {
                $id = (int) ($_POST['id'] ?? 0);
                $pdo->prepare('UPDATE gallery_photos SET approved = 0 WHERE id = :id')->execute([':id' => $id]);
                $flash = 'Photo retirée de la galerie publique.';
            } elseif ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('SELECT filename FROM gallery_photos WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $fn = (string) ($stmt->fetchColumn() ?: '');
                if ($fn !== '') {
                    upload_delete('image', $fn);
                    $pdo->prepare('DELETE FROM gallery_photos WHERE id = :id')->execute([':id' => $id]);
                    $flash = 'Photo supprimée.';
                }
            }
        }
        header('Location: gallery.php', true, 303);
        exit;
    }
} catch (Throwable $e) {
    log_event('error', 'Admin gallery: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$photos = [];
try {
    $photos = gallery_all();
} catch (Throwable $e) {
    log_event('error', 'Admin gallery: list unavailable');
}

admin_header('Galerie', 'gallery');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Ajouter des photos</h2>
  <p class="adm-help">Formats acceptés : JPEG, PNG, WebP — 5 Mo maximum par fichier. Les photos restent invisibles sur le site public jusqu'à leur approbation. Cochez « Afficher sur l'accueil » pour les faire défiler dans le bandeau animé.</p>
  <form method="POST" enctype="multipart/form-data" action="gallery.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="photos">Fichiers (sélection multiple possible)</label>
        <input id="photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp" required>
      </div>
      <div class="adm-field">
        <label for="category">Catégorie</label>
        <select id="category" name="category" class="adm-select">
          <?php foreach ($gcats as $key => $labels): ?>
            <option value="<?= e($key) ?>"><?= e($labels['fr']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="caption">Légende (appliquée à toutes les photos de ce lot, modifiable ensuite)</label>
        <input id="caption" name="caption" class="adm-input" maxlength="255">
      </div>
      <div class="adm-field">
        <label for="location">Lieu</label>
        <input id="location" name="location" class="adm-input" maxlength="160">
      </div>
    </div>
    <div class="adm-field">
      <label for="taken_on">Date</label>
      <input id="taken_on" type="date" name="taken_on" class="adm-input" style="width:auto">
    </div>
    <button type="submit" class="adm-btn">Téléverser</button>
  </form>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Photos (<?= count($photos) ?>)</h2>
  <?php if (empty($photos)): ?>
    <p class="adm-muted">Aucune photo pour le moment.</p>
  <?php else: ?>
    <?php foreach ($photos as $ph): ?>
      <div class="adm-card" style="background:var(--ivory)">
        <div class="adm-flex" style="align-items:flex-start;gap:16px">
          <img class="adm-thumb" style="width:160px;height:110px" src="../uploads/images/<?= e($ph['filename']) ?>" alt="">
          <form method="POST" action="gallery.php" style="flex:1 1 480px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $ph['id'] ?>">
            <div class="adm-grid2">
              <div class="adm-field">
                <label>Légende (français)</label>
                <input class="adm-input" name="caption" maxlength="255" value="<?= e($ph['caption']) ?>">
              </div>
              <div class="adm-field">
                <label>Légende (anglais) — affichée sur /en/</label>
                <input class="adm-input" name="caption_en" maxlength="255" value="<?= e($ph['caption_en'] ?? '') ?>">
              </div>
            </div>
            <div class="adm-grid2">
              <div class="adm-field">
                <label>Texte alternatif (accessibilité)</label>
                <input class="adm-input" name="alt_text" maxlength="255" value="<?= e($ph['alt_text']) ?>">
              </div>
              <div class="adm-field">
                <label>Étiquette du bandeau (facultative)</label>
                <input class="adm-input" name="banner_label" maxlength="120" value="<?= e($ph['banner_label'] ?? '') ?>">
              </div>
            </div>
            <div class="adm-grid2">
              <div class="adm-field">
                <label>Lieu</label>
                <input class="adm-input" name="location" maxlength="160" value="<?= e($ph['location']) ?>">
              </div>
              <div class="adm-field">
                <label>Date</label>
                <input type="date" class="adm-input" name="taken_on" value="<?= e($ph['taken_on']) ?>">
              </div>
            </div>
            <div class="adm-grid2">
              <div class="adm-field">
                <label>Catégorie</label>
                <select class="adm-select" name="category">
                  <?php foreach ($gcats as $key => $labels): ?>
                    <option value="<?= e($key) ?>" <?= $ph['category'] === $key ? 'selected' : '' ?>><?= e($labels['fr']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="adm-field">
                <label>Ordre d'affichage</label>
                <input type="number" class="adm-input" name="sort_order" value="<?= (int) $ph['sort_order'] ?>">
              </div>
            </div>
            <p class="adm-flex">
              <label style="font-weight:normal"><input type="checkbox" name="approved" <?= $ph['approved'] ? 'checked' : '' ?>> Visible sur le site (approuvée)</label>
              <label style="font-weight:normal"><input type="checkbox" name="show_home" <?= !empty($ph['show_home']) ? 'checked' : '' ?>> Afficher sur l’accueil (bandeau animé)</label>
              <button type="submit" class="adm-btn">Enregistrer</button>
            </p>
          </form>
          <div>
            <span class="badge badge-<?= $ph['approved'] ? 'processed' : 'new' ?>"><?= $ph['approved'] ? 'visible' : 'en attente' ?></span>
            <?php if (!empty($ph['show_home'])): ?><span class="badge badge-processed" style="margin-left:6px">accueil</span><?php endif; ?>
            <form method="POST" style="margin-top:10px" onsubmit="return confirm('Supprimer définitivement cette photo ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $ph['id'] ?>">
              <button type="submit" class="adm-btn small danger">Supprimer</button>
            </form>
            <?php if (!$ph['approved']): ?>
              <form method="POST" style="margin-top:8px">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= (int) $ph['id'] ?>">
                <button type="submit" class="adm-btn small">Approuver</button>
              </form>
            <?php endif; ?>
            <form method="POST" style="margin-top:8px">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_home">
              <input type="hidden" name="id" value="<?= (int) $ph['id'] ?>">
              <input type="hidden" name="show_home" value="<?= empty($ph['show_home']) ? '1' : '0' ?>">
              <button type="submit" class="adm-btn small secondary"><?= empty($ph['show_home']) ? 'Ajouter au bandeau' : 'Retirer du bandeau' ?></button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
