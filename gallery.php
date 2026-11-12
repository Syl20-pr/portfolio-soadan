<?php
/**
 * =============================================================================
 *  Portfolio SKS — Galerie photographique publique
 * =============================================================================
 *  N'affiche QUE les photos approuvées. Lightbox simple en vanilla JS
 *  (aucune dépendance externe), navigation clavier incluse.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

$LANG = current_lang();
$PAGE = 'gallery';
$BASE = '';

$activeCat = isset($_GET['cat']) ? (string) $_GET['cat'] : '';
$gcats = gallery_categories();
if (!isset($gcats[$activeCat])) {
    $activeCat = '';
}

try {
    $photos = gallery_public($activeCat !== '' ? $activeCat : null);
} catch (Throwable $e) {
    log_event('error', 'Public gallery: unavailable');
    $photos = [];
}

$TITLE = ($LANG === 'en' ? 'Gallery — ' : 'Galerie — ') . 'SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = $LANG === 'en'
    ? 'Photographic gallery of SOADAN Koffi Sylvain: conferences, diplomatic activities, community engagement, trainings, projects and public speaking.'
    : 'Galerie photographique de SOADAN Koffi Sylvain : conférences, activités diplomatiques, engagement associatif, formations, projets et interventions publiques.';

require __DIR__ . '/includes/header.php';
?>

<style>
  .gal-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:14px; }
  .gal-item { position:relative; overflow:hidden; border-radius:8px; border:1px solid #dfe3e8; background:#f2f1ec; cursor:zoom-in; }
  .gal-item img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; transition:transform .25s ease; }
  .gal-item:hover img { transform:scale(1.03); }
  .gal-cap { position:absolute; inset:auto 0 0 0; padding:10px 12px; background:linear-gradient(transparent, rgba(10,36,59,.85)); color:#fff; font-size:14px; }
  .gal-filters { display:flex; flex-wrap:wrap; gap:10px; margin:18px 0 26px; }
  .gal-filters a { text-decoration:none; }
  .lb-backdrop { position:fixed; inset:0; background:rgba(6,20,33,.94); z-index:1000; display:none; align-items:center; justify-content:center; flex-direction:column; padding:24px; }
  .lb-backdrop.open { display:flex; }
  .lb-backdrop img { max-width:92vw; max-height:78vh; border-radius:6px; object-fit:contain; }
  .lb-caption { color:#e8e6e1; margin-top:14px; text-align:center; max-width:760px; font-size:15px; }
  .lb-close { position:absolute; top:18px; right:24px; color:#fff; font-size:34px; cursor:pointer; background:none; border:none; }
  .lb-nav { position:absolute; top:50%; transform:translateY(-50%); color:#fff; font-size:40px; background:none; border:none; cursor:pointer; padding:12px; }
  .lb-prev { left:10px; } .lb-next { right:10px; }
</style>

<main id="main">
  <section class="hero" style="padding:4rem 0 1rem">
    <div class="container">
      <p class="eyebrow"><?= $LANG === 'en' ? 'In the field' : 'Sur le terrain' ?></p>
      <h1 class="hero-title" style="font-size:clamp(2rem,5vw,3rem)">
        <?= $LANG === 'en' ? 'Gallery' : 'Galerie' ?>
      </h1>
      <p class="hero-lead"><?= $LANG === 'en'
          ? 'Conferences, diplomatic engagements, community work and public interventions.'
          : 'Conférences, activités diplomatiques, engagements associatifs et interventions publiques.' ?></p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="gal-filters">
        <a class="btn btn-sm <?= $activeCat === '' ? 'btn-primary' : 'btn-secondary' ?>"
           href="<?= e($LANG === 'en' ? 'en/gallery.php' : 'gallery.php') ?>">
          <?= $LANG === 'en' ? 'All' : 'Toutes' ?>
        </a>
        <?php foreach ($gcats as $key => $labels): ?>
          <a class="btn btn-sm <?= $activeCat === $key ? 'btn-primary' : 'btn-secondary' ?>"
             href="?cat=<?= e($key) ?>"><?= e($LANG === 'en' ? $labels['en'] : $labels['fr']) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if (empty($photos)): ?>
        <p class="section-lead"><?= $LANG === 'en' ? 'No photographs published yet.' : 'Aucune photographie publiée pour le moment.' ?></p>
      <?php else: ?>
        <div class="gal-grid" id="galGrid">
          <?php foreach ($photos as $i => $ph): ?>
            <figure class="gal-item" data-index="<?= $i ?>">
              <img src="<?= e('uploads/images/' . $ph['filename']) ?>"
                   alt="<?= e($ph['alt_text'] !== '' ? $ph['alt_text'] : $ph['caption']) ?>"
                   data-full="<?= e('uploads/images/' . $ph['filename']) ?>"
                   data-caption="<?= e($ph['caption']) ?>"
                   data-where="<?= e(trim($ph['location'] . ($ph['taken_on'] ? ' · ' . date('m/Y', strtotime((string) $ph['taken_on'])) : ''), ' ·')) ?>"
                   loading="lazy">
              <?php if ((string) $ph['caption'] !== ''): ?>
                <figcaption class="gal-cap"><?= e($ph['caption']) ?></figcaption>
              <?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<div class="lb-backdrop" id="lbBackdrop" role="dialog" aria-modal="true" aria-label="Photo">
  <button class="lb-close" id="lbClose" aria-label="Fermer">&times;</button>
  <button class="lb-nav lb-prev" id="lbPrev" aria-label="Précédente">&#8249;</button>
  <img id="lbImg" src="" alt="">
  <button class="lb-nav lb-next" id="lbNext" aria-label="Suivante">&#8250;</button>
  <div class="lb-caption" id="lbCaption"></div>
</div>

<script>
(function () {
  var items = Array.prototype.slice.call(document.querySelectorAll('.gal-item'));
  if (!items.length) return;
  var backdrop = document.getElementById('lbBackdrop');
  var imgEl = document.getElementById('lbImg');
  var capEl = document.getElementById('lbCaption');
  var current = 0;

  function show(i) {
    current = (i + items.length) % items.length;
    var it = items[current];
    var im = it.querySelector('img');
    imgEl.src = im.getAttribute('data-full');
    imgEl.alt = im.alt;
    var cap = im.getAttribute('data-caption') || '';
    var where = im.getAttribute('data-where') || '';
    capEl.textContent = [cap, where].filter(Boolean).join(' — ');
  }
  function open(i) { show(i); backdrop.classList.add('open'); document.body.style.overflow = 'hidden'; }
  function close() { backdrop.classList.remove('open'); document.body.style.overflow = ''; }

  items.forEach(function (it, i) {
    it.addEventListener('click', function () { open(i); });
    it.setAttribute('tabindex', '0');
    it.addEventListener('keydown', function (ev) { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); open(i); } });
  });
  document.getElementById('lbClose').addEventListener('click', close);
  document.getElementById('lbPrev').addEventListener('click', function () { show(current - 1); });
  document.getElementById('lbNext').addEventListener('click', function () { show(current + 1); });
  backdrop.addEventListener('click', function (ev) { if (ev.target === backdrop) close(); });
  document.addEventListener('keydown', function (ev) {
    if (!backdrop.classList.contains('open')) return;
    if (ev.key === 'Escape') close();
    if (ev.key === 'ArrowLeft') show(current - 1);
    if (ev.key === 'ArrowRight') show(current + 1);
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
