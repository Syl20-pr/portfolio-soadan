<?php
/**
 * =============================================================================
 *  Portfolio SKS — Diaporama d'accueil + vidéo de présentation (FR/EN)
 * =============================================================================
 *  Diaporama : photos de la rubrique « Photos d'accueil » (repli : photos de
 *  galerie marquées accueil). Drapeaux facultatifs (choisis en admin,
 *  intercalés — aucun par défaut). Une seule photo active = affichage simple,
 *  aucune photo = image de secours.
 *  Comportement : rotation auto (home_slide_seconds), pause au survol,
 *  flèches + points de navigation, balayage tactile, lazy-loading des
 *  photos suivantes, prefers-reduced-motion sans rotation automatique.
 *  Vidéo : section « Me découvrir en vidéo » — lecteur externe chargé
 *  uniquement au clic (click-to-load), lecture jamais automatique avec son.
 * =============================================================================
 */

declare(strict_types=1);

if (!function_exists('banner_render_section')) {
    function banner_render_section(string $lang, string $base): void
    {
        try {
            require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
            $banner = banner_data();
        } catch (Throwable $e) {
            log_event('error', 'Banner: data unavailable');
            return;
        }
        if (!$banner['enabled']) {
            return;
        }
        $slides = $banner['photos'][0] ?? [];
        $isEn = $lang === 'en';
        $n = count($slides);
        ?>
<section class="banner banner-slideshow" data-banner
         data-speed="<?= (int) $banner['slide_seconds'] ?>"
         data-transition="<?= e($banner['transition']) ?>"
         aria-label="<?= $isEn ? 'Photo slideshow' : 'Diaporama de photos' ?>">
  <div class="container">
    <div class="banner-head">
      <p class="eyebrow"><?= $isEn ? 'In motion' : 'En mouvement' ?></p>
      <h2 id="banner-title"><?= $isEn
          ? 'A media showcase — my photos and engagements'
          : 'Une vitrine en mouvement — mes photos et mes engagements' ?></h2>
    </div>
  </div>

  <?php if ($n === 0): ?>
    <?php // Image de secours : aucune photo configurée ?>
    <div class="banner-stage banner-fallback">
      <img src="<?= e($base . 'assets/images/photo1.jpeg') ?>"
           alt="<?= $isEn ? 'SOADAN Koffi Sylvain' : 'SOADAN Koffi Sylvain' ?>" loading="lazy">
    </div>
  <?php else: ?>
  <div class="banner-stage" tabindex="0" role="group" aria-roledescription="carousel">
    <?php foreach ($slides as $i => $ph): ?>
      <?php
      $cap = trim((string) ($isEn ? ($ph['caption_en'] ?? '') : ($ph['caption'] ?? '')));
      $isFlag = ($ph['is_flag'] ?? false);
      ?>
      <figure class="banner-slide <?= $i === 0 ? 'is-active' : '' ?> <?= $isFlag ? 'is-flag' : '' ?>"
              data-slide="<?= (int) $i ?>" <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
        <?php if ($isFlag): ?>
          <img src="<?= e($base . 'assets/flags/' . $ph['flag_code'] . '.svg') ?>" alt="" decoding="async" width="110" height="84">
        <?php else: ?>
          <img src="<?= e($base . 'uploads/images/' . $ph['filename']) ?>"
               alt="<?= e($ph['alt_text'] !== '' ? $ph['alt_text'] : $cap) ?>"
               <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
        <?php endif; ?>
        <?php if ($cap !== '' && !$isFlag): ?>
          <figcaption class="banner-cap"><?= e($cap) ?></figcaption>
        <?php endif; ?>
      </figure>
    <?php endforeach; ?>

    <?php if ($n > 1): ?>
      <button type="button" class="banner-arrow banner-prev" aria-label="<?= $isEn ? 'Previous photo' : 'Photo précédente' ?>">‹</button>
      <button type="button" class="banner-arrow banner-next" aria-label="<?= $isEn ? 'Next photo' : 'Photo suivante' ?>">›</button>
      <div class="banner-dots" role="tablist">
        <?php foreach ($slides as $i => $_): ?>
          <button type="button" class="banner-dot <?= $i === 0 ? 'is-active' : '' ?>"
                  data-dot="<?= (int) $i ?>"
                  aria-label="<?= $isEn ? 'Go to photo' : 'Aller à la photo' ?> <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>
<script src="<?= e($base) ?>assets/js/banner.js" defer></script>

<?php
// ---------------------------------------------------------------------------
//  Vidéo de présentation (affichée uniquement si la vidéo de CETTE langue
//  existe et est activée — jamais la vidéo FR sur la page EN).
// ---------------------------------------------------------------------------
try {
    $hv = home_video_get($lang);
} catch (Throwable $e) {
    $hv = null;
}
if ($hv !== null) {
    $hvKind = (string) $hv['source_kind'];
    $hvTitle = trim((string) $hv['title']);
    ?>
<section class="section section-soft home-video-section" aria-labelledby="homevideo-title">
  <div class="container-narrow">
    <div class="section-head">
      <p class="eyebrow"><?= $isEn ? 'Video' : 'Vidéo' ?></p>
      <h2 id="homevideo-title" class="section-title"><?= $isEn ? 'Discover my work in video' : 'Me découvrir en vidéo' ?></h2>
      <?php if ($hvTitle !== ''): ?><p class="section-lead"><?= e($hvTitle) ?></p><?php endif; ?>
    </div>

    <?php if ($hvKind === 'embed'): ?>
      <figure class="c2l-frame" style="position:relative;aspect-ratio:16/9;border-radius:6px;overflow:hidden;margin:0">
        <div class="c2l-facade" data-c2l-src="<?= e((string) $hv['embed_url']) ?>" data-c2l-title="<?= e($hvTitle) ?>">
          <?php if (!empty($hv['poster_image'])): ?>
            <img src="<?= e($base . 'uploads/images/' . $hv['poster_image']) ?>" alt="" loading="lazy"
                 style="width:100%;height:100%;object-fit:cover">
          <?php endif; ?>
          <button type="button" class="c2l-play"
                  aria-label="<?= $isEn ? 'Load the player (external platform)' : 'Charger le lecteur (plateforme externe)' ?>">
            ▶ <?= $isEn ? 'Play the video — external platform' : 'Lire la vidéo — plateforme externe' ?>
          </button>
        </div>
      </figure>
    <?php else: ?>
      <figure style="margin:0">
        <video controls preload="metadata" playsinline
               poster="<?= !empty($hv['poster_image']) ? e($base . 'uploads/images/' . $hv['poster_image']) : '' ?>"
               style="width:100%;border-radius:6px;display:block">
          <source src="<?= e($base . 'uploads/media/' . $hv['media_file']) ?>" type="<?= e((string) $hv['media_mime']) ?>">
          <?php if (!empty($hv['vtt_file'])): ?>
            <track kind="subtitles" srclang="<?= e($lang) ?>"
                   src="<?= e($base . 'uploads/media/' . $hv['vtt_file']) ?>"
                   label="<?= $isEn ? 'English' : 'Français' ?>" default>
          <?php endif; ?>
        </video>
      </figure>
    <?php endif; ?>
  </div>
</section>
    <?php
}
}
} // fin if (!function_exists('banner_render_section'))
// fin de banner.php
