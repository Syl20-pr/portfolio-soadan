<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : Accueil (photos du diaporama + vidéo)
 * =============================================================================
 *  Photos d'accueil : rubrique DÉDIÉE (table home_photos, migration 005),
 *  distincte de la galerie — ajout multiple, remplacement, suppression,
 *  activation, ordre, légende/texte alternatif FR/EN côte à côte.
 *  Vidéo de présentation : UNE par langue (FR/EN), indépendantes — embed
 *  (YouTube/Vimeo, liste blanche) ou MP4 téléversé + affiche + WebVTT.
 *  CSRF partout ; upload_handle() (MIME réel, noms aléatoires).
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'un_members.php';
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

            // ============================================================
            //  Photos d'accueil
            // ============================================================
            if ($action === 'photos_upload') {
                $files = $_FILES['photos'] ?? null;
                if (is_array($files) && isset($files['name']) && is_array($files['name'])) {
                    $ok = 0; $ko = 0;
                    foreach ($files['name'] as $i => $nm) {
                        if (empty($nm)) continue;
                        $one = [
                            'name' => (string) $files['name'][$i], 'type' => (string) $files['type'][$i],
                            'tmp_name' => (string) $files['tmp_name'][$i],
                            'error' => (int) $files['error'][$i], 'size' => (int) $files['size'][$i],
                        ];
                        $up = upload_handle($one, 'image');
                        if ($up['ok']) {
                            $pdo->prepare(
                                'INSERT INTO home_photos (filename, caption_fr, caption_en, alt_fr, alt_en)
                                 VALUES (:f, :cf, :ce, :af, :ae)'
                            )->execute([
                                ':f' => $up['filename'],
                                ':cf' => mb_substr(trim((string) ($_POST['caption_fr'][$i] ?? '')), 0, 255, 'UTF-8'),
                                ':ce' => mb_substr(trim((string) ($_POST['caption_en'][$i] ?? '')), 0, 255, 'UTF-8'),
                                ':af' => mb_substr(trim((string) ($_POST['alt_fr'][$i] ?? '')), 0, 255, 'UTF-8'),
                                ':ae' => mb_substr(trim((string) ($_POST['alt_en'][$i] ?? '')), 0, 255, 'UTF-8'),
                            ]);
                            $ok++;
                        } else {
                            $ko++;
                        }
                    }
                    $flash = "$ok photo(s) ajoutée(s) au diaporama."
                        . ($ko > 0 ? " $ko rejetée(s) : format ou taille invalide." : '');
                    $flashType = $ko > 0 && $ok === 0 ? 'err' : 'ok';
                } else {
                    $flash = 'Aucun fichier reçu.';
                    $flashType = 'err';
                }
            } elseif ($action === 'photo_replace') {
                // Remplace le fichier d'une photo existante (l'ancien est supprimé).
                $pid = (int) ($_POST['photo_id'] ?? 0);
                $up = upload_handle($_FILES['photo'] ?? null, 'image');
                if ($pid > 0 && $up['ok']) {
                    $old = $pdo->prepare('SELECT filename FROM home_photos WHERE id = :id');
                    $old->execute([':id' => $pid]);
                    $fn = (string) ($old->fetchColumn() ?: '');
                    if ($fn !== '') {
                        upload_delete('image', $fn);
                    }
                    $pdo->prepare('UPDATE home_photos SET filename = :f WHERE id = :id')
                        ->execute([':f' => $up['filename'], ':id' => $pid]);
                    $flash = 'Photo remplacée.';
                } elseif ($up['error'] !== 'missing') {
                    $flash = 'Remplacement : fichier invalide (JPEG/PNG/WebP — 5 Mo max).';
                    $flashType = 'err';
                }
            } elseif ($action === 'photo_update') {
                $pid = (int) ($_POST['photo_id'] ?? 0);
                $pdo->prepare(
                    'UPDATE home_photos SET caption_fr = :cf, caption_en = :ce, alt_fr = :af, alt_en = :ae,
                        active = :ac, sort_order = :so WHERE id = :id'
                )->execute([
                    ':cf' => mb_substr(trim((string) ($_POST['caption_fr'] ?? '')), 0, 255, 'UTF-8'),
                    ':ce' => mb_substr(trim((string) ($_POST['caption_en'] ?? '')), 0, 255, 'UTF-8'),
                    ':af' => mb_substr(trim((string) ($_POST['alt_fr'] ?? '')), 0, 255, 'UTF-8'),
                    ':ae' => mb_substr(trim((string) ($_POST['alt_en'] ?? '')), 0, 255, 'UTF-8'),
                    ':ac' => isset($_POST['active']) ? 1 : 0,
                    ':so' => (int) ($_POST['sort_order'] ?? 0),
                    ':id' => $pid,
                ]);
                $flash = 'Photo mise à jour.';
            } elseif ($action === 'photo_toggle') {
                $pid = (int) ($_POST['photo_id'] ?? 0);
                $on = (int) ($_POST['active'] ?? 0) === 1;
                $pdo->prepare('UPDATE home_photos SET active = :ac WHERE id = :id')
                    ->execute([':ac' => $on ? 1 : 0, ':id' => $pid]);
                $flash = $on ? 'Photo activée.' : 'Photo désactivée.';
            } elseif ($action === 'photo_delete') {
                $pid = (int) ($_POST['photo_id'] ?? 0);
                $st = $pdo->prepare('SELECT filename FROM home_photos WHERE id = :id');
                $st->execute([':id' => $pid]);
                $fn = (string) ($st->fetchColumn() ?: '');
                if ($fn !== '') {
                    upload_delete('image', $fn);
                    $pdo->prepare('DELETE FROM home_photos WHERE id = :id')->execute([':id' => $pid]);
                    $flash = 'Photo supprimée.';
                }
            } elseif ($action === 'slideshow_settings') {
                $sec = (int) ($_POST['home_slide_seconds'] ?? 6);
                setting_set($pdo, 'home_slide_seconds', (string) ($sec >= 2 && $sec <= 60 ? $sec : 6));
                setting_set($pdo, 'home_transition', ($_POST['home_transition'] ?? 'fade') === 'slide' ? 'slide' : 'fade');
                setting_set($pdo, 'banner_enabled', isset($_POST['banner_enabled']) ? '1' : '0');
                // Drapeaux facultatifs : uniquement ceux cochés (défaut : aucun).
                $codes = [];
                foreach ((array) ($_POST['banner_flags_sel'] ?? []) as $c) {
                    $c = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $c) ?? '');
                    if (strlen($c) === 2 && isset(un_member_states()[$c])) {
                        $codes[] = $c;
                    }
                }
                setting_set($pdo, 'banner_flags_codes', implode(',', $codes));
                $flash = 'Réglages du diaporama enregistrés.';
            }

            // ============================================================
            //  Vidéo de présentation (par langue)
            // ============================================================
            elseif ($action === 'video_section_toggle') {
                setting_set($pdo, 'home_video_enabled', isset($_POST['home_video_enabled']) ? '1' : '0');
                $flash = 'Section vidéo de l’accueil ' . (isset($_POST['home_video_enabled']) ? 'activée.' : 'masquée.');
            } elseif ($action === 'video_save') {
                $vl = normalize_lang($_POST['video_lang'] ?? 'fr');
                $vk = ($_POST['video_source_kind'] ?? 'embed') === 'file' ? 'file' : 'embed';
                $vt = mb_substr(trim((string) ($_POST['video_title'] ?? '')), 0, 255, 'UTF-8');
                $vx = mb_substr(trim((string) ($_POST['video_excerpt'] ?? '')), 0, 500, 'UTF-8');
                $errs = [];

                $embedUrl = null; $mediaFile = null; $mediaMime = null;
                $posterImg = trim((string) ($_POST['existing_poster'] ?? ''));
                $vttFile = trim((string) ($_POST['existing_vtt'] ?? ''));

                if ($vk === 'embed') {
                    $embedUrl = trim((string) ($_POST['video_embed_url'] ?? ''));
                    if (!news_embed_valid($embedUrl)) {
                        $errs[] = 'URL d’intégration invalide ou plate-forme non autorisée (YouTube, Vimeo).';
                    } else {
                        $embedUrl = (string) filter_var($embedUrl, FILTER_SANITIZE_URL);
                    }
                } else {
                    if (!empty($_FILES['video_file']['name'])) {
                        $up = upload_handle($_FILES['video_file'] ?? null, 'video');
                        if ($up['ok']) {
                            // remplace l'ancien MP4 si présent
                            $prev = home_video_get($vl);
                            if ($prev !== null && !empty($prev['media_file'])) {
                                upload_delete('video', (string) $prev['media_file']);
                            }
                            $mediaFile = $up['filename'];
                            $mediaMime = $up['mime'];
                        } elseif ($up['error'] === 'too_large') {
                            $errs[] = 'Vidéo trop volumineuse (limite : ' . (int) setting('news_max_video_mb', '64') . ' Mo).';
                        } else {
                            $errs[] = 'Fichier vidéo invalide (MP4 attendu).';
                        }
                    } else {
                        $prev = home_video_get($vl);
                        if ($prev !== null && $prev['source_kind'] === 'file') {
                            $mediaFile = (string) $prev['media_file'];
                            $mediaMime = (string) $prev['media_mime'];
                        } else {
                            $errs[] = 'Aucun fichier vidéo fourni.';
                        }
                    }
                    // Sous-titres WebVTT facultatifs.
                    if (!empty($_FILES['vtt_file']['name'])) {
                        $up = upload_handle($_FILES['vtt_file'] ?? null, 'vtt');
                        if ($up['ok']) {
                            $vttFile = $up['filename'];
                        } elseif ($up['error'] !== 'missing') {
                            $errs[] = 'Fichier de sous-titres invalide (WebVTT .vtt attendu).';
                        }
                    }
                }

                // Affiche (facultative, recommandée).
                if (!empty($_FILES['poster']['name'])) {
                    $up = upload_handle($_FILES['poster'] ?? null, 'image');
                    if ($up['ok']) {
                        if ($posterImg !== '') {
                            upload_delete('image', $posterImg);
                        }
                        $posterImg = $up['filename'];
                    } elseif ($up['error'] !== 'missing') {
                        $errs[] = 'Affiche : fichier invalide (JPEG/PNG/WebP — 5 Mo max).';
                    }
                }

                if ($errs !== []) {
                    $flash = implode(' ', $errs);
                    $flashType = 'err';
                } else {
                    $enabled = isset($_POST['video_enabled']) ? 1 : 0;
                    $exists = $pdo->prepare('SELECT id FROM home_video WHERE lang = :l');
                    $exists->execute([':l' => $vl]);
                    if ($exists->fetchColumn()) {
                        $pdo->prepare(
                            'UPDATE home_video SET enabled = :en, source_kind = :sk, embed_url = :eu,
                                media_file = :mf, media_mime = :mm, poster_image = :pi, vtt_file = :vf,
                                title = :ti, excerpt = :ex WHERE lang = :l'
                        )->execute([
                            ':en' => $enabled, ':sk' => $vk, ':eu' => $embedUrl,
                            ':mf' => $mediaFile, ':mm' => $mediaMime, ':pi' => $posterImg !== '' ? $posterImg : null,
                            ':vf' => $vttFile !== '' ? $vttFile : null,
                            ':ti' => $vt, ':ex' => $vx, ':l' => $vl,
                        ]);
                    } else {
                        $pdo->prepare(
                            'INSERT INTO home_video (lang, enabled, source_kind, embed_url, media_file,
                                media_mime, poster_image, vtt_file, title, excerpt)
                             VALUES (:l, :en, :sk, :eu, :mf, :mm, :pi, :vf, :ti, :ex)'
                        )->execute([
                            ':l' => $vl, ':en' => $enabled, ':sk' => $vk, ':eu' => $embedUrl,
                            ':mf' => $mediaFile, ':mm' => $mediaMime, ':pi' => $posterImg !== '' ? $posterImg : null,
                            ':vf' => $vttFile !== '' ? $vttFile : null, ':ti' => $vt, ':ex' => $vx,
                        ]);
                    }
                    $flash = 'Vidéo de présentation (' . strtoupper($vl) . ') enregistrée.';
                }
            }
        }
        header('Location: home.php', true, 303);
        exit;
    }

    $photos = db_column_exists('home_photos', 'filename')
        ? db()->query('SELECT * FROM home_photos ORDER BY sort_order, id DESC')->fetchAll()
        : [];
    $videoFr = home_video_get('fr');
    $videoEn = home_video_get('en');
} catch (Throwable $e) {
    log_event('error', 'Admin home: failure');
    $flash = 'Une erreur est survenue.';
    $flashType = 'err';
    $photos = [];
    $videoFr = $videoEn = null;
}

