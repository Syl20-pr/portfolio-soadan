<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Confirmation page (EN)
 * =============================================================================
 *  Rendu visuel strictement identique à en/thanks.html.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'page.php';

$LANG = 'en';
$PAGE = 'thanks';
$BASE = '../';
$NOINDEX = true;
$META = page_meta($PAGE, $LANG);
$TITLE = $META[0];

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php';
?>

<main id="main">
  <section class="section">
    <div class="container-narrow center">
      <img src="../assets/logo/logo-symbol.png" alt="" style="width:64px;margin:0 auto 2rem">
      <p class="eyebrow">Confirmation</p>
      <h1 class="section-title">Thank you for your message.</h1>
      <p class="section-lead" style="margin-inline:auto">Your enquiry has been sent successfully. I will get back to
        you as soon as possible.</p>
      <p class="mt-4"><a class="btn btn-primary" href="index.php">Back to the portfolio</a></p>
    </div>
  </section>
</main>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>