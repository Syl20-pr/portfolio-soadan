<?php
/**
 * =============================================================================
 *  Portfolio SKS — Publications dynamiques : liste publique (français)
 * =============================================================================
 *  Liste les articles publiés. Les policy briefs statiques existants
 *  restent servis par publications/*.php et figurent aussi dans cette liste.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = 'fr';
$PAGE = 'publications';
$BASE = '';

try {
    $articles = articles_list(true, 60);
} catch (Throwable $e) {
    log_event('error', 'Public articles: unavailable');
    $articles = [];
}

$TITLE = 'Publications — SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = "Publications, notes d'analyse et articles de SOADAN Koffi Sylvain sur la gouvernance numérique africaine, les politiques de l'IA, la cybersécurité et la diplomatie technologique.";

require __DIR__ . '/includes/header.php';
?>

<main id="main">
  <section class="hero" style="padding:4rem 0 2rem">
    <div class="container">
      <p class="eyebrow">Publications &amp; notes d'analyse</p>
      <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3rem)">Publications</h1>
      <p class="hero-lead">Analyses et réflexions sur la gouvernance du numérique, l'intelligence artificielle,
        la cybersécurité et les politiques publiques en Afrique.</p>
    </div>
  </section>

  <section class="section" style="padding-top:1rem">
    <div class="container">
      <?php if (empty($articles)): ?>
        <div class="container-narrow">
          <p class="section-lead">Les premières publications arrivent bientôt. En attendant, consultez la
            <a class="link-inline" href="research.php">page recherche</a>.</p>
        </div>
      <?php else: ?>
        <div class="project-list">
          <?php foreach ($articles as $a): ?>
            <?php
            $catLabel = $a['category_fr'] ?? '';
            $href = 'article.php?slug=' . rawurlencode((string) $a['slug']);
            // Les publications importées gardent leur page statique d'origine.
            if (!empty($a['source_page'])) {
                $href = $a['source_page'];
            }
            ?>
            <article class="project-row" data-reveal>
              <?php if (!empty($a['cover_image'])): ?>
                <img class="card-thumb" src="<?= e('uploads/images/' . $a['cover_image']) ?>"
                     alt="" loading="lazy" decoding="async">
              <?php endif; ?>
              <p class="project-domain">
                <?= e($catLabel !== '' ? $catLabel : 'Analyse') ?>
                <?php if (!empty($a['published_at'])): ?>
                  · <?= e(date('d/m/Y', strtotime((string) $a['published_at']))) ?>
                <?php endif; ?>
              </p>
              <h3><?= e($a['title']) ?></h3>
              <?php if ((string) $a['excerpt'] !== ''): ?>
                <p class="project-desc"><?= e($a['excerpt']) ?></p>
              <?php endif; ?>
              <p class="mt-2 mb-0"><a class="link-inline" href="<?= e($href) ?>">Lire la publication →</a></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
