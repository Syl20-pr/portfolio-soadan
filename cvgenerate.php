<?php
/**
 * =============================================================================
 *  Portfolio SKS — Génération / téléchargement du CV dynamique
 * =============================================================================
 *  Point d'entrée PUBLIC en lecture seule :
 *    ?lang=fr|en & mode=preview|download
 *
 *  - mode=download : sert le PDF publié ; à défaut, repli sur l'historique
 *    cv_documents (migration 001), puis sur les PDF statiques d'origine.
 *    La publication est le seul moyen de rendre visible une version de CV.
 *  - mode=preview  : réservé à l'administrateur connecté — génère le PDF
 *    à la volée depuis les données courantes (sans l'enregistrer).
 *
 *  Aucune fonction de modification n'est accessible ici.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cvdata.php';
require_once __DIR__ . '/includes/cvpdf.php';

$lang = normalize_lang($_GET['lang'] ?? 'fr');
$mode = ($_GET['mode'] ?? 'download') === 'preview' ? 'preview' : 'download';
$slugName = 'CV_SOADAN_Koffi_Sylvain_' . strtoupper($lang) . '.pdf';

if ($mode === 'preview') {
    // Aperçu : administrateur uniquement (aucune écriture, aucune fuite de données).
    if (!admin_logged_in()) {
        http_response_code(403);
        exit('Forbidden');
    }
    $cv = cv_build($lang);
    $pdf = cv_pdf_render($cv, (string) $cv['template'], 'I');
    exit;
}

// mode=download : URL STABLE du CV (le site pointe toujours ici).
// Priorité : GÉNÉRATION À LA VOLÉE dans la langue demandée (le CV suit donc la
// langue du site : version anglaise traduite automatiquement du français),
// puis repli : CV publié (migration 002) → CV actif (migration 001) → PDF
// statique d'origine. Réponse jamais mise en cache : une nouvelle publication
// ou une modification de données est immédiatement reflétée.
try {
    $cv = cv_build($lang);
    // Ne générer à la volée QUE si la base contient réellement du contenu :
    // au moins une rubrique (expérience, formation…) alimentée. Un simple
    // profil ne suffit pas : un PDF quasi vide écraserait à tort le CV
    // publié / statique de la langue demandée.
    $hasContent = false;
    foreach ($cv['sections'] as $section) {
        if (!empty($section['data']['items'])) {
            $hasContent = true;
            break;
        }
    }
    $pdf = $hasContent ? cv_pdf_render($cv, (string) $cv['template'], 'S') : '';
    if (is_string($pdf) && $pdf !== '' && strncmp($pdf, '%PDF', 4) === 0) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $slugName . '"');
        header('Content-Length: ' . strlen($pdf));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $pdf;
        exit;
    }
} catch (Throwable $e) {
    // Génération impossible (base indisponible, etc.) : repli ci-dessous.
}
$candidates = [];
try {
    $state = cv_state_get($lang);
    if (($state['status'] ?? '') === 'published' && !empty($state['published_file'])) {
        $candidates[] = ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv'
            . DIRECTORY_SEPARATOR . basename((string) $state['published_file']);
    }
    $active = cv_active($lang);
    if ($active !== null && !empty($active['filename'])) {
        $candidates[] = ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv'
            . DIRECTORY_SEPARATOR . basename((string) $active['filename']);
    }
} catch (Throwable $e) {
    // Base indisponible : on retombe sur le PDF statique d'origine.
}
$candidates[] = ROOT_PATH . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR
    . ($lang === 'en' ? CV_FILE_EN : CV_FILE_FR);

$path = '';
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $path = $candidate;
        break;
    }
}
if ($path === '') {
    http_response_code(404);
    exit('CV not available');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $slugName . '"');
header('Content-Length: ' . (string) filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
readfile($path);
exit;
