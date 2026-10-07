<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — En-tête partagé
 * =============================================================================
 *
 *  Reproduit à l'identique le <head> et le <header> des pages statiques
 *  existantes. Les chemins sont résolus via $BASE ('' à la racine, '../'
 *  depuis /en, etc.) afin de préserver strictement le rendu d'origine.
 *
 *  Variables attendues avant l'inclusion :
 *    $BASE        préfixe de chemin relatif ('', '../', '../../')
 *    $PAGE        identifiant de page ('index', 'about', 'contact', ...)
 *    $LANG        'fr' ou 'en'
 *    $TITLE       titre <title>
 *    $DESCRIPTION meta description
 *    $CANONICAL   (optionnel) URL canonique relative
 *    $NOINDEX     (optionnel) true => noindex
 *    $BODY_CLASS  (optionnel) classes du <body>
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

$BASE = $BASE ?? '';
$PAGE = $PAGE ?? 'index';
$LANG = $LANG ?? 'fr';
$TITLE = $TITLE ?? 'SOADAN Koffi Sylvain | SKS';
$DESCRIPTION = $DESCRIPTION ?? '';
$CANONICAL = $CANONICAL ?? null;
$NOINDEX = $NOINDEX ?? false;
$BODY_CLASS = $BODY_CLASS ?? '';

// Libellés de navigation selon la langue.
$NAV = $LANG === 'en'
  ? [
    'index' => 'Home',
    'about' => 'About',
    'education' => 'Education',
    'experience' => 'Experience',
    'expertise' => 'Expertise',
    'projects' => 'Projects',
    'research' => 'Research',
    'contact' => 'Contact',
  ]
  : [
    'index' => 'Accueil',
    'about' => 'À propos',
    'education' => 'Formation',
    'experience' => 'Expérience',
    'expertise' => 'Expertise',
    'projects' => 'Projets',
    'research' => 'Recherche',
    'contact' => 'Contact',
  ];

$ariaNav = $LANG === 'en' ? 'Main navigation' : 'Navigation principale';
$ariaLang = $LANG === 'en' ? 'Language' : 'Langue';
$ariaHome = $LANG === 'en'
  ? 'SKS — SOADAN Koffi Sylvain, home'
  : 'SKS — SOADAN Koffi Sylvain, accueil';
$skipLabel = $LANG === 'en' ? 'Skip to main content' : 'Aller au contenu principal';
$menuLabel = $LANG === 'en' ? 'Open menu' : 'Ouvrir le menu';
$cvLabel = $LANG === 'en' ? 'Download my CV' : 'Télécharger mon CV';

// Préfixe de navigation relative pour les liens du menu et de la marque.
$navBase = ($LANG === 'en')
  ? ($BASE === '../../' ? '../' : '')
  : $BASE;

// Détermination propre de la cible miroir FR / EN
$scriptRel = '';
if (!empty($_SERVER['SCRIPT_FILENAME'])) {
    $scriptFilename = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']) ?: $_SERVER['SCRIPT_FILENAME']);
    $rootNorm = str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH);
    if (strpos($scriptFilename, $rootNorm) === 0) {
        $scriptRel = ltrim(substr($scriptFilename, strlen($rootNorm)), '/');
    }
}
if ($scriptRel === '') {
    $scriptRel = ($PAGE === 'index' ? 'index.php' : $PAGE . '.php');
}
$frRelPath = preg_replace('#^en/#i', '', $scriptRel);

$frUrl = $BASE . $frRelPath;
$enUrl = $BASE . 'en/' . $frRelPath;

// Fichier CV selon la langue.
$cvFile = $LANG === 'en' ? CV_FILE_EN : CV_FILE_FR;
$cvHref = $BASE . 'documents/' . $cvFile;
?>
<!doctype html>
<html lang="<?= e($LANG) ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($TITLE) ?></title>
  <?php if ($DESCRIPTION !== ''): ?>
    <meta name="description" content="<?= e($DESCRIPTION) ?>">
  <?php endif; ?>
  <meta name="author" content="SOADAN Koffi Sylvain">
  <?php if ($NOINDEX): ?>
    <meta name="robots" content="noindex">
  <?php endif; ?>
  <?php if ($CANONICAL !== null): ?>
    <link rel="canonical" href="<?= e($CANONICAL) ?>">
  <?php endif; ?>
  <link rel="icon" type="image/png" href="<?= e($BASE) ?>assets/logo/favicon.png">
  <link rel="apple-touch-icon" href="<?= e($BASE) ?>assets/logo/apple-touch-icon.png">
  <link rel="stylesheet" href="<?= e($BASE) ?>assets/css/global.css">
  <script src="<?= e($BASE) ?>assets/js/global.js" defer></script>
</head>

<body<?= $BODY_CLASS !== '' ? ' class="' . e($BODY_CLASS) . '"' : '' ?>>
  <a class="skip-link" href="#main"><?= e($skipLabel) ?></a>

  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="<?= e($navBase) ?>index.php" aria-label="<?= e($ariaHome) ?>">
        <img class="brand-logo" src="<?= e($BASE) ?>assets/logo/logo-horizontal.png" alt="SKS — Sylvain Koffi SOADAN">
      </a>
      <nav class="site-nav" id="site-nav" aria-label="<?= e($ariaNav) ?>">
        <?php foreach ($NAV as $key => $label): ?>
          <a href="<?= e($navBase . $key) ?>.php" <?= $key === $PAGE ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="nav-tools">
        <div class="lang-switch" role="group" aria-label="<?= e($ariaLang) ?>">
          <a href="<?= e($frUrl) ?>" <?= $LANG === 'fr' ? ' aria-current="true"' : '' ?> hreflang="fr">FR</a>
          <a href="<?= e($enUrl) ?>" <?= $LANG === 'en' ? ' aria-current="true"' : '' ?> hreflang="en">EN</a>
        </div>
        <a class="btn btn-primary" href="<?= e($cvHref) ?>" download data-cv><span
            data-cv-text><?= e($cvLabel) ?></span></a>
        <button class="menu-btn" type="button" aria-label="<?= e($menuLabel) ?>" aria-controls="site-nav"
          aria-expanded="false">&#9776;</button>
      </div>
    </div>
  </header>