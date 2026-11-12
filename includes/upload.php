<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Téléversement sécurisé de fichiers
 * =============================================================================
 *  Contrôles appliqués à chaque upload :
 *    - présence effective du fichier et code d'erreur PHP ;
 *    - taille maximale (constante, moindre que les limites serveur) ;
 *    - type MIME RÉEL déterminé par finfo (jamais l'extension déclarée) ;
 *    - nom final généré aléatoirement (jamais le nom d'origine) ;
 *    - dossier de destination sans exécution PHP (.htaccess dédié).
 * =============================================================================
 */

declare(strict_types=1);

// --- Images & PDF : limite unique (5 Mo) -------------------------------------
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
define('UPLOAD_IMAGE_TYPES', ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']);
define('UPLOAD_PDF_TYPE', 'application/pdf');

// --- Médias Actualités : limites configurables via .env ----------------------
// MB_MAX_UPLOAD_VIDEO / MB_MAX_UPLOAD_AUDIO (voir .env.example et
// DOCUMENTATION-HOSTING.md). La limite est aussi bornée côté site_settings
// (news_max_video_mb / news_max_audio_mb) ; le moindre des deux s'applique.
define('UPLOAD_VIDEO_TYPES', ['video/mp4' => 'mp4']);
define('UPLOAD_AUDIO_TYPES', ['audio/mpeg' => 'mp3', 'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a',
                              'audio/ogg' => 'ogg', 'audio/vorbis' => 'oga']);
define('UPLOAD_MAX_VIDEO_BYTES', (int) env('MB_MAX_UPLOAD_VIDEO', 64) * 1024 * 1024);
define('UPLOAD_MAX_AUDIO_BYTES', (int) env('MB_MAX_UPLOAD_AUDIO', 24) * 1024 * 1024);
// Sous-titres WebVTT (vidéo de présentation) — petit plafond fixe.
define('UPLOAD_VTT_TYPE', 'text/vtt');
define('UPLOAD_MAX_VTT_BYTES', 512 * 1024); // 512 Ko

/**
 * Retourne le répertoire absolu d'une catégorie d'upload (créé si absent).
 */
function upload_dir(string $kind): string
{
    $sub = [
        'image' => 'images',
        'cv'    => 'cv',
        'video' => 'media',   // capsules MP4 (Actualités, accueil)
        'audio' => 'media',   // podcasts MP3/M4A
        'vtt'   => 'media',   // sous-titres WebVTT
    ][$kind] ?? 'misc';
    $dir = ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $sub;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

/**
 * Traite un champ de fichier envoyé ($_FILES['nom']).
 *
 * @param array{tmp_name:string,name:string,size:int,error:int,type:string}|null $file
 * @param string $kind   'image' (JPEG/PNG/WebP), 'cv' (PDF),
 *                       'video' (MP4, limite UPLOAD_MAX_VIDEO_BYTES)
 *                       ou 'audio' (MP3/M4A/OGG, limite UPLOAD_MAX_AUDIO_BYTES)
 * @return array{ok:bool, error:string, filename:string, mime:string}  filename = nom final relatif
 */
function upload_handle($file, string $kind): array
{
    if ($file === null || !is_array($file)) {
        return ['ok' => false, 'error' => 'missing', 'filename' => '', 'mime' => ''];
    }
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'upload_error', 'filename' => '', 'mime' => ''];
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    $size = (int) ($file['size'] ?? 0);
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'upload_error', 'filename' => '', 'mime' => ''];
    }

    // Limite par type : les médias vidéo/audio ont leur propre plafond.
    $kindMax = [
        'image' => UPLOAD_MAX_BYTES,
        'cv'    => UPLOAD_MAX_BYTES,
        'video' => UPLOAD_MAX_VIDEO_BYTES,
        'audio' => UPLOAD_MAX_AUDIO_BYTES,
        'vtt'   => UPLOAD_MAX_VTT_BYTES,
    ][$kind] ?? UPLOAD_MAX_BYTES;
    if ($size <= 0 || $size > $kindMax) {
        return ['ok' => false, 'error' => 'too_large', 'filename' => '', 'mime' => ''];
    }

    // Type MIME réel (finfo), jamais l'extension déclarée par le navigateur.
    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $mime = (string) finfo_file($fi, $tmp);
            finfo_close($fi);
        }
    }
    if ($mime === '' && function_exists('mime_content_type')) {
        $mime = (string) mime_content_type($tmp);
    }
    // ffmpeg-WebM / MDN : certains navigateurs déclarent audio/x-m4a en audio/mp4
    // ou audio/ogg en application/ogg — on normalise sur la table ci-dessus.
    if ($mime === 'application/ogg') {
        $mime = 'audio/ogg';
    }

    $ext = '';
    if ($kind === 'image') {
        if (!isset(UPLOAD_IMAGE_TYPES[$mime])) {
            return ['ok' => false, 'error' => 'bad_type', 'filename' => '', 'mime' => $mime];
        }
        $ext = UPLOAD_IMAGE_TYPES[$mime];
    } elseif ($kind === 'cv') {
        if ($mime !== UPLOAD_PDF_TYPE) {
            return ['ok' => false, 'error' => 'bad_type', 'filename' => '', 'mime' => $mime];
        }
        $ext = 'pdf';
    } elseif ($kind === 'video') {
        if (!isset(UPLOAD_VIDEO_TYPES[$mime])) {
            return ['ok' => false, 'error' => 'bad_type', 'filename' => '', 'mime' => $mime];
        }
        $ext = UPLOAD_VIDEO_TYPES[$mime];
    } elseif ($kind === 'audio') {
        if (!isset(UPLOAD_AUDIO_TYPES[$mime])) {
            return ['ok' => false, 'error' => 'bad_type', 'filename' => '', 'mime' => $mime];
        }
        $ext = UPLOAD_AUDIO_TYPES[$mime];
    } elseif ($kind === 'vtt') {
        // finfo détecte parfois text/plain : vérifie aussi le contenu.
        $head = (string) @file_get_contents($tmp, false, null, 0, 64);
        if (!str_starts_with(trim($head), 'WEBVTT')) {
            return ['ok' => false, 'error' => 'bad_type', 'filename' => '', 'mime' => $mime];
        }
        $ext = 'vtt';
    } else {
        return ['ok' => false, 'error' => 'bad_kind', 'filename' => '', 'mime' => $mime];
    }

    $dir = upload_dir($kind);
    // Nom aléatoire : impossible de réinjecter un chemin, collision quasi nulle.
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target = $dir . DIRECTORY_SEPARATOR . $filename;

    if (!@move_uploaded_file($tmp, $target)) {
        log_event('error', 'Upload: move failed', ['kind' => $kind]);
        return ['ok' => false, 'error' => 'move_failed', 'filename' => '', 'mime' => $mime];
    }
    @chmod($target, 0644);

    log_event('info', 'Upload ok', ['kind' => $kind, 'size' => $size, 'mime' => $mime]);
    return ['ok' => true, 'error' => '', 'filename' => $filename, 'mime' => $mime];
}

/** Supprime un fichier uploadé (nom court sans chemin). */
function upload_delete(string $kind, string $filename): void
{
    $name = basename((string) preg_replace('/[^A-Za-z0-9._-]/', '', $filename));
    if ($name === '' || $name === '.' || $name === '..') {
        return;
    }
    $path = upload_dir($kind) . DIRECTORY_SEPARATOR . $name;
    if (is_file($path)) {
        @unlink($path);
    }
}
