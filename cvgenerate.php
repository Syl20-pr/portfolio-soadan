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
// ORDRE DE PRIORITÉ (une publication remplace IMMÉDIATEMENT l'ancienne) :
//   1. fichier publié par le générateur (cv_state.published_file, migr. 002) ;
//   2. CV actif de l'historique cv_documents (migr. 001) ;
//   3. PDF statique d'origine de documents/.
// Il n'y a PLUS de génération à la volée en mode download (c'était la cause
// du bug « le CV ne change pas après publication » : le flux ignorait le
// fichier publié et regénérait depuis les données brutes). L'aperçu à la
// volée reste disponible en mode=preview, réservé à l'administrateur.
// Aucune erreur/warning PHP dans le flux du fichier : le PDF doit commencer
// par %PDF, sinon le navigateur le considère corrompu.
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
while (ob_get_level() > 0) {
    ob_end_clean(); // supprime toute sortie parasite accumulée (BOM, notice…)
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
    // Aucun CV disponible : réponse claire (HTML), jamais une page blanche
    // ni un fichier binaire corrompu.
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    $isEn = $lang === 'en';
    exit('<!doctype html><html lang="' . e($lang) . '"><meta charset="utf-8">'
        . '<title>' . ($isEn ? 'CV not available' : 'CV non disponible') . '</title>'
        . '<body style="font-family:Georgia,serif;text-align:center;padding:4rem">'
        . '<h1>' . ($isEn ? 'CV temporarily unavailable' : 'CV temporairement indisponible') . '</h1>'
        . '<p>' . ($isEn
            ? 'No CV is currently published. Please come back later or contact me directly at <a href="mailto:sylvainsoadan3@gmail.com">sylvainsoadan3@gmail.com</a>.'
            : 'Aucun CV n’est actuellement publié. Revenez plus tard ou écrivez-moi directement à <a href="mailto:sylvainsoadan3@gmail.com">sylvainsoadan3@gmail.com</a>.')
        . '</p><p><a href="javascript:history.back()">' . ($isEn ? 'Back' : 'Retour') . '</a></p></body></html>');
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
