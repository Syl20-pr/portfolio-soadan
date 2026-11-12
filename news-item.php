<?php
/**
 * =============================================================================
 *  Portfolio SKS — Détail d'une publication Actualités (public)
 * =============================================================================
 *  Confidentialité : pour les liens intégrés (YouTube, Vimeo, Facebook,
 *  TikTok, Spotify, Apple), la plateforme externe n'est chargée qu'APRÈS un
 *  clic explicite de l'internaute (façade locale + bouton). Pour les fichiers
 *  MP4/MP3 téléversés, les balises natives <video>/<audio> suffisent.
 *
 *  Commentaires : modérés avant publication, désactivés par défaut ;
 *  protection CSRF + champ piège + limitation de fréquence.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = current_lang();
$BASE = '';

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$item = [];

try {
    $item = $slug !== '' ? news_by_slug($slug, true, $LANG) : [];
    if ($item !== [] && $item !== null) {
        $item = bilingual_pick($item, $LANG);
    }
} catch (Throwable $e) {
    log_event('error', 'News detail: unavailable');
    $item = [];
}

$isEn = $LANG === 'en';
$ncats = news_categories();
$ntypes = news_types();

if ($item === [] || $item === null) {
    header('HTTP/1.1 404 Not Found', true, 404);
    $TITLE = 'Publication introuvable — SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = '';
} else {
    $TITLE = (string) $item['title'] . ' — ' . ($isEn ? 'News — ' : 'Actualités — ') . 'SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = mb_substr((string) $item['excerpt'], 0, 320, 'UTF-8');
}

$appUrl = APP_URL;

require __DIR__ . '/includes/header.php';

// Balises de partage (Open Graph / Twitter) pour cette page dynamique.
if ($item !== [] && $item !== null) {
    $ogPoster = !empty($item['poster_image']) ? $appUrl . '/uploads/images/' . $item['poster_image'] : $appUrl . '/assets/images/logo-horizontal.png';
    $ogUrl = $appUrl . '/news-item.php?slug=' . rawurlencode((string) $item['slug']);
    ?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= e($TITLE) ?>">
    <meta property="og:description" content="<?= e(mb_substr((string) $item['excerpt'], 0, 200, 'UTF-8')) ?>">
    <meta property="og:image" content="<?= e($ogPoster) ?>">
    <meta property="og:url" content="<?= e($ogUrl) ?>">
    <meta property="og:site_name" content="SOADAN Koffi Sylvain — Portfolio">
    <meta property="og:locale" content="<?= $isEn ? 'en_US' : 'fr_FR' ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($TITLE) ?>">
    <meta name="twitter:description" content="<?= e(mb_substr((string) $item['excerpt'], 0, 200, 'UTF-8')) ?>">
    <meta name="twitter:image" content="<?= e($ogPoster) ?>">
<?php } ?>

<?php if ($item === [] || $item === null) {
?>
<main id="main">
  <section class="section">
    <div class="container-narrow center">
      <h1 class="section-title"><?= $isEn ? 'Publication not found' : 'Publication introuvable' ?></h1>
      <p class="mt-2"><a class="btn btn-secondary" href="news.php"><?= $isEn ? 'Back to the news feed' : 'Retour au fil d’actualités' ?></a></p>
    </div>
  </section>
</main>
<?php
    require __DIR__ . '/includes/footer.php';
    return;
}

// ---------------------------------------------------------------------------
//  Traitement du commentaire (POST) : CSRF, champ piège, rate-limit.
// ---------------------------------------------------------------------------
$commentFlash = '';
$commentOk = false;
if (is_post() && !empty($item['comments_enabled'])) {
    $flashError = static function (string $msg) use (&$commentFlash) {
        $commentFlash = $msg;
    };
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $flashError($isEn ? 'Your session has expired. Please reload the page and try again.'
                          : 'Votre session a expiré. Merci de recharger la page et de réessayer.');
    } elseif (trim((string) ($_POST['website'] ?? '')) !== '') {
        // Champ piège : les humains ne le remplissent jamais.
        $commentFlash = $isEn ? 'Message rejected.' : 'Message rejeté.';
    } else {
        $name  = mb_substr(trim((string) ($_POST['author_name'] ?? '')), 0, 120, 'UTF-8');
        $email = mb_substr(trim((string) ($_POST['author_email'] ?? '')), 0, 254, 'UTF-8');
        $body  = trim((string) ($_POST['body'] ?? ''));
        $body  = mb_substr(strip_tags($body), 0, 2000, 'UTF-8');

        if ($name === '' || mb_strlen($body, 'UTF-8') < 5) {
            $flashError($isEn ? 'Name and a message of at least 5 characters are required.'
                              : 'Nom et message (5 caractères minimum) obligatoires.');
        } else {
            try {
                $pdo = db();
                $ipHash = client_ip() !== '' ? hash_ip(client_ip()) : '';
                if ($ipHash !== '') {
                    $stmt = $pdo->prepare(
                        'SELECT COUNT(*) FROM comment_rate_limit WHERE ip_hash = :ip AND created_at > ' . sql_now_minus(10, 'MINUTE')
                    );
                    $stmt->execute([':ip' => $ipHash]);
                    if ((int) $stmt->fetchColumn() >= 3) {
                        $flashError($isEn ? 'Too many comments posted recently. Please try again later.'
                                          : 'Trop de commentaires envoyés récemment. Merci de réessayer plus tard.');
                    }
                }
                if ($commentFlash === '') {
                    $pdo->prepare(
                        'INSERT INTO news_comments (news_id, author_name, author_email, body, status, ip_hash, user_agent)
                         VALUES (:nid, :name, :mail, :body, \'pending\', :ip, :ua)'
                    )->execute([
                        ':nid'  => (int) $item['id'],
                        ':name' => $name,
                        ':mail' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '',
                        ':body' => $body,
                        ':ip'   => $ipHash !== '' ? $ipHash : null,
                        ':ua'   => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
                    ]);
                    if ($ipHash !== '') {
                        $pdo->prepare('INSERT INTO comment_rate_limit (ip_hash) VALUES (:ip)')->execute([':ip' => $ipHash]);
                    }
                    $commentOk = true;
                    $commentFlash = $isEn
                        ? 'Thank you! Your comment will appear once it has been moderated.'
                        : 'Merci ! Votre commentaire apparaîtra après modération.';
                }
            } catch (Throwable $e) {
                log_event('error', 'News comment: failure');
                $commentFlash = $isEn ? 'The comment could not be saved.'
                                      : 'Le commentaire n’a pas pu être enregistré.';
            }
        }
    }
}

$comments = [];
try {
    $comments = news_comments_approved((int) $item['id']);
} catch (Throwable $e) {
    $comments = [];
}

// ---------------------------------------------------------------------------
//  Rendu du lecteur (click-to-load pour les plateformes externes)
// ---------------------------------------------------------------------------
$type = (string) $item['type'];
$kind = (string) $item['source_kind'];

if ($kind === 'embed' && news_embed_valid((string) $item['embed_url'])) {
    $host = strtolower((string) parse_url((string) $item['embed_url'], PHP_URL_HOST));
    $ratio = str_contains($host, 'spotify') || str_contains($host, 'podcasts.apple') ? 'aspect-ratio:1/1' : 'aspect-ratio:16/9';
    $playerHtml = '<iframe src="' . e((string) $item['embed_url']) . '" title="' . e($item['title']) . '"'
        . ' style="border:0;width:100%;height:100%" allow="accelerometer; autoplay; clipboard-write;'
        . ' encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen loading="lazy">'
        . '</iframe>';
} elseif ($kind === 'file' && !empty($item['media_file'])) {
    $playerHtml = $type === 'podcast'
        ? '<audio controls preload="metadata" style="width:100%" src="uploads/media/' . e((string) $item['media_file']) . '"></audio>'
        : '<video controls preload="metadata" poster="' . (!empty($item['poster_image']) ? e('uploads/images/' . $item['poster_image']) : '') . '" style="width:100%;border-radius:6px" src="uploads/media/' . e((string) $item['media_file']) . '"></video>';
} else {
    $playerHtml = '';
}
?>

<main id="main">
  <article class="container-narrow" style="padding:4rem 0 6rem">
    <p class="eyebrow">
      <span class="tag"><?= e($ntypes[$type][$LANG] ?? $type) ?></span>
      <span class="tag"><?= e($ncats[$item['category']][$LANG] ?? $item['category']) ?></span>
      <?php
      $dateShown = !empty($item['event_date']) ? $item['event_date']
                 : (!empty($item['published_at']) ? date('Y-m-d', strtotime((string) $item['published_at'])) : '');
      if ($dateShown !== ''): ?> · <?= e(date('d/m/Y', strtotime((string) $dateShown))) ?><?php endif; ?>
      <?php if ((string) $item['location'] !== ''): ?> · <?= e($item['location']) ?><?php endif; ?>
    </p>
    <h1 class="section-title" style="font-size:clamp(1.8rem,4.5vw,2.6rem)"><?= e($item['title']) ?></h1>
    <?php if ((string) $item['excerpt'] !== ''): ?>
      <p class="section-lead"><?= e($item['excerpt']) ?></p>
    <?php endif; ?>

    <?php if ($playerHtml !== '' && $kind === 'file'): ?>
      <figure style="margin:2rem 0"><?= $playerHtml ?></figure>
    <?php elseif ($playerHtml !== '' && $kind === 'embed'): ?>
      <figure class="c2l-frame" style="margin:2rem 0;position:relative;<?= $ratio ?>">
        <div class="c2l-facade" data-c2l-src="<?= e((string) $item['embed_url']) ?>" data-c2l-title="<?= e($item['title']) ?>">
          <?php if (!empty($item['poster_image'])): ?>
            <img src="<?= e('uploads/images/' . $item['poster_image']) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">
          <?php endif; ?>
          <button type="button" class="c2l-play" aria-label="<?= $isEn ? 'Load the player (external platform)' : 'Charger le lecteur (plateforme externe)' ?>">
            ▶ <?= $isEn ? 'Load the player — external platform' : 'Charger le lecteur — plateforme externe' ?>
          </button>
        </div>
      </figure>
    <?php endif; ?>

    <div class="prose" style="margin-top:2rem">
      <?= $item['body_html'] /* HTML authored by the trusted administrator */ ?>
    </div>

    <?php // Liens de partage (sans traceur) ?>
    <div class="share-row" style="margin-top:2rem">
      <?php $shareUrl = e($appUrl . '/news-item.php?slug=' . rawurlencode((string) $item['slug'])); ?>
      <a class="btn btn-sm btn-secondary" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareUrl ?>" target="_blank" rel="noopener">LinkedIn</a>
      <a class="btn btn-sm btn-secondary" href="https://x.com/intent/post?url=<?= $shareUrl ?>" target="_blank" rel="noopener">X</a>
      <a class="btn btn-sm btn-secondary" href="mailto:?subject=<?= e(rawurlencode((string) $item['title'])) ?>&amp;body=<?= $shareUrl ?>"><?= $isEn ? 'Email' : 'Courriel' ?></a>
    </div>

    <?php // Commentaires ?>
    <section style="margin-top:3rem">
      <h2 style="font-size:1.2rem;color:var(--navy)"><?= $isEn ? 'Comments' : 'Commentaires' ?></h2>
      <?php if ($commentFlash !== ''): ?>
        <div style="padding:10px 14px;border-radius:4px;background:#e9f5ee;color:#0f6b3c;margin:10px 0"><?= e($commentFlash) ?></div>
      <?php endif; ?>

      <?php if (empty($item['comments_enabled'])): ?>
        <p class="adm-muted" style="color:var(--muted);font-size:14px"><?= $isEn
            ? 'Comments are closed for this publication.'
            : 'Les commentaires sont désactivés pour cette publication.' ?></p>
      <?php else: ?>
        <?php if (!empty($comments)): ?>
          <?php foreach ($comments as $cm): ?>
            <div style="border:1px solid var(--line);border-radius:4px;padding:12px;margin-bottom:10px">
              <strong><?= e($cm['author_name']) ?></strong>
              <span class="adm-muted" style="color:var(--muted);font-size:13px"> · <?= e(date('d/m/Y', strtotime((string) $cm['created_at']))) ?></span>
              <p style="margin:6px 0 0"><?= nl2br(e($cm['body'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="adm-muted" style="color:var(--muted);font-size:14px"><?= $isEn
              ? 'No approved comment yet.'
              : 'Aucun commentaire approuvé pour le moment.' ?></p>
        <?php endif; ?>

        <form method="POST" action="news-item.php?slug=<?= e(rawurlencode((string) $item['slug'])) ?>" style="margin-top:1.5rem">
          <?= csrf_field() ?>
          <p class="adm-help" style="font-size:13px;color:var(--muted);margin:0 0 12px"><?= $isEn
              ? 'Your comment is published only after moderation.'
              : 'Votre commentaire n’apparaîtra qu’après modération.' ?></p>
          <input class="adm-input" style="display:none" type="text" name="website" tabindex="-1" autocomplete="off">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <input class="adm-input" name="author_name" maxlength="120" required placeholder="<?= $isEn ? 'Your name' : 'Votre nom' ?>">
            <input class="adm-input" type="email" name="author_email" maxlength="254" placeholder="<?= $isEn ? 'Your email (optional, not displayed)' : 'Votre courriel (facultatif, non affiché)' ?>">
          </div>
          <textarea class="adm-input" name="body" rows="4" maxlength="2000" required
                    placeholder="<?= $isEn ? 'Your comment…' : 'Votre commentaire…' ?>"></textarea>
          <p><button type="submit" class="btn btn-primary"><?= $isEn ? 'Submit for moderation' : 'Soumettre à modération' ?></button></p>
        </form>
      <?php endif; ?>
    </section>

    <p class="mt-4"><a class="btn btn-secondary" href="news.php"><?= $isEn ? '← Back to the news feed' : '← Retour au fil d’actualités' ?></a></p>
  </article>
</main>

<style>
  .c2l-facade { position:absolute; inset:0; background:var(--navy); border-radius:6px; display:flex; align-items:center; justify-content:center; }
  .c2l-facade img { border-radius:6px; opacity:.8; }
  .c2l-play { position:relative; z-index:2; background:var(--green); color:#fff; border:0; border-radius:26px; padding:12px 22px; cursor:pointer; font-family:inherit; font-size:15px; }
  .c2l-play:hover { background:var(--green-600); }
  .news-card-link { text-decoration:none; color:inherit; display:block; }
  .share-row { display:flex; gap:10px; flex-wrap:wrap; }
</style>
<script>
(function () {
  // Click-to-load : l'iframe de la plateforme externe n'est créée QU'ICI,
  // au clic — aucune requête vers la plateforme avant consentement.
  document.querySelectorAll('.c2l-facade').forEach(function (facade) {
    facade.querySelector('.c2l-play').addEventListener('click', function () {
      var src = facade.getAttribute('data-c2l-src') || '';
      if (!src) return;
      var iframe = document.createElement('iframe');
      iframe.src = src;
      iframe.title = facade.getAttribute('data-c2l-title') || '';
      iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen');
      iframe.allowFullscreen = true;
      iframe.style.border = '0';
      iframe.style.width = '100%';
      iframe.style.height = '100%';
      iframe.loading = 'lazy';
      facade.parentElement.innerHTML = '';
      facade.parentElement.appendChild(iframe);
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
