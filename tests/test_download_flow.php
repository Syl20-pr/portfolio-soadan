<?php
declare(strict_types=1);
// Simule la logique de choix du candidat CV en mode download (cvgenerate.php).
require __DIR__ . '/../includes/content.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/cvdata.php';
require __DIR__ . '/../includes/cvpdf.php';

function resolve_candidate(string $lang): string
{
    try {
        $cv = cv_build($lang);
        $hasContent = false;
        foreach ($cv['sections'] as $section) {
            if (!empty($section['data']['items'])) { $hasContent = true; break; }
        }
        if ($hasContent) {
            return 'GENERATED (à la volée, langue ' . $lang . ')';
        }
    } catch (Throwable $e) {}
    try {
        $state = cv_state_get($lang);
        if (($state['status'] ?? '') === 'published' && !empty($state['published_file'])
            && is_file(ROOT_PATH . '/uploads/cv/' . basename((string) $state['published_file']))) {
            return 'publié: ' . $state['published_file'];
        }
        $active = cv_active($lang);
        if ($active !== null && !empty($active['filename'])
            && is_file(ROOT_PATH . '/uploads/cv/' . basename((string) $active['filename']))) {
            return 'actif: ' . $active['filename'];
        }
    } catch (Throwable $e) {}
    return 'statique: ' . ($lang === 'en' ? CV_FILE_EN : CV_FILE_FR);
}

echo 'FR → ', resolve_candidate('fr'), PHP_EOL;
echo 'EN → ', resolve_candidate('en'), PHP_EOL;
