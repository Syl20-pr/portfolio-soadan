<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Page de confirmation (FR)
 * =============================================================================
 *  Rendu visuel strictement identique à thanks.html.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'page.php';

$LANG = 'fr';
$PAGE = 'thanks';
$BASE = '';
$NOINDEX = true;
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];

require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<main id="main">
  <section class="section">
    <div class="container-narrow center">
      <img src="assets/logo/logo-symbol.png" alt="" style="width:64px;margin:0 auto 2rem">
      <p class="eyebrow">Confirmation</p>
      <h1 class="section-title">Merci pour votre message.</h1>
      <p class="section-lead" style="margin-inline:auto">Votre prise de contact a bien été transmise. Je reviendrai
        vers vous dans les meilleurs délais.</p>
      <p class="mt-4"><a class="btn btn-primary" href="index.php">Retour au portfolio</a></p>
    </div>
  </section>
</main>

<?php require __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>