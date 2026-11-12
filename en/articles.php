<?php
/**
 * =============================================================================
 *  Portfolio SKS — Publications (English mirror)
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/content.php';

$LANG = 'en';
$PAGE = 'publications';
$BASE = '../';

try {
    $articles = articles_list(true, 60);
} catch (Throwable $e) {
    log_event('error', 'Public articles (EN): unavailable');
    $articles = [];
}

$TITLE = 'Publications — SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = "Articles, analysis notes and essays by SOADAN Koffi Sylvain on African digital governance, AI policy, cybersecurity and technology diplomacy.";

require __DIR__ . '/../includes/header.php';
?>

<main id="main">
  <section class="hero" style="padding:4rem 0 2rem">
    <div class="container">
      <p class="eyebrow">Publications &amp; analysis notes</p>
      <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3rem)">Publications</h1>
      <p class="hero-lead">Analyses and reflections on digital governance, artificial intelligence,
        cybersecurity and public policy in Africa.</p>
    </div>
  </section>

  <section class="section" style="padding-top:1rem">
    <div class="container">
      <?php if (empty($articles)): ?>
        <div class="container-narrow">
          <p class="section-lead">First publications coming soon. In the meantime, see the
            <a class="link-inline" href="research.php">research page</a>.</p>
        </div>
      <?php else: ?>
        <div class="project-list">
          <?php foreach ($articles as $a): ?>
            <?php
            $catLabel = $a['category_en'] ?? '';
            // English mirror: publications live under /en/ too when a static page exists.
            $href = 'publications/' . basename((string) ($a['source_page'] ?? '')) ?: '';
            $dynamicHref = '../article.php?slug=' . rawurlencode((string) $a['slug']) . '&lang=en';
            $href = !empty($a['source_page'])
                ? 'publications/' . basename((string) $a['source_page'])
                : $dynamicHref;
            ?>
            <article class="project-row" data-reveal>
              <?php if (!empty($a['cover_image'])): ?>
                <img class="card-thumb" src="<?= e('../uploads/images/' . $a['cover_image']) ?>"
                     alt="" loading="lazy" decoding="async">
              <?php endif; ?>
              <p class="project-domain">
                <?= e($catLabel !== '' ? $catLabel : 'Analysis') ?>
                <?php if (!empty($a['published_at'])): ?>
                  · <?= e(date('m/d/Y', strtotime((string) $a['published_at']))) ?>
                <?php endif; ?>
              </p>
              <h3><?= e($a['title']) ?></h3>
              <?php if ((string) $a['excerpt'] !== ''): ?>
                <p class="project-desc"><?= e($a['excerpt']) ?></p>
              <?php endif; ?>
              <p class="mt-2 mb-0"><a class="link-inline" href="<?= e($href) ?>">Read the publication →</a></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
