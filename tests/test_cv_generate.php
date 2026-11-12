<?php
// Test CLI : simule la génération du CV dans les deux langues.
declare(strict_types=1);
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/cvdata.php';
require_once __DIR__ . '/../includes/cvpdf.php';

foreach (['fr', 'en'] as $lang) {
    $cv = cv_build($lang);
    $pdf = cv_pdf_render($cv, (string) $cv['template'], 'S');
    $ok = is_string($pdf) && $pdf !== '' && strncmp($pdf, '%PDF', 4) === 0;
    echo $lang, ': ', $ok ? 'OK (' . strlen($pdf) . " octets)" : 'ECHEC', PHP_EOL;
    if ($lang === 'en') {
        // Vérifie que la traduction auto a produit du contenu anglais
        foreach ($cv['sections'] as $s) {
            if ($s['key'] === 'summary') {
                echo '  summary (EN): ', mb_substr((string) $s['data']['text'], 0, 80), PHP_EOL;
            }
        }
        echo '  translation ok=', var_export($cv['translation']['ok'] ?? null, true),
             ' translated=', (string) ($cv['translation']['translated'] ?? -1),
             ' error=', (string) ($cv['translation']['error'] ?? ''), PHP_EOL;
    }
}
