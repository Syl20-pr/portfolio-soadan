<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Pied de page partagé
 * =============================================================================
 *
 *  Reproduit à l'identique le <footer> des pages statiques existantes.
 *  Variables attendues :
 *    $BASE  préfixe de chemin relatif ('', '../', '../../')
 *    $LANG  'fr' ou 'en'
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

$BASE = $BASE ?? '';
$LANG = $LANG ?? 'fr';

$navBase = ($LANG === 'en')
  ? ($BASE === '../../' ? '../' : '')
  : $BASE;

$scriptRel = '';
if (!empty($_SERVER['SCRIPT_FILENAME'])) {
    $scriptFilename = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']);
    $rootNorm = str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH);
    if (strpos($scriptFilename, $rootNorm) === 0) {
        $scriptRel = ltrim(substr($scriptFilename, strlen($rootNorm)), '/');
    }
}
if ($scriptRel === '') {
    $scriptRel = 'index.php';
}
$frRelPath = preg_replace('#^en/#i', '', $scriptRel);

if ($LANG === 'en') {
  $fJourney = 'Overview';
  $fLinks = 'Professional links';
  $fCert = 'Certifications';
  $fLead = 'Leadership';
  $fDossier = 'Professional Dossier';
  $fPubs = 'Publications';
  $fEmail = 'Email';
  $fContact = 'Contact';
  $fLangName = 'Français';
  $fLangHref = $BASE . $frRelPath;
  $footerCv = CV_FILE_EN;
  $fPubsHref = $navBase . 'publications/policy-brief-01.php';
} else {
  $fJourney = 'Parcours';
  $fLinks = 'Liens professionnels';
  $fCert = 'Certifications';
  $fLead = 'Leadership';
  $fDossier = 'Dossier professionnel';
  $fPubs = 'Publications';
  $fEmail = 'Email';
  $fContact = 'Contact';
  $fLangName = 'English';
  $fLangHref = $BASE . 'en/' . $frRelPath;
  $footerCv = CV_FILE_FR;
  $fPubsHref = $navBase . 'publications/policy-brief-01.php';
}
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="<?= e($BASE) ?>assets/logo/logo-horizontal.png" alt="SKS — Sylvain Koffi SOADAN">
        <p class="footer-name">SKS — Sylvain Koffi SOADAN</p>
        <p class="footer-role">African Digital Governance &amp; AI Policy</p>
      </div>
      <div>
        <p class="footer-heading"><?= e($fJourney) ?></p>
        <ul class="footer-links">
          <li><a href="<?= e($navBase) ?>certifications.php"><?= e($fCert) ?></a></li>
          <li><a href="<?= e($navBase) ?>leadership.php"><?= e($fLead) ?></a></li>
          <li><a href="<?= e($navBase) ?>dossier.php"><?= e($fDossier) ?></a></li>
          <li><a href="<?= e($fPubsHref) ?>"><?= e($fPubs) ?></a></li>
        </ul>
      </div>
      <div>
        <p class="footer-heading"><?= e($fLinks) ?></p>
        <ul class="footer-links">
          <li><a href="https://www.linkedin.com/in/koffi-sylvain-soadan-747572356/" target="_blank"
              rel="noopener">LinkedIn</a></li>
          <li><a class="footer-link-disabled" href="#" aria-disabled="true" tabindex="-1">GitHub</a></li>
          <li><a href="mailto:sylvainsoadan3@gmail.com"><?= e($fEmail) ?></a></li>
          <li><a href="<?= e($BASE) ?>documents/<?= e($footerCv) ?>" download>CV</a></li>
          <li><a href="<?= e($navBase) ?>contact.php"><?= e($fContact) ?></a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Sylvain Koffi SOADAN. All rights reserved.</span>
      <span>Lomé, Togo — <a href="<?= e($fLangHref) ?>"
          hreflang="<?= $LANG === 'en' ? 'fr' : 'en' ?>"><?= e($fLangName) ?></a></span>
    </div>
  </div>
</footer>
</body>

</html>