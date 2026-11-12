<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : Actualités (vidéos, capsules, podcasts)
 * =============================================================================
 *  Deux sources exclusives par publication :
 *    - embed : URL de la plateforme (liste blanche stricte, voir
 *              news_embed_hosts() dans includes/content.php) ;
 *    - file  : MP4 ou MP3 court téléversé via upload_handle()
 *              (MIME réel, nom aléatoire, limites news_max_video_mb /
 *              news_max_audio_mb du .env).
 *  Commentaires : modération dans le même écran (statuts pending/approved).
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

$editing = null;
$ncats = news_categories();
$ntypes = news_types();
$maxVideoMb = (int) setting('news_max_video_mb', '64');
$maxAudioMb = (int) setting('news_max_audio_mb', '24');

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
                $n = $id > 0 ? news_by_id($id) : null;
                if ($n !== null) {
                    if (!empty($n['media_file'])) {
                        upload_delete($n['type'] === 'podcast' ? 'audio' : 'video', (string) $n['media_file']);
                    }
                    if (!empty($n['poster_image'])) {
                        upload_delete('image', (string) $n['poster_image']);
                    }
                    $pdo->prepare('DELETE FROM news_items WHERE id = :id')->execute([':id' => $id]);
                    $flash = 'Publication supprimée.';
                }
            } elseif ($action === 'toggle') {
                $id = (int) ($_POST['id'] ?? 0);
                $n = $id > 0 ? news_by_id($id) : null;
                if ($n !== null) {
                    $new = $n['status'] === 'published' ? 'draft' : 'published';
                    $pdo->prepare('UPDATE news_items SET status = :st, published_at = COALESCE(published_at, :pa) WHERE id = :id')
                        ->execute([':st' => $new, ':pa' => $new === 'published' ? date('Y-m-d H:i:s') : null, ':id' => $id]);
                    $flash = $new === 'published' ? 'Publication en ligne.' : 'Publication retirée (brouillon).';
                }
            } elseif ($action === 'comment_status') {
                $cid = (int) ($_POST['comment_id'] ?? 0);
                $state = ($_POST['state'] ?? '') === 'approve' ? 'approved'
                      : (($_POST['state'] ?? '') === 'reject' ? 'rejected' : 'pending');
                $pdo->prepare('UPDATE news_comments SET status = :st WHERE id = :id')
                    ->execute([':st' => $state, ':id' => $cid]);
                $flash = $state === 'approved' ? 'Commentaire approuvé.' : 'Commentaire rejeté.';
            } elseif ($action === 'comment_delete') {
                $cid = (int) ($_POST['comment_id'] ?? 0);
                $pdo->prepare('DELETE FROM news_comments WHERE id = :id')->execute([':id' => $cid]);
                $flash = 'Commentaire supprimé.';
            } elseif ($action === 'save') {
                $clean = static function ($v, int $max): string {
                    $v = is_scalar($v) ? (string) $v : '';
                    $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
                    return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
                };

                $id = (int) ($_POST['id'] ?? 0);
                $title = $clean($_POST['title'] ?? '', 255);
                $slug = slugify($clean($_POST['slug'] ?? '', 160) !== '' ? $clean($_POST['slug'] ?? '', 160) : $title);
                $type = array_key_exists((string) ($_POST['type'] ?? ''), $ntypes) ? (string) $_POST['type'] : 'video';
                $category = array_key_exists((string) ($_POST['category'] ?? ''), $ncats) ? (string) $_POST['category'] : 'conference';
                $lang = normalize_lang($_POST['lang'] ?? 'fr');
                $errors = [];

                if ($title === '') {
                    $errors[] = 'Le titre est obligatoire.';
                }
                if ($slug === '') {
                    $errors[] = 'Le slug est obligatoire.';
                }

                $stmt = $pdo->prepare('SELECT id FROM news_items WHERE slug = :s AND id <> :id LIMIT 1');
                $stmt->execute([':s' => $slug, ':id' => $id]);
                if ($stmt->fetchColumn()) {
                    $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                }

                // Slug anglais distinct (si titre EN fourni — migration 003).
                $titleEn = $clean($_POST['title_en'] ?? '', 255);
                $slugEn = '';
                if ($titleEn !== '' && db_column_exists('news_items', 'title_en')) {
                    $slugEn = slugify($titleEn);
                    if ($slugEn !== '') {
                        $stmt = $pdo->prepare('SELECT id FROM news_items WHERE slug_en = :s AND id <> :id LIMIT 1');
                        $stmt->execute([':s' => $slugEn, ':id' => $id]);
                        if ($stmt->fetchColumn()) {
                            $slugEn .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                        }
                    }
                }

                // Source : embed (liste blanche) OU fichier téléversé.
                $sourceKind = ($_POST['source_kind'] ?? 'embed') === 'file' ? 'file' : 'embed';
                $embedUrl = '';
                $mediaFile = '';
                $mediaMime = '';
                if ($sourceKind === 'embed') {
                    $embedUrl = $clean($_POST['embed_url'] ?? '', 500);
                    if ($embedUrl === '' || !news_embed_valid($embedUrl)) {
                        $errors[] = 'URL d’intégration invalide ou plate-forme non autorisée '
                            . '(YouTube, Vimeo, Facebook, TikTok, Spotify, Apple Podcasts, SoundCloud).';
                    } else {
                        $embedUrl = (string) filter_var($embedUrl, FILTER_SANITIZE_URL);
                    }
                } else {
                    $field = $type === 'podcast' ? 'audio_file' : 'video_file';
                    $kindU = $type === 'podcast' ? 'audio' : 'video';
                    if (!empty($_FILES[$field]['name'])) {
                        $up = upload_handle($_FILES[$field] ?? null, $kindU);
                        if ($up['ok']) {
                            // Remplace l'ancien fichier si présent.
                            $prev = $id > 0 ? news_by_id($id) : null;
                            if ($prev !== null && !empty($prev['media_file'])) {
                                upload_delete($prev['type'] === 'podcast' ? 'audio' : 'video', (string) $prev['media_file']);
                            }
                            $mediaFile = $up['filename'];
                            $mediaMime = $up['mime'];
                        } elseif ($up['error'] === 'too_large') {
                            $errors[] = 'Fichier trop volumineux (limites : vidéo ' . $maxVideoMb
                                . ' Mo, audio ' . $maxAudioMb . ' Mo — ajustables dans .env).';
                        } else {
                            $errors[] = 'Fichier média invalide (formats acceptés : MP4 pour la vidéo, MP3/M4A/OGG pour l’audio).';
                        }
                    } else {
                        // Conservation du fichier existant lors d'une simple édition.
                        $prev = $id > 0 ? news_by_id($id) : null;
                        if ($prev !== null && $prev['source_kind'] === 'file') {
                            $mediaFile = (string) $prev['media_file'];
                            $mediaMime = (string) $prev['media_mime'];
                        } else {
                            $errors[] = 'Aucun fichier média fourni.';
                        }
                    }
                }

                // Miniature (facultative mais recommandée).
                $poster = $clean($_POST['existing_poster'] ?? '', 160);
                if (!empty($_FILES['poster']['name'])) {
                    $up = upload_handle($_FILES['poster'] ?? null, 'image');
                    if ($up['ok']) {
                        if ($poster !== '') {
                            upload_delete('image', $poster);
                        }
                        $poster = $up['filename'];
                    } elseif ($up['error'] !== 'missing') {
                        $errors[] = 'Miniature : fichier invalide (JPEG/PNG/WebP — 5 Mo max).';
                    }
                }

                $eventDate = trim((string) ($_POST['event_date'] ?? ''));
                $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
                $commentsEnabled = isset($_POST['comments_enabled']) ? 1 : 0;

                if ($errors !== []) {
                    $flash = implode(' ', $errors);
                    $flashType = 'err';
                } else {
                    $now = date('Y-m-d H:i:s');
                    if ($id > 0) {
                        $pdo->prepare(
                            'UPDATE news_items SET
                                slug = :slug, lang = :lang, title = :title, excerpt = :excerpt, body_html = :body,
                                type = :type, category = :category, source_kind = :sk, embed_url = :eu,
                                media_file = :mf, media_mime = :mm, poster_image = :pi, event_date = :ed,
                                location = :loc, status = :st, comments_enabled = :ce,
                                published_at = COALESCE(published_at, :pa),
                                slug_en = :slug_en, title_en = :title_en, excerpt_en = :excerpt_en,
                                body_html_en = :body_en, location_en = :loc_en
                             WHERE id = :id'
                        )->execute([
                            ':slug' => $slug, ':lang' => $lang, ':title' => $title,
                            ':excerpt' => $clean($_POST['excerpt'] ?? '', 500),
                            ':body' => (string) ($_POST['body'] ?? ''),
                            ':type' => $type, ':category' => $category, ':sk' => $sourceKind,
                            ':eu' => $embedUrl !== '' ? $embedUrl : null,
                            ':mf' => $mediaFile !== '' ? $mediaFile : null,
                            ':mm' => $mediaMime !== '' ? $mediaMime : null,
                            ':pi' => $poster !== '' ? $poster : null,
                            ':ed' => $eventDate !== '' && strtotime($eventDate) ? date('Y-m-d', strtotime($eventDate)) : null,
                            ':loc' => $clean($_POST['location'] ?? '', 160),
                            ':st' => $status, ':ce' => $commentsEnabled,
                            ':pa' => $status === 'published' ? $now : null,
                            ':id' => $id,
                            ':slug_en' => $slugEn !== '' ? $slugEn : null,
                            ':title_en' => $clean($_POST['title_en'] ?? '', 255),
                            ':excerpt_en' => $clean($_POST['excerpt_en'] ?? '', 500),
                            ':body_en' => (string) ($_POST['body_en'] ?? ''),
                            ':loc_en' => $clean($_POST['location_en'] ?? '', 160),
                        ]);
                        $savedId = $id;
                    } else {
                        $pdo->prepare(
                            'INSERT INTO news_items
                                (slug, lang, title, excerpt, body_html, type, category, source_kind, embed_url,
                                 media_file, media_mime, poster_image, event_date, location, status,
                                 comments_enabled, published_at,
                                 slug_en, title_en, excerpt_en, body_html_en, location_en)
                             VALUES
                                (:slug, :lang, :title, :excerpt, :body, :type, :category, :sk, :eu,
                                 :mf, :mm, :pi, :ed, :loc, :st, :ce, :pa,
                                 :slug_en, :title_en, :excerpt_en, :body_en, :loc_en)'
                        )->execute([
                            ':slug' => $slug, ':lang' => $lang, ':title' => $title,
                            ':excerpt' => $clean($_POST['excerpt'] ?? '', 500),
                            ':body' => (string) ($_POST['body'] ?? ''),
                            ':type' => $type, ':category' => $category, ':sk' => $sourceKind,
                            ':eu' => $embedUrl !== '' ? $embedUrl : null,
                            ':mf' => $mediaFile !== '' ? $mediaFile : null,
                            ':mm' => $mediaMime !== '' ? $mediaMime : null,
                            ':pi' => $poster !== '' ? $poster : null,
                            ':ed' => $eventDate !== '' && strtotime($eventDate) ? date('Y-m-d', strtotime($eventDate)) : null,
                            ':loc' => $clean($_POST['location'] ?? '', 160),
                            ':st' => $status, ':ce' => $commentsEnabled,
                            ':pa' => $status === 'published' ? $now : null,
                            ':slug_en' => $slugEn !== '' ? $slugEn : null,
                            ':title_en' => $clean($_POST['title_en'] ?? '', 255),
                            ':excerpt_en' => $clean($_POST['excerpt_en'] ?? '', 500),
                            ':body_en' => (string) ($_POST['body_en'] ?? ''),
                            ':loc_en' => $clean($_POST['location_en'] ?? '', 160),
                        ]);
                        $savedId = (int) $pdo->lastInsertId();
                    }
                    $flash = $status === 'published' ? 'Publication en ligne.' : 'Brouillon enregistré.';
                }
            }
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: news.php' . ($qs !== '' ? '?' . $qs : ''), true, 303);
        exit;
    }

    $editId = (int) ($_GET['edit'] ?? 0);
    if ($editId > 0) {
        $editing = news_by_id($editId);
    }
} catch (Throwable $e) {
    log_event('error', 'Admin news: failure');
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

$list = [];
$pending = [];
try {
    $list = news_list(false, null, 200);
    $pending = db()->query('SELECT c.*, n.title AS news_title FROM news_comments c JOIN news_items n ON n.id = c.news_id WHERE c.status = \'pending\' ORDER BY c.created_at DESC')->fetchAll();
} catch (Throwable $e) {
    log_event('error', 'Admin news: list unavailable');
}

admin_header('Actualités', 'news');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<?php if ($editing !== null || isset($_GET['new'])): ?>
  <?php
  $n = $editing ?? [
      'id' => 0, 'slug' => '', 'lang' => 'fr', 'title' => '', 'excerpt' => '', 'body_html' => '',
      'type' => 'video', 'category' => 'conference', 'source_kind' => 'embed', 'embed_url' => '',
      'media_file' => '', 'media_mime' => '', 'poster_image' => '', 'event_date' => '', 'location' => '',
      'status' => 'draft', 'comments_enabled' => 0,
  ];
  $isFileSrc = $n['source_kind'] === 'file';
  ?>
  <div class="adm-card">
    <p><a class="adm-btn secondary small" href="news.php">&larr; Retour à la liste</a></p>
    <h2 style="font-size:18px;color:#0a243b;margin-top:0">
      <?= $editing !== null ? 'Modifier la publication' : 'Nouvelle publication Actualités' ?>
    </h2>
    <form method="POST" enctype="multipart/form-data" action="news.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="slug">Slug (URL) — généré depuis le titre si vide</label>
          <input id="slug" name="slug" class="adm-input" maxlength="160" value="<?= e($n['slug']) ?>">
        </div>
      </div>

      <div class="adm-grid3" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px">
        <div class="adm-field">
          <label for="type">Type de publication</label>
          <select id="type" name="type" class="adm-select">
            <?php foreach ($ntypes as $key => $labels): ?>
              <option value="<?= e($key) ?>" <?= $n['type'] === $key ? 'selected' : '' ?>><?= e($labels['fr']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="adm-field">
          <label for="category">Catégorie</label>
          <select id="category" name="category" class="adm-select">
            <?php foreach ($ncats as $key => $labels): ?>
              <option value="<?= e($key) ?>" <?= $n['category'] === $key ? 'selected' : '' ?>><?= e($labels['fr']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="adm-field">
          <label for="event_date">Date de l’événement</label>
          <input id="event_date" type="date" name="event_date" class="adm-input" value="<?= e($n['event_date']) ?>" style="width:auto">
        </div>
      </div>

      <?php $enOkN = bilingual_status($n) === 'ok'; ?>
      <div class="adm-duo">
        <div class="adm-lang fr">
          <?php admin_lang_head('fr', 'Français'); ?>
          <div class="adm-field">
            <label for="title">Titre *</label>
            <input id="title" name="title" class="adm-input" required maxlength="255" value="<?= e($n['title']) ?>">
          </div>
          <div class="adm-field">
            <label for="excerpt">Description courte (chapeau, 500 car. max)</label>
            <textarea id="excerpt" name="excerpt" class="adm-textarea" rows="2" maxlength="500"><?= e($n['excerpt']) ?></textarea>
          </div>
          <div class="adm-field">
            <label for="body">Texte complet (HTML simple autorisé : &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, &lt;strong&gt;, &lt;em&gt;)</label>
            <textarea id="body" name="body" class="adm-textarea" rows="7"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($n['body_html']) ?></textarea>
          </div>
          <div class="adm-field">
            <label for="location">Lieu</label>
            <input id="location" name="location" class="adm-input" maxlength="160" value="<?= e($n['location']) ?>">
          </div>
        </div>
        <div class="adm-lang en">
          <?php admin_lang_head('en', 'English', '#title', '#title_en'); ?>
          <div class="adm-field">
            <label for="title_en">Title (English)</label>
            <input id="title_en" name="title_en" class="adm-input" maxlength="255" value="<?= e($n['title_en'] ?? '') ?>">
          </div>
          <div class="adm-field">
            <label for="excerpt_en">Short description (500 car. max)</label>
            <textarea id="excerpt_en" name="excerpt_en" class="adm-textarea" rows="2" maxlength="500"><?= e($n['excerpt_en'] ?? '') ?></textarea>
          </div>
          <div class="adm-field">
            <label for="body_en">Full text (simple HTML)</label>
            <textarea id="body_en" name="body_en" class="adm-textarea" rows="7"
                      style="font-family:Consolas,monospace;font-size:13px"><?= e($n['body_html_en'] ?? '') ?></textarea>
          </div>
          <div class="adm-field">
            <label for="location_en">Location</label>
            <input id="location_en" name="location_en" class="adm-input" maxlength="160" value="<?= e($n['location_en'] ?? '') ?>">
          </div>
          <p class="adm-help">Laissez vide : la publication n’apparaîtra pas sur la page anglaise.</p>
        </div>
      </div>
      <?php admin_copy_script_once(); ?>

      <div class="adm-field">
        <label>Source du média</label>
        <p class="adm-flex" style="gap:20px">
          <label style="font-weight:normal"><input type="radio" name="source_kind" value="embed" <?= !$isFileSrc ? 'checked' : '' ?>> Lien d’intégration (YouTube, Vimeo, Facebook, TikTok, Spotify, Apple Podcasts)</label>
          <label style="font-weight:normal"><input type="radio" name="source_kind" value="file" <?= $isFileSrc ? 'checked' : '' ?>> Fichier téléversé (MP4 / MP3 court)</label>
        </p>
        <div class="adm-field" id="srcEmbed">
          <label for="embed_url">URL d’intégration (https://…)</label>
          <input id="embed_url" name="embed_url" class="adm-input" maxlength="500"
                 value="<?= e($n['embed_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=…">
        </div>
        <div class="adm-field" id="srcFile">
          <?php $isPodcast = $n['type'] === 'podcast'; ?>
          <label for="video_file">Fichier vidéo (MP4, <?= $maxVideoMb ?> Mo max)</label>
          <input id="video_file" type="file" name="video_file" accept=".mp4,video/mp4">
          <label for="audio_file" style="margin-top:8px">Fichier audio (MP3/M4A/OGG, <?= $maxAudioMb ?> Mo max)</label>
          <input id="audio_file" type="file" name="audio_file" accept=".mp3,.m4a,.ogg,audio/*">
          <?php if (!empty($n['media_file'])): ?>
            <p class="adm-help">Fichier actuel : <?= e($n['media_file']) ?> (<?= e($n['media_mime'] ?? '') ?>) — choisir un fichier pour le remplacer.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="adm-grid2">
        <div class="adm-field">
          <label for="poster">Miniature (JPEG/PNG/WebP — 5 Mo max, recommandée)</label>
          <?php if (!empty($n['poster_image'])): ?>
            <p><img class="adm-thumb" src="../uploads/images/<?= e($n['poster_image']) ?>" alt=""></p>
            <input type="hidden" name="existing_poster" value="<?= e($n['poster_image']) ?>">
          <?php endif; ?>
          <input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="adm-field">
          <label for="status">Statut</label>
          <select id="status" name="status" class="adm-select">
            <option value="draft" <?= $n['status'] === 'draft' ? 'selected' : '' ?>>Brouillon (non visible)</option>
            <option value="published" <?= $n['status'] === 'published' ? 'selected' : '' ?>>Publié (visible sur le site)</option>
          </select>
        </div>
      </div>

      <p>
        <label style="font-weight:normal"><input type="checkbox" name="comments_enabled" <?= !empty($n['comments_enabled']) ? 'checked' : '' ?>> Activer les commentaires pour cette publication (modérés avant publication — désactivés par défaut)</label>
      </p>

      <p class="adm-flex">
        <button type="submit" class="adm-btn">Enregistrer</button>
        <a class="adm-btn secondary" href="news.php">Annuler</a>
      </p>
    </form>
  </div>

  <script>
    (function () {
      function sync() {
        var embed = document.querySelector('input[name=source_kind][value=embed]');
        var e = document.getElementById('srcEmbed');
        var f = document.getElementById('srcFile');
        if (embed && e && f) {
          e.style.display = embed.checked ? '' : 'none';
          f.style.display = embed.checked ? 'none' : '';
        }
      }
      document.querySelectorAll('input[name=source_kind]').forEach(function (r) { r.addEventListener('change', sync); });
      sync();
    })();
  </script>

<?php else: ?>
  <div class="adm-toolbar">
    <a class="adm-btn" href="news.php?new=1">+ Nouvelle publication</a>
    <span class="adm-muted"><?= count($list) ?> publication(s)</span>
  </div>

  <?php if (!empty($pending)): ?>
  <div class="adm-card">
    <h2 style="margin-top:0;font-size:18px;color:#0a243b">Commentaires en attente (<?= count($pending) ?>)</h2>
    <?php foreach ($pending as $cm): ?>
      <div style="background:var(--ivory);border-radius:4px;padding:10px;margin-bottom:10px">
        <strong><?= e($cm['author_name']) ?></strong>
        <span class="adm-muted"> sur « <?= e(mb_substr((string) $cm['news_title'], 0, 60, 'UTF-8')) ?> » — <?= e(date('d/m/Y', strtotime((string) $cm['created_at']))) ?></span>
        <p style="margin:6px 0"><?= nl2br(e($cm['body'])) ?></p>
        <p class="adm-flex">
          <form method="POST" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="comment_status">
            <input type="hidden" name="comment_id" value="<?= (int) $cm['id'] ?>">
            <input type="hidden" name="state" value="approve">
            <button class="adm-btn small" type="submit">Approuver</button>
          </form>
          <form method="POST" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="comment_status">
            <input type="hidden" name="comment_id" value="<?= (int) $cm['id'] ?>">
            <input type="hidden" name="state" value="reject">
            <button class="adm-btn small secondary" type="submit">Rejeter</button>
          </form>
          <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce commentaire ?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="comment_delete">
            <input type="hidden" name="comment_id" value="<?= (int) $cm['id'] ?>">
            <button class="adm-btn small danger" type="submit">Supprimer</button>
          </form>
        </p>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="adm-card">
    <?php if (empty($list)): ?>
      <p class="adm-muted">Aucune publication Actualités pour le moment.</p>
    <?php else: ?>
      <table class="adm-table">
        <thead>
          <tr><th>Titre</th><th>Type</th><th>Catégorie</th><th>Source</th><th>Statut</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $n): ?>
            <tr>
              <td><strong><?= e(mb_substr((string) $n['title'], 0, 60, 'UTF-8')) ?></strong><br>
                <span class="adm-muted">/news-item/<?= e($n['slug']) ?></span></td>
              <td><?= e($ntypes[$n['type']]['fr']) ?></td>
              <td><?= e($ncats[$n['category']]['fr']) ?></td>
              <td><?= $n['source_kind'] === 'embed' ? 'Lien' : 'Fichier' ?></td>
              <td><span class="badge badge-<?= $n['status'] === 'published' ? 'processed' : 'read' ?>"><?= $n['status'] === 'published' ? 'publié' : 'brouillon' ?></span></td>
              <td><?= e($n['event_date'] ?? '') ?></td>
              <td class="adm-flex" style="gap:6px">
                <a class="adm-btn small" href="news.php?edit=<?= (int) $n['id'] ?>">Éditer</a>
                <?php if ($n['status'] === 'published'): ?>
                  <a class="adm-btn small secondary" href="../news-item.php?slug=<?= e(rawurlencode((string) $n['slug'])) ?>" target="_blank" rel="noopener">Voir</a>
                <?php endif; ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Changer le statut ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <button type="submit" class="adm-btn small secondary"><?= $n['status'] === 'published' ? 'Dépublier' : 'Publier' ?></button>
                </form>
                <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer définitivement cette publication ? Cette action est irréversible.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
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
