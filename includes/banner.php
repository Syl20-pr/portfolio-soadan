<?php
/**
 * =============================================================================
 *  Portfolio SKS — Diaporama de la page d'accueil (partiel partagé FR/EN)
 * =============================================================================
 *  Une seule bannière : un diaporama à fondu de MES photos uniquement
 *  (gallery_photos.show_home = 1 et approved = 1, gérées dans l'admin).
 *  Des drapeaux peuvent être glissés parmi les images, mais UNIQUEMENT ceux
 *  choisis explicitement dans l'admin (réglage banner_flags_codes).
 *  Aucune ligne de drapeaux défilante n'est affichée.
 *
 *  Interaction : pause au survol, balayage tactile (scroll-snap),
 *  prefers-reduced-motion respecté via CSS.
 * =============================================================================
 */

declare(strict_types=1);

if (!function_exists('banner_render_section')) {
    /**
     * Affiche le diaporama d'accueil dans la langue courante.
     */
    function banner_render_section(string $lang, string $base): void
    {
        try {
            require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
            $banner = banner_data();
        } catch (Throwable $e) {
            log_event('error', 'Banner: data unavailable');
            return; // repli silencieux : la page d'accueil reste fonctionnelle
        }
        if (!$banner['enabled']) {
            return;
        }
        // Une seule liste : les photos choisies (+ éventuels drapeaux choisis).
        $slides = $banner['photos'][0] ?? [];
        if ($slides === []) {
            return;
        }
        $isEn = $lang === 'en';
        ?>
<section class="banner banner-slideshow" aria-label="<?= $isEn ? 'Photo slideshow' : 'Diaporama de photos' ?>" data-banner data-speed="<?= max(2, (int) round((int) $banner['speed_seconds'] / max(1, count($slides)))) ?>">
  <div class="container">
    <div class="banner-head">
      <p class="eyebrow"><?= $isEn ? 'In motion' : 'En mouvement' ?></p>
      <h2 id="banner-title"><?= $isEn
          ? 'A media showcase — my photos and engagements'
          : 'Une vitrine en mouvement — mes photos et mes engagements' ?></h2>
    </div>
  </div>

  <div class="banner-stage" tabindex="0" role="group"
       aria-roledescription="carousel"
       aria-label="<?= $isEn ? 'Photo slideshow' : 'Diaporama de photos' ?>">
    <?php foreach ($slides as $i => $ph): ?>
      <?php
      $cap = trim((string) ($isEn ? ($ph['caption_en'] ?? '') : ($ph['caption'] ?? '')));
      $isFlag = ($ph['is_flag'] ?? false);
      ?>
      <figure class="banner-slide <?= $i === 0 ? 'is-active' : '' ?>" data-slide="<?= (int) $i ?>" <?= $i === 0 ? '' : 'aria-hidden="true"' ?>>
        <img src="<?= e($isFlag
            ? $base . 'assets/flags/' . $ph['flag_code'] . '.svg'
            : $base . 'uploads/images/' . $ph['filename']) ?>"
             alt="<?= e($isFlag ? '' : ($ph['alt_text'] !== '' ? $ph['alt_text'] : $cap)) ?>"
             <?= $i === 0 ? '' : 'loading="lazy"' ?> decoding="async">
        <?php if ($cap !== ''): ?>
          <figcaption class="banner-cap"><?= e($cap) ?></figcaption>
        <?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>
</section>
<script src="<?= e($base) ?>assets/js/banner.js" defer></script>
<?php
    }
}
