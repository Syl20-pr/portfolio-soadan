<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : gestion des projets dynamiques
 * =============================================================================
 *  Les 6 fiches projets statiques existantes (projects/*.php) restent
 *  inchangées ; ce module ajoute des projets dynamiques complémentaires.
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
$editing = null;

try {
    $pdo = db();

    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $p = $id > 0 ? project_get($id) : null;
                if ($p !== null) {
                    if (!empty($p['image'])) {
                        upload_delete('image', (string) $p['image']);
                    }
                    // media_images est purgé en cascade (clé étrangère).
                    $pdo->prepare('DELETE FROM projects WHERE id = :id')->execute([':id' => $id]);
                    $flash = 'Projet supprimé.';
                }
            } elseif ($action === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                $p = $id > 0 ? project_get($id) : null;
                if ($p !== null) {
                    $new = $p['status'] === 'published' ? 'draft' : 'published';
                    $pdo->prepare('UPDATE projects SET status = :st WHERE id = :id')
                        ->execute([':st' => $new, ':id' => $id]);
                    $flash = $new === 'published' ? 'Projet publié.' : 'Projet masqué.';
                }
            } elseif ($action === 'save') {
                $clean = static function ($v, int $max): string {
                    $v = is_scalar($v) ? (string) $v : '';
                    $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
                    return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
                };

                $id = (int) ($_POST['id'] ?? 0);
                $title = $clean($_POST['title'] ?? '', 200);
                $slug = slugify($clean($_POST['slug'] ?? '', 160) !== '' ? $clean($_POST['slug'] ?? '', 160) : $title);
                $errors = [];

                if ($title === '') $errors[] = 'Le titre est obligatoire.';
                if ($slug === '') $errors[] = 'Le slug est obligatoire.';

                $stmt = $pdo->prepare('SELECT id FROM projects WHERE slug = :s AND id <> :id LIMIT 1');
                $stmt->execute([':s' => $slug, ':id' => $id]);
                if ($stmt->fetchColumn()) {
                    $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                }

                $image = $clean($_POST['existing_image'] ?? '', 160);
                if (!empty($_FILES['image']['name'])) {
                    $up = upload_handle($_FILES['image'] ?? null, 'image');
                    if ($up['ok']) {
                        if ($image !== '') upload_delete('image', $image);
                        $image = $up['filename'];
                    } elseif ($up['error'] !== 'missing') {
                        $errors[] = 'Image : fichier invalide (JPEG/PNG/WebP — 5 Mo max).';
                    }
                }

                $url = $clean($_POST['external_url'] ?? '', 400);
                if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $errors[] = 'Lien externe invalide (doit commencer par http:// ou https://).';
                } elseif ($url !== '') {
                    $url = (string) filter_var($url, FILTER_SANITIZE_URL);
                }

                $ds = trim((string) ($_POST['date_start'] ?? ''));
                $de = trim((string) ($_POST['date_end'] ?? ''));

                if ($errors !== []) {
                    $flash = implode(' ', $errors);
                    $flashType = 'err';
                } else {
                    $savedId = project_save($pdo, [
                        'id' => $id,
                        'slug' => $slug,
                        'title' => $title,
                        'role' => $clean($_POST['role'] ?? '', 160),
                        'summary' => $clean($_POST['summary'] ?? '', 400),
                        'description_html' => (string) ($_POST['description'] ?? ''),
                        'objectives_html' => (string) ($_POST['objectives'] ?? ''),
                        'results_html' => (string) ($_POST['results'] ?? ''),
                        'partners' => $clean($_POST['partners'] ?? '', 400),
                        'title_en' => $clean($_POST['title_en'] ?? '', 200),
                        'role_en' => $clean($_POST['role_en'] ?? '', 160),
                        'summary_en' => $clean($_POST['summary_en'] ?? '', 400),
                        'description_en' => (string) ($_POST['description_en'] ?? ''),
                        'objectives_en' => (string) ($_POST['objectives_en'] ?? ''),
                        'results_en' => (string) ($_POST['results_en'] ?? ''),
                        'partners_en' => $clean($_POST['partners_en'] ?? '', 400),
                        'image' => $image,
                        'external_url' => $url,
                        'date_start' => $ds !== '' && strtotime($ds) ? date('Y-m-d', strtotime($ds)) : null,
                        'date_end' => $de !== '' && strtotime($de) ? date('Y-m-d', strtotime($de)) : null,
                        'status' => ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
                        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    ]);

                    // Images supplémentaires (galerie façon LinkedIn).
                    $extraFiles = $_FILES['extra_images'] ?? null;
                    if (is_array($extraFiles) && isset($extraFiles['name']) && is_array($extraFiles['name'])) {
                        foreach ($extraFiles['name'] as $i => $n) {
                            if (empty($n)) continue;
                            $one = [
                                'name' => (string) $extraFiles['name'][$i],
                                'type' => (string) $extraFiles['type'][$i],
                                'tmp_name' => (string) $extraFiles['tmp_name'][$i],
                                'error' => (int) $extraFiles['error'][$i],
                                'size' => (int) $extraFiles['size'][$i],
                            ];
                            $up = upload_handle($one, 'image');
                            if ($up['ok']) {
                                media_image_add('project', $savedId, $up['filename'], [
                                    'caption_fr' => (string) ($_POST['extra_caption_fr'][$i] ?? ''),
                                    'caption_en' => (string) ($_POST['extra_caption_en'][$i] ?? ''),
                                ]);
                            }
                        }
                    }

                    $flash = 'Projet enregistré.';
                }
            } elseif ($action === 'delete_extra_image') {
                $imgId = (int) ($_POST['image_id'] ?? 0);
                if ($imgId > 0) {
                    media_image_delete($imgId);
                    $flash = 'Image supplémentaire supprimée.';
                }
            } elseif ($action === 'update_extra_image') {
                $imgId = (int) ($_POST['image_id'] ?? 0);
                if ($imgId > 0) {
                    db()->prepare(
                        'UPDATE media_images SET caption_fr = :cf, caption_en = :ce, sort_order = :so WHERE id = :id'
                    )->execute([
                        ':cf' => mb_substr(trim((string) ($_POST['caption_fr'] ?? '')), 0, 255, 'UTF-8'),
                        ':ce' => mb_substr(trim((string) ($_POST['caption_en'] ?? '')), 0, 255, 'UTF-8'),
                        ':so' => (int) ($_POST['sort_order'] ?? 0),
                        ':id' => $imgId,
                    ]);
                    $flash = 'Légende mise à jour.';
                }
            }
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: projects.php' . ($qs !== '' ? '?' . $qs : ''), true, 303);
        exit;
    }

    $editId = (int) ($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $editing = project_get($editId);
    }
} catch (Throwable $e) {
    log_event('error', 'Admin projects: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$list = [];
try {
    $list = projects_list(false);
} catch (Throwable $e) {
    log_event('error', 'Admin projects: list unavailable');
}

admin_header('Projets', 'projects');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<?php if ($editing !== null || isset($_GET['new'])): ?>
  <?php $p = $editing ?? [
      'id' => 0, 'slug' => '', 'title' => '', 'role' => '', 'summary' => '', 'description_html' => '',
      'objectives_html' => '', 'results_html' => '', 'partners' => '', 'image' => '',
      'external_url' => '', 'date_start' => '', 'date_end' => '', 'status' => 'draft', 'sort_order' => 0,
  ]; ?>
  <div class="adm-card">
    <p><a class="adm-btn secondary small" href="projects.php">&larr; Retour à la liste</a></p>
    <h2 style="font-size:18px;color:#0a243b;margin-top:0">
      <?= $editing !== null ? 'Modifier le projet' : 'Nouveau projet' ?>
    </h2>
    <form method="POST" enctype="multipart/form-data" action="projects.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="slug">Slug (URL) — généré depuis le titre si vide</label>
          <input id="slug" name="slug" class="adm-input" maxlength="160" value="<?= e($p['slug']) ?>">
        </div>
      </div>

      <?php $enOkP = bilingual_status($p) === 'ok'; ?>
      <div class="adm-duo">
        <div class="adm-lang fr">
          <?php admin_lang_head('fr', 'Français'); ?>
          <div class="adm-field">
            <label for="title">Titre *</label>
            <input id="title" name="title" class="adm-input" required maxlength="200" value="<?= e($p['title']) ?>">
          </div>
          <div class="adm-grid2">
            <div class="adm-field">
              <label for="role">Rôle occupé</label>
              <input id="role" name="role" class="adm-input" maxlength="160" value="<?= e($p['role']) ?>">
            </div>
            <div class="adm-field">
              <label for="partners">Partenaires</label>
              <input id="partners" name="partners" class="adm-input" maxlength="400" value="<?= e($p['partners']) ?>">
            </div>
          </div>
          <div class="adm-field">
            <label for="summary">Résumé (400 car. max)</label>
            <textarea id="summary" name="summary" class="adm-textarea" rows="2" maxlength="400"><?= e($p['summary']) ?></textarea>
          </div>
          <div class="adm-field">
            <label for="description">Description (HTML simple autorisé)</label>
            <textarea id="description" name="description" class="adm-textarea" rows="7"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['description_html']) ?></textarea>
          </div>
          <div class="adm-field">
            <label for="objectives">Objectifs (HTML simple, une puce &lt;li&gt; par objectif dans un &lt;ul&gt;)</label>
            <textarea id="objectives" name="objectives" class="adm-textarea" rows="4"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['objectives_html']) ?></textarea>
          </div>
          <div class="adm-field">
            <label for="results">Résultats (HTML simple)</label>
            <textarea id="results" name="results" class="adm-textarea" rows="4"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['results_html']) ?></textarea>
          </div>
        </div>
        <div class="adm-lang en">
          <?php admin_lang_head('en', 'English', '#title', '#title_en'); ?>
          <div class="adm-field">
            <label for="title_en">Title (English)</label>
            <input id="title_en" name="title_en" class="adm-input" maxlength="200" value="<?= e($p['title_en'] ?? '') ?>">
          </div>
          <div class="adm-grid2">
            <div class="adm-field">
              <label for="role_en">Role (English)</label>
              <input id="role_en" name="role_en" class="adm-input" maxlength="160" value="<?= e($p['role_en'] ?? '') ?>">
            </div>
            <div class="adm-field">
              <label for="partners_en">Partners (English)</label>
              <input id="partners_en" name="partners_en" class="adm-input" maxlength="400" value="<?= e($p['partners_en'] ?? '') ?>">
            </div>
          </div>
          <div class="adm-field">
            <label for="summary_en">Summary (400 car. max)</label>
            <textarea id="summary_en" name="summary_en" class="adm-textarea" rows="2" maxlength="400"><?= e($p['summary_en'] ?? '') ?></textarea>
          </div>
          <div class="adm-field">
            <label for="description_en">Description (simple HTML allowed)</label>
            <textarea id="description_en" name="description_en" class="adm-textarea" rows="7"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['description_en'] ?? '') ?></textarea>
          </div>
          <div class="adm-field">
            <label for="objectives_en">Objectives (simple HTML)</label>
            <textarea id="objectives_en" name="objectives_en" class="adm-textarea" rows="4"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['objectives_en'] ?? '') ?></textarea>
          </div>
          <div class="adm-field">
            <label for="results_en">Results (simple HTML)</label>
            <textarea id="results_en" name="results_en" class="adm-textarea" rows="4"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($p['results_en'] ?? '') ?></textarea>
          </div>
          <p class="adm-help">Laissez vide : le projet n’apparaîtra pas sur la page anglaise.</p>
        </div>
      </div>
      <?php admin_copy_script_once(); ?>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="image">Image principale (JPEG/PNG/WebP — 5 Mo max) — sert aussi d'aperçu de partage</label>
          <?php if (!empty($p['image'])): ?>
            <p><img class="adm-thumb" src="../uploads/images/<?= e($p['image']) ?>" alt=""></p>
            <input type="hidden" name="existing_image" value="<?= e($p['image']) ?>">
          <?php endif; ?>
          <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="adm-field">
          <label for="external_url">Lien externe (https://…)</label>
          <input id="external_url" name="external_url" class="adm-input" maxlength="400" value="<?= e($p['external_url']) ?>">
        </div>
      </div>

      <?php if ((int) $p['id'] > 0): ?>
      <div class="adm-field">
        <label>Images supplémentaires (galerie de la fiche projet)</label>
        <?php $extraImgs = media_images_for('project', (int) $p['id']); ?>
        <?php if (!empty($extraImgs)): ?>
          <?php foreach ($extraImgs as $im): ?>
            <div class="adm-flex" style="background:var(--ivory);padding:10px;border-radius:4px;margin-bottom:8px">
              <img class="adm-thumb" src="../uploads/images/<?= e($im['filename']) ?>" alt="">
              <form method="POST" action="projects.php" style="flex:1;display:grid;grid-template-columns:1fr 1fr 90px auto;gap:8px;align-items:end">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_extra_image">
                <input type="hidden" name="image_id" value="<?= (int) $im['id'] ?>">
                <input class="adm-input" name="caption_fr" placeholder="Légende FR" maxlength="255" value="<?= e($im['caption_fr']) ?>">
                <input class="adm-input" name="caption_en" placeholder="Légende EN" maxlength="255" value="<?= e($im['caption_en']) ?>">
                <input class="adm-input" type="number" name="sort_order" value="<?= (int) $im['sort_order'] ?>" title="Ordre">
                <button class="adm-btn small" type="submit">OK</button>
              </form>
              <form method="POST" action="projects.php" onsubmit="return confirm('Supprimer cette image ?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_extra_image">
                <input type="hidden" name="image_id" value="<?= (int) $im['id'] ?>">
                <button class="adm-btn small danger" type="submit">✕</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <input type="file" name="extra_images[]" multiple accept=".jpg,.jpeg,.png,.webp" style="margin-top:6px">
        <p class="adm-help">Ajouter une ou plusieurs images (elles seront reliées à ce projet après enregistrement).</p>
      </div>
      <?php endif; ?>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="date_start">Date de début</label>
          <input id="date_start" type="date" name="date_start" class="adm-input" value="<?= e($p['date_start']) ?>">
        </div>
        <div class="adm-field">
          <label for="date_end">Date de fin (vide = en cours)</label>
          <input id="date_end" type="date" name="date_end" class="adm-input" value="<?= e($p['date_end']) ?>">
        </div>
      </div>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="sort_order">Ordre d'affichage</label>
          <input id="sort_order" type="number" name="sort_order" class="adm-input" value="<?= (int) $p['sort_order'] ?>">
        </div>
        <div class="adm-field">
          <label for="status">Statut</label>
          <select id="status" name="status" class="adm-select">
            <option value="draft" <?= $p['status'] === 'draft' ? 'selected' : '' ?>>Brouillon (masqué)</option>
            <option value="published" <?= $p['status'] === 'published' ? 'selected' : '' ?>>Publié</option>
          </select>
        </div>
      </div>

      <p class="adm-flex">
        <button type="submit" class="adm-btn">Enregistrer</button>
        <a class="adm-btn secondary" href="projects.php">Annuler</a>
      </p>
    </form>
  </div>

<?php else: ?>
  <div class="adm-toolbar">
    <a class="adm-btn" href="projects.php?new=1">+ Nouveau projet</a>
    <span class="adm-muted"><?= count($list) ?> projet(s) dynamique(s)</span>
  </div>
  <p class="adm-help">Les fiches statiques existantes (BITC, African Health OS, 4 Voix Jeunesse, JDA, TJT, Observatory) restent affichées sur le site — elles ne sont pas modifiées par ce module.</p>

  <div class="adm-card">
    <?php if (empty($list)): ?>
      <p class="adm-muted">Aucun projet dynamique pour le moment.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Titre</th><th>Rôle</th><th>Période</th><th>Statut</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($list as $p): ?>
            <tr>
              <td><strong><?= e(mb_substr((string) $p['title'], 0, 60, 'UTF-8')) ?></strong><br>
                <span class="adm-muted">/projets/<?= e($p['slug']) ?></span></td>
              <td><?= e($p['role'] !== '' ? $p['role'] : '—') ?></td>
              <td><?= e(($p['date_start'] ?? '—') . ' → ' . ($p['date_end'] ?? 'en cours')) ?></td>
              <td><span class="badge badge-<?= $p['status'] === 'published' ? 'processed' : 'read' ?>"><?= $p['status'] === 'published' ? 'publié' : 'brouillon' ?></span></td>
              <td class="adm-flex" style="gap:6px">
                <a class="adm-btn small" href="projects.php?edit=<?= (int) $p['id'] ?>">Éditer</a>
                <form method="POST" style="display:inline" onsubmit="return confirm('Changer le statut de ce projet ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="adm-btn small secondary"><?= $p['status'] === 'published' ? 'Masquer' : 'Publier' ?></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer définitivement ce projet ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="adm-btn small danger">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php admin_footer(); ?>
