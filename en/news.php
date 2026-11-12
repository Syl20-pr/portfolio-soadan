<?php
/**
 * =============================================================================
 *  Portfolio SKS — News: videos, clips & podcasts (public, English mirror)
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/content.php';

$LANG = 'en';
$PAGE = 'news';
$BASE = '../';

$ncats = news_categories();
$activeCat = isset($_GET['cat']) ? (string) $_GET['cat'] : '';
if (!isset($ncats[$activeCat])) {
    $activeCat = '';
}

try {
    $items = news_list(true, $activeCat !== '' ? $activeCat : null, 60, 'en'); // version EN complète uniquement
} catch (Throwable $e) {
    log_event('error', 'Public news: unavailable');
    $items = [];
}

$TITLE = 'News — videos, clips & podcasts — SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = 'News feed of SOADAN Koffi Sylvain: conference talks, community activities, debates, TV appearances and podcasts.';

require __DIR__ . '/../includes/header.php';
?>

<main id="main">
  <section class="hero" style="padding:4rem 0 2rem">
    <div class="container">
      <p class="eyebrow">News &amp; media</p>
      <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3rem)">News</h1>
      <p class="hero-lead">Talks, capsules, debates, TV appearances and podcast episodes.</p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="gal-filters">
        <a class="btn btn-sm <?= $activeCat === '' ? 'btn-primary' : 'btn-secondary' ?>"
           href="en/news.php">All</a>
        <?php foreach ($ncats as $key => $labels): ?>
          <a class="btn btn-sm <?= $activeCat === $key ? 'btn-primary' : 'btn-secondary' ?>"
             href="?cat=<?= e($key) ?>"><?= e($labels['en']) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($items)): ?>
        <div class="container-narrow">
          <p class="section-lead">No publication yet in this category. Come back soon!</p>
        </div>
      <?php else: ?>
        <div class="news-grid">
          <?php foreach ($items as $n):
            $n = bilingual_pick($n, 'en'); // champs EN si disponibles
            $ntypes = news_types();
            $typeLabel = $ntypes[$n['type']]['en'] ?? $n['type'];
            $catLabel  = $ncats[$n['category']]['en'] ?? $n['category'];
            $date = !empty($n['event_date']) ? $n['event_date']
                  : (!empty($n['published_at']) ? date('Y-m-d', strtotime((string) $n['published_at'])) : '');
            $dateFmt = $date !== '' ? date('m/d/Y', strtotime((string) $date)) : '';
          ?>
            <article class="news-card">
              <a class="news-card-link" href="../news-item.php?slug=<?= e(rawurlencode((string) $n['slug'])) ?>&amp;lang=en">
                <?php if (!empty($n['poster_image'])): ?>
                  <img class="card-thumb" src="<?= e('../uploads/images/' . $n['poster_image']) ?>" alt="" loading="lazy" decoding="async">
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

<style>
  .news-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px,1fr)); gap:18px; }
  .news-card { border:1px solid var(--line); border-radius:var(--radius); background:var(--paper); overflow:hidden; }
  .news-card-link { text-decoration:none; color:inherit; display:block; height:100%; }
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
