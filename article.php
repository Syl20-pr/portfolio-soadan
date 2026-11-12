<?php
/**
 * =============================================================================
 *  Portfolio SKS — Publication individuelle (générée dynamiquement)
 * =============================================================================
 *  Affiche un article publié ?slug=… . Supporte l'aperçu d'administration
 *  (?preview=1, réservé à l'administrateur connecté).
 *  Les métadonnées SEO (title, description, Open Graph, JSON-LD) proviennent
 *  des champs saisis dans l'administration.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = current_lang();
$BASE = '';

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$isPreview = isset($_GET['preview']) && admin_logged_in();
$article = [];

try {
    $article = $slug !== '' ? article_by_slug($slug, !$isPreview) : [];
} catch (Throwable $e) {
    log_event('error', 'Article page: unavailable');
    $article = [];
}

// Article introuvable → page 404 propre.
if ($article === [] || $article === null) {
    header('HTTP/1.1 404 Not Found', true, 404);
}

$catField = $LANG === 'en' ? 'category_en' : 'category_fr';
$catLabel = (string) ($article[$catField] ?? ($article['category_fr'] ?? ''));
$seoTitle = (string) ($article['seo_title'] ?? '') !== '' ? (string) $article['seo_title'] : (string) ($article['title'] ?? 'Publication');
$seoDesc = (string) ($article['seo_description'] ?? '') !== '' ? (string) $article['seo_description'] : (string) ($article['excerpt'] ?? '');
$publishedAt = !empty($article['published_at']) ? date('d/m/Y', strtotime((string) $article['published_at'])) : '';
$isoDate = !empty($article['published_at']) ? date(DATE_ATOM, strtotime((string) $article['published_at'])) : '';
$appUrl = APP_URL !== '' ? APP_URL : '';

if ($article === [] || $article === null) {
    $TITLE = 'Publication introuvable — SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = '';
} else {
    $TITLE = $seoTitle . ' — SOADAN Koffi Sylvain | SKS';
    $DESCRIPTION = mb_substr($seoDesc, 0, 320, 'UTF-8');
}

require __DIR__ . '/includes/header.php';

if ($article === [] || $article === null) {
  require __DIR__ . '/includes/footer.php';
  return;
}

// Open Graph + JSON-LD injection (SEO module) pour cette page dynamique.
if ($article !== [] && $article !== null) {
    $ogTitle = e($seoTitle);
    $ogDesc = e(mb_substr($seoDesc, 0, 200, 'UTF-8'));
    $ogImage = !empty($article['cover_image'])
        ? e($appUrl . '/uploads/images/' . $article['cover_image'])
        : e($appUrl . '/assets/images/logo-horizontal.png');
    $ogUrl = e(($appUrl !== '' ? $appUrl : '') . '/article.php?slug=' . rawurlencode((string) $article['slug']));
    $personDesc = e(mb_substr(setting($LANG === 'en' ? 'bio_en' : 'bio_fr', ''), 0, 300, 'UTF-8'));
    ?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= $ogDesc ?>">
    <meta property="og:image" content="<?= $ogImage ?>">
    <?php
    // Images supplémentaires également proposées aux plateformes de partage.
    try { $ogExtras = media_images_for('article', (int) $article['id']); } catch (Throwable $e) { $ogExtras = []; }
    foreach (array_slice($ogExtras, 0, 4) as $ogExtra): ?>
    <meta property="og:image" content="<?= e($appUrl . '/uploads/images/' . $ogExtra['filename']) ?>">
    <?php endforeach; ?>
    <meta property="og:url" content="<?= $ogUrl ?>">
    <meta property="og:site_name" content="SOADAN Koffi Sylvain — Portfolio">
    <meta property="og:locale" content="<?= $LANG === 'en' ? 'en_US' : 'fr_FR' ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $ogTitle ?>">
    <meta name="twitter:description" content="<?= $ogDesc ?>">
    <meta name="twitter:image" content="<?= $ogImage ?>">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": <?= json_encode($seoTitle, JSON_UNESCAPED_UNICODE) ?>,
      "datePublished": <?= json_encode($isoDate) ?>,
      "inLanguage": <?= json_encode($LANG) ?>,
      "author": {
        "@type": "Person",
        "name": "SOADAN Koffi Sylvain",
        "jobTitle": "Digital Governance & AI Policy",
        "description": <?= json_encode($personDesc) ?>
      },
      "publisher": {
        "@type": "Person",
        "name": "SOADAN Koffi Sylvain"
      }
    }
    </script>
<?php } ?>

<main id="main">
  <?php if ($isPreview): ?>
    <div style="background:#0f6b3c;color:#fff;text-align:center;padding:8px;font-size:14px">
      Aperçu d'administration — cette page n'est visible que par vous.
    </div>
  <?php endif; ?>

  <article class="container-narrow" style="padding:4rem 0 6rem">
    <p class="eyebrow">
      <?= e($catLabel !== '' ? $catLabel : 'Publication') ?>
      <?php if ($publishedAt !== ''): ?> · <?= e($publishedAt) ?><?php endif; ?>
    </p>
    <h1 class="section-title" style="font-size:clamp(1.8rem,4.5vw,2.6rem)"><?= e($article['title']) ?></h1>
    <?php if ((string) $article['excerpt'] !== ''): ?>
      <p class="section-lead"><?= e($article['excerpt']) ?></p>
    <?php endif; ?>

    <?php if (!empty($article['cover_image'])): ?>
      <figure style="margin:2rem 0">
        <img src="<?= e('uploads/images/' . $article['cover_image']) ?>"
             alt="<?= e($article['title']) ?>"
             style="width:100%;height:auto;border-radius:8px;border:1px solid #dfe3e8"
             loading="lazy">
      </figure>
    <?php endif; ?>

    <div class="prose" style="margin-top:2rem">
      <?= $article['content_html'] /* HTML authored by the trusted administrator */ ?>
    </div>

    <?php
    // Images supplémentaires (galerie façon LinkedIn) — clic pour agrandir.
    try { $extraImgs = media_images_for('article', (int) $article['id']); } catch (Throwable $e) { $extraImgs = []; }
    if (!empty($extraImgs)): ?>
      <section class="extra-gallery" style="margin-top:2.5rem">
        <h2 style="font-size:1.2rem;color:var(--navy)"><?= $LANG === 'en' ? 'In pictures' : 'En images' ?></h2>
        <div class="exgrid">
          <?php foreach ($extraImgs as $im):
            $cap = $LANG === 'en' ? $im['caption_en'] : $im['caption_fr'];
            $alt = $LANG === 'en' ? $im['alt_en'] : $im['alt_fr'];
          ?>
            <figure class="exitem">
              <a href="<?= e('uploads/images/' . $im['filename']) ?>" target="_blank" rel="noopener">
                <img src="<?= e('uploads/images/' . $im['filename']) ?>" alt="<?= e($alt !== '' ? $alt : $cap) ?>" loading="lazy" decoding="async">
              </a>
              <?php if (trim((string) $cap) !== ''): ?><figcaption><?= e($cap) ?></figcaption><?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php
    $tags = article_tags((int) $article['id']);
    if (!empty($tags)): ?>
      <div class="tag-row" style="margin-top:2rem">
        <?php foreach ($tags as $t): ?>
          <span class="tag"><?= e(is_array($t) ? $t['name'] : $t) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="mt-4"><a class="btn btn-secondary" href="articles.php">← Toutes les publications</a></p>
  </article>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