admin_header('Accueil', 'home');
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<!-- ========================= PHOTOS D'ACCUEIL ========================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Photos d’accueil (<?= count($photos) ?>)</h2>
  <p class="adm-help">Rubrique dédiée au diaporama de la page d’accueil — distincte de la galerie. Formats : JPEG, PNG, WebP — 5 Mo max par fichier.</p>

  <form method="POST" enctype="multipart/form-data" action="home.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="photos_upload">
    <div class="adm-field">
      <label for="photos">Ajouter une ou plusieurs photos d’un coup</label>
      <input id="photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp" required>
    </div>
    <button type="submit" class="adm-btn">Téléverser</button>
  </form>
  <p class="adm-help">Les légendes FR/EN et le texte alternatif se complètent après le téléversement, dans la liste ci-dessous.</p>
</div>

<div class="adm-card">
  <?php if (empty($photos)): ?>
    <p class="adm-muted">Aucune photo d’accueil pour le moment — l’accueil affiche une image de secours.</p>
  <?php else: ?>
    <?php foreach ($photos as $ph): ?>
      <div class="adm-card" style="background:var(--ivory)">
        <div class="adm-flex" style="align-items:flex-start;gap:16px">
          <img class="adm-thumb" style="width:180px;height:110px" src="../uploads/images/<?= e($ph['filename']) ?>" alt="">
          <form method="POST" action="home.php" style="flex:1 1 520px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="photo_update">
            <input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">

            <?php // ===== Légendes et textes alternatifs FR/EN côte à côte ===== ?>
            <div class="adm-duo">
              <div class="adm-lang fr">
                <?php admin_lang_head('fr', 'Français'); ?>
                <div class="adm-field">
                  <label>Légende</label>
                  <input class="adm-input" name="caption_fr" maxlength="255" value="<?= e($ph['caption_fr']) ?>">
                </div>
                <div class="adm-field">
                  <label>Texte alternatif (accessibilité)</label>
                  <input class="adm-input" name="alt_fr" maxlength="255" value="<?= e($ph['alt_fr']) ?>">
                </div>
              </div>
              <div class="adm-lang en">
                <?php admin_lang_head('en', 'English'); ?>
                <div class="adm-field">
                  <label>Caption</label>
                  <input class="adm-input" name="caption_en" maxlength="255" value="<?= e($ph['caption_en']) ?>">
                </div>
                <div class="adm-field">
                  <label>Alt text (accessibility)</label>
                  <input class="adm-input" name="alt_en" maxlength="255" value="<?= e($ph['alt_en']) ?>">
                </div>
              </div>
            </div>
            <?php admin_copy_script_once(); ?>

            <div class="adm-grid2" style="margin-top:12px">
              <div class="adm-field">
                <label>Ordre d’affichage</label>
                <input type="number" class="adm-input" name="sort_order" value="<?= (int) $ph['sort_order'] ?>">
              </div>
              <div class="adm-field">
                <label style="font-weight:normal;margin-top:22px;display:block">
                  <input type="checkbox" name="active" <?= !empty($ph['active']) ? 'checked' : '' ?>> Active dans le diaporama
                </label>
              </div>
            </div>
            <p class="adm-flex">
              <button type="submit" class="adm-btn">Enregistrer</button>
            </p>
          </form>
          <div>
            <span class="badge badge-<?= $ph['active'] ? 'processed' : 'new' ?>"><?= $ph['active'] ? 'active' : 'inactive' ?></span>
            <form method="POST" enctype="multipart/form-data" action="home.php" style="margin-top:10px">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="photo_replace">
              <input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">
              <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" style="max-width:220px;font-size:12px">
              <button type="submit" class="adm-btn small secondary">Remplacer</button>
            </form>
            <form method="POST" action="home.php" style="margin-top:8px" onsubmit="return confirm('Désactiver/activer cette photo ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="photo_toggle">
              <input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">
              <input type="hidden" name="active" value="<?= empty($ph['active']) ? '1' : '0' ?>">
              <button type="submit" class="adm-btn small"><?= empty($ph['active']) ? 'Activer' : 'Désactiver' ?></button>
            </form>
            <form method="POST" action="home.php" style="margin-top:8px" onsubmit="return confirm('Supprimer définitivement cette photo ?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="photo_delete">
              <input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">
              <button type="submit" class="adm-btn small danger">Supprimer</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- ========================= RÉGLAGES DU DIAPORAMA ========================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Réglages du diaporama</h2>
  <form method="POST" action="home.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="slideshow_settings">
    <div class="adm-grid2">
      <div class="adm-field">
        <label for="home_slide_seconds">Durée d’affichage de chaque photo (secondes, 2–60)</label>
        <input id="home_slide_seconds" type="number" name="home_slide_seconds" class="adm-input" min="2" max="60"
               value="<?= e(setting('home_slide_seconds', '6')) ?>" style="width:auto">
      </div>
      <div class="adm-field">
        <label for="home_transition">Type de transition</label>
        <select id="home_transition" name="home_transition" class="adm-select" style="width:auto">
          <option value="fade" <?= setting('home_transition', 'fade') === 'fade' ? 'selected' : '' ?>>Fondu enchaîné</option>
          <option value="slide" <?= setting('home_transition', 'fade') === 'slide' ? 'selected' : '' ?>>Glissement</option>
        </select>
      </div>
    </div>
    <p>
      <label style="font-weight:normal"><input type="checkbox" name="banner_enabled" <?= setting('banner_enabled', '1') === '1' ? 'checked' : '' ?>> Diaporama activé sur l’accueil</label>
    </p>
    <?php // Drapeaux facultatifs : aucun par défaut ?>
    <div class="adm-field">
      <label>Drapeaux éventuels à intercaler (aucun par défaut)</label>
      <input id="flagSearch" type="search" class="adm-input" placeholder="Rechercher un État : Togo, Brazil…" autocomplete="off" style="max-width:320px">
      <div id="flagList" style="max-height:220px;overflow:auto;border:1px solid var(--line);border-radius:4px;padding:10px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:4px;margin-top:8px">
        <?php
        $selectedFlags = array_filter(array_map('trim', explode(',', setting('banner_flags_codes', ''))));
        foreach (un_member_states() as $code => $names):
            [$nameFr, $nameEn] = $names;
        ?>
          <label style="font-weight:normal;font-size:14px" data-search="<?= e(mb_strtolower($nameFr . ' ' . $nameEn . ' ' . $code, 'UTF-8')) ?>">
            <input type="checkbox" name="banner_flags_sel[]" value="<?= e($code) ?>" <?= in_array($code, $selectedFlags, true) ? 'checked' : '' ?>>
            <?= e($nameFr) ?> <span class="adm-muted">(<?= e($nameEn) ?>)</span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <button type="submit" class="adm-btn">Enregistrer les réglages</button>
  </form>
