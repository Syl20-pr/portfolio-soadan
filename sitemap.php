<?php
/**
 * =============================================================================
 *  Portfolio SKS — Sitemap XML dynamique
 * =============================================================================
 *  Sert /sitemap.xml via la règle de réécriture (.htaccess) ou directement.
 *  Inclut : pages statiques FR/EN + articles publiés + projets dynamiques.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = APP_URL;
if ($base === '') {
    // Repli : déduit l'URL de base de la requête courante.
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
}

$today = date('Y-m-d');

// Pages statiques (chemins relatifs, miroir /en/ automatique).
$staticFr = ['', 'about', 'education', 'experience', 'expertise', 'projects', 'news', 'research',
             'publications', 'gallery', 'certifications', 'leadership', 'dossier', 'contact'];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($staticFr as $path): ?>
  <url>
    <loc><?= e($base . '/' . $path . ($path !== '' ? '.php' : '')) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
  </url>
  <url>
    <loc><?= e($base . '/en/' . $path . ($path !== '' ? '.php' : '')) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq>monthly</changefreq>
  </url>
<?php endforeach; ?>

<?php foreach (articles_list(true, 500) as $a): ?>
  <?php if (empty($a['source_page'])): // les pages importées ont déjà leur URL statique ?>
  <url>
    <loc><?= e($base . '/article.php?slug=' . rawurlencode((string) $a['slug'])) ?></loc>
    <lastmod><?= e(date('Y-m-d', strtotime((string) ($a['updated_at'] ?? $a['published_at'] ?? $today)))) ?></lastmod>
    <changefreq>yearly</changefreq>
  </url>
  <?php endif; ?>
<?php endforeach; ?>

<?php foreach (projects_list(true, 200) as $p): ?>
  <url>
    <loc><?= e($base . '/projet/' . rawurlencode((string) $p['slug'])) ?></loc>
    <lastmod><?= e(date('Y-m-d', strtotime((string) ($p['updated_at'] ?? $today)))) ?></lastmod>
    <changefreq>yearly</changefreq>
  </url>
<?php endforeach; ?>

<?php foreach (news_list(true, null, 200) as $nw): ?>
  <url>
    <loc><?= e($base . '/news-item.php?slug=' . rawurlencode((string) $nw['slug'])) ?></loc>
    <lastmod><?= e(date('Y-m-d', strtotime((string) ($nw['updated_at'] ?? $today)))) ?></lastmod>
    <changefreq>monthly</changefreq>
  </url>
<?php endforeach; ?>
</urlset>
