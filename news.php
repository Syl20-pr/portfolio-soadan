<?php
/**
 * =============================================================================
 *  Portfolio SKS — Actualités : vidéos, capsules et podcasts (public)
 * =============================================================================
 *  Fil d'actualités filtrable par catégorie. Le lecteur externe n'est
 *  chargé qu'après un clic de l'internaute (page détail news-item.php).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = current_lang();
$PAGE = 'news';
$BASE = '';

$ncats = news_categories();
$activeCat = isset($_GET['cat']) ? (string) $_GET['cat'] : '';
if (!isset($ncats[$activeCat])) {
    $activeCat = '';
}

try {
    $items = news_list(true, $activeCat !== '' ? $activeCat : null, 60);
} catch (Throwable $e) {
    log_event('error', 'Public news: unavailable');
    $items = [];
}

$isEn = $LANG === 'en';
$TITLE = ($isEn ? 'News — videos, clips & podcasts — ' : 'Actualités — vidéos, capsules et podcasts — ') . 'SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = $isEn
    ? 'News feed of SOADAN Koffi Sylvain: conference talks, community activities, debates, TV appearances and podcasts.'
    : 'Fil d’actualités de SOADAN Koffi Sylvain : conférences, activités communautaires, débats, passages télévisés et podcasts.';

require __DIR__ . '/includes/header.php';
?>

<main id="main">
  <section class="hero" style="padding:4rem 0 2rem">
    <div class="container">
      <p class="eyebrow"><?= $isEn ? 'News & media' : 'Actualités & médias' ?></p>
      <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3rem)"><?= $isEn ? 'News' : 'Actualités' ?></h1>
      <p class="hero-lead"><?= $isEn
          ? 'Talks, capsules, debates, TV appearances and podcast episodes.'
          : 'Conférences, capsules, débats, passages télévisés et épisodes de podcast.' ?></p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="gal-filters">
        <a class="btn btn-sm <?= $activeCat === '' ? 'btn-primary' : 'btn-secondary' ?>"
           href="<?= $isEn ? 'en/news.php' : 'news.php' ?>"><?= $isEn ? 'All' : 'Toutes' ?></a>
        <?php foreach ($ncats as $key => $labels): ?>
          <a class="btn btn-sm <?= $activeCat === $key ? 'btn-primary' : 'btn-secondary' ?>"
             href="?cat=<?= e($key) ?>"><?= e($isEn ? $labels['en'] : $labels['fr']) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($items)): ?>
        <div class="container-narrow">
          <p class="section-lead"><?= $isEn
              ? 'No publication yet in this category. Come back soon!'
              : 'Aucune publication dans cette catégorie pour le moment. Revenez bientôt !' ?></p>
        </div>
      <?php else: ?>
        <div class="news-grid">
          <?php foreach ($items as $n):
            $ntypes = news_types();
            $typeLabel = $ntypes[$n['type']][$LANG] ?? $n['type'];
            $catLabel  = $ncats[$n['category']][$LANG] ?? $n['category'];
            $date = !empty($n['event_date']) ? $n['event_date']
                  : (!empty($n['published_at']) ? date('Y-m-d', strtotime((string) $n['published_at'])) : '');
            $dateFmt = $date !== '' ? date('d/m/Y', strtotime((string) $date)) : '';
          ?>
            <article class="news-card">
              <a class="news-card-link" href="news-item.php?slug=<?= e(rawurlencode((string) $n['slug'])) ?>">
                <?php if (!empty($n['poster_image'])): ?>
                  <img class="card-thumb" src="<?= e('uploads/images/' . $n['poster_image']) ?>" alt="" loading="lazy" decoding="async">
                <?php endif; ?>
                <p class="project-domain">
                  <span class="tag"><?= e($typeLabel) ?></span>
                  <span class="tag"><?= e($catLabel) ?></span>
                  <?php if ($dateFmt !== ''): ?> · <?= e($dateFmt) ?><?php endif; ?>
                </p>
                <h3><?= e($n['title']) ?></h3>
                <?php if ((string) $n['excerpt'] !== ''): ?>
                  <p class="project-desc"><?= e($n['excerpt']) ?></p>
                <?php endif; ?>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