</div>
<script>
(function () {
  var q = document.getElementById('flagSearch');
  if (!q) return;
  q.addEventListener('input', function () {
    var n = q.value.toLowerCase();
    document.querySelectorAll('#flagList label[data-search]').forEach(function (lab) {
      lab.style.display = (!n || lab.getAttribute('data-search').indexOf(n) !== -1) ? '' : 'none';
    });
  });
})();
</script>

<!-- ========================= VIDÉO DE PRÉSENTATION ========================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Vidéo de présentation</h2>
  <p class="adm-help">Une vidéo par langue (FR et EN indépendantes) — si la version anglaise n’est pas renseignée, rien ne s’affiche sur la page anglaise. Lecture jamais automatique avec le son ; le lecteur externe ne se charge qu’après un clic.</p>
  <form method="POST" action="home.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="video_section_toggle">
    <p>
      <label style="font-weight:normal"><input type="checkbox" name="home_video_enabled" <?= setting('home_video_enabled', '1') === '1' ? 'checked' : '' ?> onchange="this.form.submit()"> Afficher la section « Me découvrir en vidéo » sur l’accueil</label>
    </p>
  </form>

  <?php foreach (['fr' => 'Français', 'en' => 'English'] as $vLang => $vLabel): ?>
    <?php $v = ${'video' . strtoupper($vLang)}; ?>
    <form method="POST" enctype="multipart/form-data" action="home.php" style="border-top:1px dashed var(--line);padding-top:14px;margin-top:14px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="video_save">
      <input type="hidden" name="video_lang" value="<?= e($vLang) ?>">
      <p class="adm-flex">
        <span class="adm-lang-tag"><?= e($vLang) ?></span>
        <strong style="color:var(--navy)">Vidéo — <?= e($vLabel) ?></strong>
      </p>
      <div class="adm-grid2">
        <div class="adm-field">
          <label>Source</label>
          <label style="font-weight:normal;display:block"><input type="radio" name="video_source_kind" value="embed" <?= (!$v || $v['source_kind'] === 'embed') ? 'checked' : '' ?>> Lien à intégrer (YouTube, Vimeo — recommandé pour plus de 1–2 min)</label>
          <label style="font-weight:normal;display:block"><input type="radio" name="video_source_kind" value="file" <?= ($v && $v['source_kind'] === 'file') ? 'checked' : '' ?>> Fichier MP4 court téléversé (<?= (int) setting('news_max_video_mb', '64') ?> Mo max)</label>
        </div>
        <div class="adm-field">
          <label for="video_embed_url_<?= e($vLang) ?>">Lien d’intégration (https://…)</label>
          <input id="video_embed_url_<?= e($vLang) ?>" name="video_embed_url" class="adm-input" maxlength="500"
                 value="<?= e($v['embed_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=…">
          <label for="video_file_<?= e($vLang) ?>" style="margin-top:10px">Fichier MP4</label>
          <input id="video_file_<?= e($vLang) ?>" type="file" name="video_file" accept=".mp4,video/mp4">
        </div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field">
          <label for="video_title_<?= e($vLang) ?>">Titre affiché</label>
          <input id="video_title_<?= e($vLang) ?>" name="video_title" class="adm-input" maxlength="255" value="<?= e($v['title'] ?? '') ?>">
        </div>
        <div class="adm-field">
          <label for="video_excerpt_<?= e($vLang) ?>">Courte description</label>
          <input id="video_excerpt_<?= e($vLang) ?>" name="video_excerpt" class="adm-input" maxlength="500" value="<?= e($v['excerpt'] ?? '') ?>">
        </div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field">
          <label for="poster_<?= e($vLang) ?>">Affiche (image d’aperçu — JPEG/PNG/WebP, 5 Mo max)</label>
          <?php if (!empty($v['poster_image'])): ?>
            <p><img class="adm-thumb" src="../uploads/images/<?= e($v['poster_image']) ?>" alt=""></p>
            <input type="hidden" name="existing_poster" value="<?= e($v['poster_image']) ?>">
          <?php endif; ?>
          <input id="poster_<?= e($vLang) ?>" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="adm-field">
          <label for="vtt_<?= e($vLang) ?>">Sous-titres WebVTT (facultatifs — fichiers téléversés uniquement)</label>
          <?php if (!empty($v['vtt_file'])): ?>
            <p class="adm-help">Sous-titres actuels : <?= e($v['vtt_file']) ?></p>
            <input type="hidden" name="existing_vtt" value="<?= e($v['vtt_file']) ?>">
          <?php endif; ?>
          <input id="vtt_<?= e($vLang) ?>" type="file" name="vtt_file" accept=".vtt,text/vtt">
        </div>
      </div>
      <p>
        <label style="font-weight:normal"><input type="checkbox" name="video_enabled" <?= ($v && !empty($v['enabled'])) ? 'checked' : '' ?>> Afficher cette vidéo (<?= e($vLang) ?>) sur l’accueil</label>
      </p>
      <p><button type="submit" class="adm-btn">Enregistrer la vidéo <?= e(strtoupper($vLang)) ?></button></p>
    </form>
  <?php endforeach; ?>
</div>

<?php admin_footer(); ?>
