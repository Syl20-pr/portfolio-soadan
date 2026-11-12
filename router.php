<?php
declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

// 1. Redirection de /admin vers /admin/
if ($uri === '/admin') {
    header('Location: /admin/', true, 301);
    exit;
}

// 2. Fichier ou dossier existant
if ($uri !== '/' && file_exists($file)) {
    if (is_dir($file)) {
        $index = rtrim($file, '/\\') . DIRECTORY_SEPARATOR . 'index.php';
        if (file_exists($index)) {
            require $index;
            return true;
        }
    }
    return false; // PHP sert le fichier statique directement
}

// 3. URL sans extension .php (ex: /about -> /about.php)
if (file_exists($file . '.php')) {
    require $file . '.php';
    return true;
}

// 4. Publication dynamique : /publication/<slug>
if (preg_match('#^/publication/([a-z0-9-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/article.php';
    return true;
}

// 5. Projet dynamique : /projets/<slug>
if (preg_match('#^/projets/([a-z0-9-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/projet.php';
    return true;
}

// 6. Sitemap XML
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return true;
}

// 7. Racine
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

return false;

