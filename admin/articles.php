<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : gestion des publications
 * =============================================================================
 *  Liste, création, édition, brouillon/publication, couverture, catégorie,
 *  tags, SEO, prévisualisation et suppression (avec confirmation).
 *  CSRF sur chaque action ; requêtes préparées PDO.
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

$editing = null;        // article en cours d'édition
$editTags = [];
$cats = [];

try {
    $pdo = db();
    $cats = categories_all();

    // -------------------------------------------------------------------
    //  Actions POST (avec CSRF)
    // -------------------------------------------------------------------
    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');

            if ($action === 'delete') {
                $id = (int) ($_POST['id'] ?? 0);
                $art = $id > 0 ? article_by_id($id) : null;
                if ($art !== null) {
                    if (!empty($art['cover_image'])) {
                        upload_delete('image', (string) $art['cover_image']);
                    }
                    $pdo->prepare('DELETE FROM articles WHERE id = :id')->execute([':id' => $id]);
                    $flash = 'Publication supprimée.';
                }
            } elseif ($action === 'toggle') {
                // Publier / dépublier
                $id = (int) ($_POST['id'] ?? 0);
                $art = $id > 0 ? article_by_id($id) : null;
                if ($art !== null) {
                    $new = $art['status'] === 'published' ? 'draft' : 'published';
                    $publishedAt = $new === 'published'
                        ? ($art['published_at'] ?? date('Y-m-d H:i:s'))
                        : null;
                    if ($publishedAt !== null && $art['published_at'] !== null) {
                        $publishedAt = $art['published_at']; // conserve la date initiale
                    }
                    $pdo->prepare('UPDATE articles SET status = :st, published_at = COALESCE(:pa, published_at) WHERE id = :id')
                        ->execute([':st' => $new, ':pa' => $new === 'draft' ? null : $publishedAt, ':id' => $id]);
                    $flash = $new === 'published' ? 'Publication en ligne.' : 'Publication retirée (brouillon).';
                }
            } elseif ($action === 'save') {
                // --- Validation des champs ---
                $clean = static function ($v, int $max): string {
                    $v = is_scalar($v) ? (string) $v : '';
                    $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
                    return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
                };

                $id = (int) ($_POST['id'] ?? 0);
                $title = $clean($_POST['title'] ?? '', 255);
                $slug = slugify($clean($_POST['slug'] ?? '', 160) !== '' ? $clean($_POST['slug'] ?? '', 160) : $title);
                $excerpt = $clean($_POST['excerpt'] ?? '', 500);
                $content = (string) ($_POST['content'] ?? '');       // HTML édité par l'admin (unique auteur de confiance)
                $content = str_replace(["\r\n", "\r"], "\n", $content);
                $categoryId = (int) ($_POST['category_id'] ?? 0);
                $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
                $publishedAt = trim((string) ($_POST['published_at'] ?? ''));
                $lang = normalize_lang($_POST['lang'] ?? 'fr');
                $seoTitle = $clean($_POST['seo_title'] ?? '', 160);
                $seoDesc = $clean($_POST['seo_description'] ?? '', 320);
                $tagsRaw = $clean($_POST['tags'] ?? '', 500);
                $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw)), static fn ($t) => $t !== ''));

                $errors = [];
                if ($title === '') $errors[] = 'Le titre est obligatoire.';
                if ($slug === '') $errors[] = 'Le slug est obligatoire.';
                if (trim($content) === '') $errors[] = 'Le contenu est obligatoire.';

                // Slug unique (ajoute un suffixe si nécessaire).
                if ($slug !== '') {
                    $stmt = $pdo->prepare('SELECT id FROM articles WHERE slug = :s AND id <> :id LIMIT 1');
                    $stmt->execute([':s' => $slug, ':id' => $id]);
                    if ($stmt->fetchColumn()) {
                        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                    }
                }

                // Couverture (facultative).
                $coverImage = $clean($_POST['existing_cover'] ?? '', 160);
                if (!empty($_FILES['cover']['name'])) {
                    $up = upload_handle($_FILES['cover'] ?? null, 'image');
                    if ($up['ok']) {
                        if ($coverImage !== '') {
                            upload_delete('image', $coverImage);
                        }
                        $coverImage = $up['filename'];
                    } elseif ($up['error'] !== 'missing') {
                        $errors[] = 'Couverture : fichier invalide (formats acceptés : JPEG, PNG, WebP — 5 Mo max).';
                    }
                }

                if ($publishedAt !== '' && strtotime($publishedAt) === false) {
                    $errors[] = 'Date de publication invalide.';
                }

                if ($errors !== []) {
                    $flash = implode(' ', $errors);
                    $flashType = 'err';
                } else {
                    $articleId = article_save($pdo, [
                        'id' => $id,
                        'slug' => $slug,
                        'lang' => $lang,
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'content' => $content,
                        'cover_image' => $coverImage,
                        'category_id' => $categoryId > 0 ? $categoryId : null,
                        'status' => $status,
                        'published_at' => $publishedAt !== '' ? date('Y-m-d H:i:s', strtotime($publishedAt))
                            : ($status === 'published' ? date('Y-m-d H:i:s') : null),
                        'seo_title' => $seoTitle,
                        'seo_description' => $seoDesc,
                    ], $tags);
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
                                media_image_add('article', $articleId, $up['filename'], [
                                    'caption_fr' => (string) ($_POST['extra_caption_fr'][$i] ?? ''),
                                    'caption_en' => (string) ($_POST['extra_caption_en'][$i] ?? ''),
                                ]);
                            }
                        }
                    }

                    $flash = $status === 'published' ? 'Publication enregistrée et en ligne.' : 'Brouillon enregistré.';
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
        header('Location: articles.php' . ($qs !== '' ? '?' . $qs : ''), true, 303);
        exit;
    }

    // -------------------------------------------------------------------
    //  GET : édition ou liste
    // -------------------------------------------------------------------
    $editId = (int) ($_GET['edit'] ?? ($_GET['id'] ?? 0));
    if ($editId > 0) {
        $editing = article_by_id($editId);
        if ($editing !== null) {
            $tagRows = article_tags($editId);
            $editTags = array_column($tagRows, 'name');
        }
    }
} catch (Throwable $e) {
    log_event('error', 'Admin articles: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$list = [];
$dbError = '';
try {
    $list = articles_list(false, 200);
} catch (Throwable $e) {
    log_event('error', 'Admin articles: list unavailable');
    $dbError = 'La base de données est temporairement indisponible.';
}

admin_header('Publications', 'articles');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>
<?php if ($dbError !== ''): ?>
  <div class="adm-alert err"><?= e($dbError) ?></div>
<?php endif; ?>

<?php if ($editing !== null || isset($_GET['new'])): ?>
  <?php
  $a = $editing ?? [
      'id' => 0, 'slug' => '', 'lang' => 'fr', 'title' => '', 'excerpt' => '', 'content_html' => '',
      'cover_image' => '', 'category_id' => '', 'status' => 'draft', 'published_at' => '',
      'seo_title' => '', 'seo_description' => '',
  ];
  $previewBase = '../article.php?preview=1&slug=' . rawurlencode((string) $a['slug']);
  ?>
  <div class="adm-card">
    <p class="adm-flex">
      <a class="adm-btn secondary small" href="articles.php">&larr; Retour à la liste</a>
      <?php if ((string) $a['slug'] !== ''): ?>
        <a class="adm-btn small" href="<?= e($previewBase) ?>" target="_blank" rel="noopener">Prévisualiser ↗</a>
      <?php endif; ?>
    </p>
    <h2 style="font-size:18px;color:#0a243b;margin-top:0">
      <?= $editing !== null ? 'Modifier la publication' : 'Nouvelle publication' ?>
    </h2>
    <form method="POST" enctype="multipart/form-data" action="articles.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="title">Titre *</label>
          <input id="title" name="title" class="adm-input" required maxlength="255"
                 value="<?= e($a['title']) ?>">
        </div>
        <div class="adm-field">
          <label for="slug">Slug (URL) — généré depuis le titre si vide</label>
          <input id="slug" name="slug" class="adm-input" maxlength="160" value="<?= e($a['slug']) ?>">
        </div>
      </div>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="lang">Langue de la publication</label>
          <select id="lang" name="lang" class="adm-select">
            <option value="fr" <?= ($a['lang'] ?? 'fr') === 'fr' ? 'selected' : '' ?>>Français</option>
            <option value="en" <?= ($a['lang'] ?? 'fr') === 'en' ? 'selected' : '' ?>>English</option>
          </select>
        </div>
        <div class="adm-field">
          <label for="category_id">Catégorie</label>
          <select id="category_id" name="category_id" class="adm-select">
            <option value="">— Aucune —</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= (string) $a['category_id'] === (string) $c['id'] ? 'selected' : '' ?>>
                <?= e(category_label($c, 'fr')) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="adm-field">
        <label for="excerpt">Résumé (chapeau, 500 car. max)</label>
        <textarea id="excerpt" name="excerpt" class="adm-textarea" rows="2" maxlength="500"><?= e($a['excerpt']) ?></textarea>
      </div>

      <div class="adm-field">
        <label for="content">Contenu (HTML simple autorisé : &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;blockquote&gt;) *</label>
        <textarea id="content" name="content" class="adm-textarea" rows="16" required
                  style="font-family:Consolas,monospace;font-size:13px"><?= e($a['content_html']) ?></textarea>
      </div>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="cover">Image de couverture (JPEG/PNG/WebP — 5 Mo max) — sert aussi d'aperçu de partage</label>
          <?php if (!empty($a['cover_image'])): ?>
            <p><img class="adm-thumb" src="../uploads/images/<?= e($a['cover_image']) ?>" alt="">
              <span class="adm-muted">Couverture actuelle — choisir un fichier pour la remplacer.</span></p>
            <input type="hidden" name="existing_cover" value="<?= e($a['cover_image']) ?>">
          <?php endif; ?>
          <input id="cover" type="file" name="cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        </div>
        <div class="adm-field">
          <label for="tags">Mots-clés (séparés par des virgules)</label>
          <input id="tags" name="tags" class="adm-input" value="<?= e(implode(', ', $editTags)) ?>"
                 placeholder="gouvernance, IA, Afrique">
        </div>
      </div>

      <?php if ((int) $a['id'] > 0): ?>
      <div class="adm-field">
        <label>Images supplémentaires (galerie de l'article, façon LinkedIn)</label>
        <?php $extraImgs = media_images_for('article', (int) $a['id']); ?>
        <?php if (!empty($extraImgs)): ?>
          <?php foreach ($extraImgs as $im): ?>
            <div class="adm-flex" style="background:var(--ivory);padding:10px;border-radius:4px;margin-bottom:8px">
              <img class="adm-thumb" src="../uploads/images/<?= e($im['filename']) ?>" alt="">
              <form method="POST" action="articles.php" style="flex:1;display:grid;grid-template-columns:1fr 1fr 90px auto;gap:8px;align-items:end">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_extra_image">
                <input type="hidden" name="image_id" value="<?= (int) $im['id'] ?>">
                <input class="adm-input" name="caption_fr" placeholder="Légende FR" maxlength="255" value="<?= e($im['caption_fr']) ?>">
                <input class="adm-input" name="caption_en" placeholder="Légende EN" maxlength="255" value="<?= e($im['caption_en']) ?>">
                <input class="adm-input" type="number" name="sort_order" value="<?= (int) $im['sort_order'] ?>" title="Ordre">
                <button class="adm-btn small" type="submit">OK</button>
              </form>
              <form method="POST" action="articles.php" onsubmit="return confirm('Supprimer cette image ?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_extra_image">
                <input type="hidden" name="image_id" value="<?= (int) $im['id'] ?>">
                <button class="adm-btn small danger" type="submit">✕</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <input type="file" name="extra_images[]" multiple accept=".jpg,.jpeg,.png,.webp" style="margin-top:6px">
        <p class="adm-help">Ajouter une ou plusieurs images (elles seront reliées à cet article après enregistrement).</p>
      </div>
      <?php endif; ?>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="published_at">Date de publication</label>
          <input id="published_at" type="datetime-local" name="published_at" class="adm-input"
                 value="<?= e($a['published_at'] !== null && $a['published_at'] !== '' ? date('Y-m-d\TH:i', strtotime((string) $a['published_at'])) : '') ?>">
        </div>
        <div class="adm-field">
          <label for="status">Statut</label>
          <select id="status" name="status" class="adm-select">
            <option value="draft" <?= $a['status'] === 'draft' ? 'selected' : '' ?>>Brouillon (non visible)</option>
            <option value="published" <?= $a['status'] === 'published' ? 'selected' : '' ?>>Publié (visible sur le site)</option>
          </select>
        </div>
      </div>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="seo_title">SEO — titre (recommandé ≤ 60 car.)</label>
          <input id="seo_title" name="seo_title" class="adm-input" maxlength="160" value="<?= e($a['seo_title']) ?>">
        </div>
        <div class="adm-field">
          <label for="seo_description">SEO — méta description (≤ 160 car. recommandé)</label>
          <input id="seo_description" name="seo_description" class="adm-input" maxlength="320" value="<?= e($a['seo_description']) ?>">
        </div>
      </div>

      <p class="adm-flex">
        <button type="submit" name="status_submit" class="adm-btn" formaction="articles.php">Enregistrer</button>
        <a class="adm-btn secondary" href="articles.php">Annuler</a>
      </p>
    </form>
  </div>

<?php else: ?>
  <div class="adm-toolbar">
    <a class="adm-btn" href="articles.php?new=1">+ Nouvelle publication</a>
    <span class="adm-muted"><?= count($list) ?> publication(s)</span>
  </div>

  <div class="adm-card">
    <?php if (empty($list)): ?>
      <p class="adm-muted">Aucune publication pour le moment.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead>
          <tr>
            <th>Titre</th><th>Catégorie</th><th>Langue</th><th>Statut</th><th>Publiée le</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $a): ?>
            <tr>
              <td>
                <strong><?= e(mb_substr((string) $a['title'], 0, 70, 'UTF-8')) ?></strong><br>
                <span class="adm-muted">/articles/<?= e($a['slug']) ?></span>
              </td>
              <td><?= e($a['category_fr'] !== null ? $a['category_fr'] : '—') ?></td>
              <td><?= e(strtoupper((string) $a['lang'])) ?></td>
              <td><span class="badge badge-<?= $a['status'] === 'published' ? 'processed' : 'read' ?>"><?= $a['status'] === 'published' ? 'publié' : 'brouillon' ?></span></td>
              <td><?= e($a['published_at'] ?? '—') ?></td>
              <td class="adm-flex" style="gap:6px">
                <a class="adm-btn small" href="articles.php?edit=<?= (int) $a['id'] ?>">Éditer</a>
                <?php if ($a['status'] === 'published'): ?>
                  <a class="adm-btn small secondary" href="../article.php?slug=<?= e(rawurlencode((string) $a['slug'])) ?>" target="_blank" rel="noopener">Voir</a>
                <?php endif; ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Changer le statut de cette publication ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                  <button type="submit" class="adm-btn small secondary"><?= $a['status'] === 'published' ? 'Dépublier' : 'Publier' ?></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer définitivement cette publication ? Cette action est irréversible.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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
