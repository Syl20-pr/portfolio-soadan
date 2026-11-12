<?php
/**
 * =============================================================================
 *  Portfolio SKS — Générateur de CV PDF (FPDF) : logo, photo, format institutionnel
 * =============================================================================
 *  Moteur : FPDF 1.86 (libre — http://www.fpdf.org), inclus dans includes/fpdf.
 *
 *  Encodage : les polices standard de FPDF attendent du CP1252. Tout texte UTF-8
 *             (accents, œ, —, ’, «», etc.) est converti automatiquement dans
 *             Cell(), MultiCell() et GetStringWidth() (voir CV_PDF::toLatin()).
 *
 *  Trois modèles, tous avec le LOGO en tête de page :
 *    - diplomatic : logo centré, double filet doré, photo à gauche, chronologie
 *                   en colonnes (dates à gauche) — format institutionnel (défaut)
 *    - classic    : logo à gauche, photo à droite
 *    - modern     : bande latérale navy (logo blanc), photo en haut de bande
 *
 *  Identité visuelle : navy #0A243B, vert #0F6B3C, or #B8942F.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fpdf' . DIRECTORY_SEPARATOR . 'fpdf.php';

const CV_NAVY = [10, 36, 59];
const CV_GREEN = [15, 107, 60];
const CV_GOLD = [184, 148, 47];
const CV_GREY = [90, 102, 117];
const CV_TEXT = [52, 62, 76];

class CV_PDF extends FPDF
{
    /** Marge gauche et largeur du contenu courantes. */
    public float $contentX = 18;
    public float $contentW = 174;
    /** Modèle (classic|modern|diplomatic) et famille de police principale. */
    public string $tpl = 'classic';
    public string $fontFam = 'helvetica';
    /** Texte du pied de page / de l'en-tête des pages suivantes. */
    public string $footerName = '';
    /** Profondeur d'appel (évite la double conversion MultiCell → Cell). */
    private int $convDepth = 0;

    // ------------------------------------------------------------------
    //  Encodage UTF-8 → CP1252
    // ------------------------------------------------------------------

    /** Convertit une chaîne UTF-8 en CP1252 pour les polices standard FPDF. */
    public static function toLatin(string $s): string
    {
        if ($s === '' || !preg_match('/[^\x00-\x7F]/', $s)) {
            return $s;
        }
        // Déjà en CP1252 (UTF-8 invalide) : on ne touche à rien.
        if (function_exists('mb_check_encoding') && !mb_check_encoding($s, 'UTF-8')) {
            return $s;
        }
        // Espaces et tirets absents de CP1252.
        $s = strtr($s, [
            "\u{202F}" => ' ', "\u{2009}" => ' ', "\u{200A}" => ' ', "\u{2002}" => ' ', "\u{2003}" => ' ',
            "\u{200B}" => '', "\u{FEFF}" => '', "\u{2011}" => '-', "\u{2212}" => '-',
        ]);
        if (function_exists('iconv')) {
            $r = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);
            if ($r !== false && $r !== '') {
                return $r;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            return (string) mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
        }
        return $s;
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        if ($this->convDepth === 0) {
            $txt = self::toLatin((string) $txt);
        }
        $this->convDepth++;
        try {
            parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
        } finally {
            $this->convDepth--;
        }
    }

    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        if ($this->convDepth === 0) {
            $txt = self::toLatin((string) $txt);
        }
        $this->convDepth++;
        try {
            parent::MultiCell($w, $h, $txt, $border, $align, $fill);
        } finally {
            $this->convDepth--;
        }
    }

    public function GetStringWidth($s)
    {
        if ($this->convDepth === 0) {
            $s = self::toLatin((string) $s);
        }
        return parent::GetStringWidth($s);
    }

    // ------------------------------------------------------------------
    //  En-tête / pied de page des pages
    // ------------------------------------------------------------------

    public function Header()
    {
        if ($this->tpl === 'modern') {
            $this->SetFillColor(...CV_NAVY);
            $this->Rect(0, 0, 62, 297, 'F');
        }
        if ($this->PageNo() > 1) {
            $this->SetXY($this->contentX, 10);
            $this->SetFont($this->fontFam, 'B', $this->fs(8.5));
            $this->SetTextColor(...CV_NAVY);
            $this->Cell($this->contentW, 4, $this->footerName, 0, 2);
            $y = $this->GetY() + 0.6;
            $this->SetDrawColor(...CV_GOLD);
            $this->SetLineWidth(0.5);
            $this->Line($this->contentX, $y, $this->contentX + $this->contentW, $y);
            $this->SetY($y + 6);
        }
    }

    public function Footer()
    {
        $y = 285;
        $this->SetDrawColor(205, 210, 217);
        $this->SetLineWidth(0.2);
        $this->Line($this->contentX, $y - 1.5, $this->contentX + $this->contentW, $y - 1.5);
        $this->SetXY($this->contentX, $y);
        $this->SetFont('helvetica', '', 7.5);
        $this->SetTextColor(...CV_GREY);
        $this->Cell($this->contentW / 2, 4, $this->footerName . ' — Curriculum Vitae', 0, 0, 'L');
        $this->Cell($this->contentW / 2, 4, 'Page ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
    }

    /** Taille de police ajustée (Times paraît plus petit que Helvetica). */
    public function fs(float $size): float
    {
        return $this->fontFam === 'times' ? $size + 0.8 : $size;
    }

    // ------------------------------------------------------------------
    //  Blocs de contenu
    // ------------------------------------------------------------------

    /** Passe à la page suivante s'il ne reste pas $h mm. */
    public function ensureSpace(float $h): void
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public static function upper(string $s): string
    {
        return function_exists('mb_strtoupper') ? mb_strtoupper($s, 'UTF-8') : strtoupper($s);
    }

    /** Nombre de lignes estimé pour un texte dans une largeur donnée. */
    private function estLines(string $text, float $width, string $style, float $size): int
    {
        if ($text === '') {
            return 0;
        }
        $this->SetFont($this->fontFam, $style, $size);
        $lines = 0;
        foreach (preg_split('/\R/u', $text) ?: [''] as $para) {
            $lines += max(1, (int) ceil($this->GetStringWidth($para) / max(10.0, $width - 2)));
        }
        return $lines;
    }

    /** Titre de rubrique : MAJUSCULES navy, filet fin + segment doré. */
    public function sectionTitle(string $title): void
    {
        $this->ensureSpace(26);
        $this->SetX($this->contentX);
        $this->SetFont($this->fontFam, 'B', $this->fs($this->tpl === 'diplomatic' ? 11 : 10.5));
        $this->SetTextColor(...CV_NAVY);
        $this->Cell($this->contentW, 6, self::upper($title), 0, 2);
        $y = $this->GetY() + 0.4;
        $this->SetDrawColor(...CV_NAVY);
        $this->SetLineWidth(0.25);
        $this->Line($this->contentX, $y, $this->contentX + $this->contentW, $y);
        $this->SetDrawColor(...CV_GOLD);
        $this->SetLineWidth(0.9);
        $this->Line($this->contentX, $y, $this->contentX + 22, $y);
        $this->SetLineWidth(0.2);
        $this->SetY($y + 3.5);
    }

    public function sectionEnd(): void
    {
        $this->Ln(3.5);
    }

    /** Paragraphe justifié (profil). */
    public function paragraph(string $text): void
    {
        $this->SetFont($this->fontFam, '', $this->fs(9.6));
        $this->SetTextColor(...CV_TEXT);
        $this->SetX($this->contentX);
        $this->MultiCell($this->contentW, 4.9, $text, 0, 'J');
    }

    /** Entrée chronologique : période à gauche, intitulé / structure / description à droite. */
    public function entry(string $period, string $title, string $sub = '', string $desc = ''): void
    {
        $dW = $this->tpl === 'modern' ? 27.0 : 32.0;
        $gap = 3.0;
        $tx = $this->contentX + $dW + $gap;
        $tw = $this->contentW - $dW - $gap;

        $h = 5 * max(1, $this->estLines($title, $tw, 'B', $this->fs(10)))
            + 4.5 * $this->estLines($sub, $tw, 'I', $this->fs(9.2))
            + 4.3 * min(6, $this->estLines($desc, $tw, '', $this->fs(9)));
        $this->ensureSpace(min($h, 38));

        $y0 = $this->GetY();
        $yEndLeft = $y0;
        if ($period !== '') {
            $this->SetXY($this->contentX, $y0 + 0.4);
            $this->SetFont($this->fontFam, 'B', $this->fs(8.6));
            $this->SetTextColor(...CV_GREEN);
            $this->MultiCell($dW, 4.4, $period, 0, 'L');
            $yEndLeft = $this->GetY();
        }
        $this->SetXY($tx, $y0);
        $this->SetFont($this->fontFam, 'B', $this->fs(10));
        $this->SetTextColor(...CV_NAVY);
        $this->MultiCell($tw, 5, $title, 0, 'L');
        if ($sub !== '') {
            $this->SetX($tx);
            $this->SetFont($this->fontFam, 'I', $this->fs(9.2));
            $this->SetTextColor(...CV_GREY);
            $this->MultiCell($tw, 4.5, $sub, 0, 'L');
        }
        if ($desc !== '') {
            $this->Ln(0.6);
            $this->SetX($tx);
            $this->SetFont($this->fontFam, '', $this->fs(9));
            $this->SetTextColor(...CV_TEXT);
            $this->MultiCell($tw, 4.3, $desc, 0, strpos($desc, '•') !== false ? 'L' : 'J');
        }
        $this->SetY(max($this->GetY(), $yEndLeft) + 2.6);
    }

    /** Compétences en grille (2 ou 3 colonnes) avec puce dorée. */
    public function skillGrid(array $names): void
    {
        $cols = $this->contentW > 150 ? 3 : 2;
        $cw = $this->contentW / $cols;
        $this->SetFont($this->fontFam, '', $this->fs(9.4));
        $this->SetTextColor(...CV_TEXT);
        $this->SetFillColor(...CV_GOLD);
        $y = $this->GetY();
        $i = 0;
        foreach ($names as $n) {
            $col = $i % $cols;
            if ($col === 0) {
                $this->ensureSpace(6);
                $y = $this->GetY();
            }
            $x = $this->contentX + $col * $cw;
            $max = $cw - 5;
            while ($n !== '' && $this->GetStringWidth($n) > $max) {
                $n = rtrim(function_exists('mb_substr') ? mb_substr($n, 0, -2, 'UTF-8') : substr($n, 0, -2)) . '…';
                if (function_exists('mb_strlen') && mb_strlen($n, 'UTF-8') < 4) {
                    break;
                }
            }
            $this->Rect($x, $y + 1.9, 1.3, 1.3, 'F');
            $this->SetXY($x + 3.2, $y);
            $this->Cell($cw - 4, 5, $n, 0, 0, 'L');
            $i++;
            if ($col === $cols - 1) {
                $this->SetY($y + 5.4);
            }
        }
        if ($i % $cols !== 0) {
            $this->SetY($y + 5.4);
        }
    }

    /** Ligne « clé à gauche / valeur à droite » (langues). */
    public function keyValue(string $key, string $value): void
    {
        $dW = $this->tpl === 'modern' ? 27.0 : 32.0;
        $tx = $this->contentX + $dW + 3.0;
        $this->ensureSpace(7);
        $y = $this->GetY();
        $this->SetXY($this->contentX, $y);
        $this->SetFont($this->fontFam, 'B', $this->fs(9.6));
        $this->SetTextColor(...CV_NAVY);
        $this->Cell($dW, 5, $key, 0, 0, 'L');
        $this->SetXY($tx, $y);
        $this->SetFont($this->fontFam, '', $this->fs(9.6));
        $this->SetTextColor(...CV_TEXT);
        $this->Cell($this->contentW - $dW - 3.0, 5, $value, 0, 2, 'L');
        $this->SetY($y + 5.6);
    }
}

// =============================================================================
//  Utilitaires
// =============================================================================

/** Chemin d'un logo (logo-horizontal | logo-white | logo-symbol…) ou null. */
function cv_pdf_logo(string $variant): ?string
{
    $f = ROOT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images'
        . DIRECTORY_SEPARATOR . $variant . '.png';
    return is_file($f) ? $f : null;
}

/**
 * Prépare la photo : recadrage 3:4 selon cv_photo, sortie JPEG temporaire.
 * Retourne le chemin du fichier temporaire ou null.
 */
function cv_pdf_prepare_photo(?array $photo): ?string
{
    // Interrupteur admin (migration 006) : photo masquée → rien sur le PDF.
    if ($photo === null || empty($photo['filename'])) {
        return null;
    }
    if (array_key_exists('show_on_pdf', $photo) && !(int) $photo['show_on_pdf']) {
        return null;
    }
    if (!function_exists('imagecreatetruecolor')) {
        // Bibliothèque GD absente : recadrage impossible. Le PDF est généré
        // sans photo ; l'admin est averti (voir admin/cv-builder.php).
        log_event('warning', 'CV photo: bibliothèque GD manquante, photo omise du PDF');
        return null;
    }
    $src = ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $photo['filename'];
    if (!is_file($src)) {
        return null;
    }

    // Type MIME réel — jamais l'extension.
    $mime = '';
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string) finfo_file($fi, $src);
        finfo_close($fi);
    }

    $img = null;
    try {
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $img = @imagecreatefromjpeg($src);
        } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            $img = @imagecreatefrompng($src);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $img = @imagecreatefromwebp($src);
        }
    } catch (Throwable $e) {
        $img = null;
    }
    if (!$img) {
        return null;
    }

    // Recadrage 3:4 piloté par les pourcentages enregistrés.
    $sx = imagesx($img);
    $sy = imagesy($img);
    $zoom = min(300, max(50, (float) ($photo['crop_zoom'] ?? 100))) / 100;
    $ratio = 3 / 4;
    $cropW = min($sx, $sy * $ratio) / $zoom;
    $cropH = $cropW / $ratio;
    $cx = ($sx - $cropW) * (min(100, max(0, (float) ($photo['crop_x'] ?? 50))) / 100);
    $cy = ($sy - $cropH) * (min(100, max(0, (float) ($photo['crop_y'] ?? 50))) / 100);

    $outW = 450;
    $outH = 600;
    $dst = imagecreatetruecolor($outW, $outH);
    imagecopyresampled($dst, $img, 0, 0, (int) $cx, (int) $cy, $outW, $outH, (int) max(1, $cropW), (int) max(1, $cropH));
    $tmp = tempnam(sys_get_temp_dir(), 'cvphoto');
    $jpg = $tmp . '.jpg';
    rename($tmp, $jpg);
    imagejpeg($dst, $jpg, 88);
    imagedestroy($img);
    imagedestroy($dst);
    return $jpg;
}

/** Valeur FR/EN d'un champ bilingue (repli sur l'autre langue si vide). */
function cv_pdf_pick(array $it, string $base, string $lang): string
{
    $fr = trim((string) ($it[$base . '_fr'] ?? ''));
    $en = trim((string) ($it[$base . '_en'] ?? ''));
    return $lang === 'en' ? ($en !== '' ? $en : $fr) : ($fr !== '' ? $fr : $en);
}

/** Puces « - » / « * » → « • » pour une meilleure lisibilité. */
function cv_pdf_bullets(string $s): string
{
    return trim((string) preg_replace('/^[ \t]*[-*•][ \t]+/mu', '•  ', $s));
}

/** « mm/aaaa » depuis une date SQL ('' si invalide). */
function cv_pdf_month(?string $d): string
{
    if ($d === null || $d === '') {
        return '';
    }
    $t = strtotime($d);
    return $t ? date('m/Y', $t) : '';
}

/** Période « début – fin / présent » à partir de dates SQL. */
function cv_pdf_range(array $it, string $present): string
{
    $start = cv_pdf_month($it['date_start'] ?? null);
    if ($start === '') {
        return '';
    }
    $end = !empty($it['is_current']) ? $present : cv_pdf_month($it['date_end'] ?? null);
    return $end !== '' ? $start . ' – ' . $end : $start;
}

/**
 * Génère le PDF du CV.
 *
 * @param array  $cv        données de cv_build() (includes/cvdata.php)
 * @param string $template  classic|modern|diplomatic
 * @param string $output    'S' (chaîne) | 'I' (inline navigateur) | 'D' (téléchargement)
 */
function cv_pdf_render(array $cv, string $template = 'classic', string $output = 'S'): string
{
    if (!in_array($template, ['classic', 'modern', 'diplomatic'], true)) {
        $template = 'diplomatic';
    }
    $lang = (string) $cv['lang'];
    $p = (array) ($cv['personal'] ?? []);

    $labels = $lang === 'en'
        ? ['present' => 'Present', 'now' => 'ongoing']
        : ['present' => 'Présent', 'now' => 'en cours'];

    $name = trim((string) ($p['full_name'] ?? ''));
    $headline = trim((string) ($p['headline'] ?? ''));
    $f = static fn ($k) => trim((string) ($p[$k] ?? ''));
    // Contacts regroupés par ligne : (e-mail, téléphone) / (lieu, nationalité) / (site, LinkedIn).
    $contactLines = array_values(array_filter([
        implode('   ·   ', array_filter([$f('email'), $f('phone')], static fn ($v) => $v !== '')),
        implode('   ·   ', array_filter([$f('location'), $f('nationality')], static fn ($v) => $v !== '')),
        implode('   ·   ', array_filter([$f('website'), $f('linkedin')], static fn ($v) => $v !== '')),
    ], static fn ($v) => $v !== ''));
    $contactFlat = array_values(array_filter([
        $f('email'), $f('phone'), $f('location'), $f('website'), $f('linkedin'), $f('nationality'),
    ], static fn ($v) => $v !== ''));

    $pdf = new CV_PDF('P', 'mm', 'A4');
    $pdf->tpl = $template;
    $pdf->fontFam = $template === 'diplomatic' ? 'times' : 'helvetica';
    $pdf->footerName = $name !== '' ? $name : 'CV';
    $pdf->contentX = $template === 'modern' ? 72 : 18;
    $pdf->contentW = $template === 'modern' ? 120 : 174;
    $pdf->SetMargins(18, 18, 18);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->AliasNbPages();
    $pdf->SetTitle(($name !== '' ? $name . ' — ' : '') . 'Curriculum Vitae', true);
    $pdf->SetAuthor($name, true);
    $pdf->SetCreator('Portfolio SKS');
    $pdf->AddPage();

    $photoTmp = cv_pdf_prepare_photo($cv['photo'] ?? null);
    $fam = $pdf->fontFam;

    // ------------------------------------------------------------------
    //  EN-TÊTE — le logo est toujours en haut de la première page
    // ------------------------------------------------------------------
    if ($template === 'modern') {
        // Bande latérale navy : logo blanc, photo, identité et contacts.
        $logo = cv_pdf_logo('logo-white');
        if ($logo !== null) {
            $pdf->Image($logo, 8, 11, 46);
        }
        $yPhoto = 26;
        if ($photoTmp !== null) {
            $pdf->Image($photoTmp, 11, $yPhoto, 40, 53.3, 'JPEG');
            $yName = $yPhoto + 53.3 + 7;
        } else {
            $yName = 30;
        }
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 15);
        $pdf->SetXY(8, $yName);
        $pdf->MultiCell(46, 6.3, $name, 0, 'L');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(214, 220, 227);
        $pdf->SetXY(8, $pdf->GetY() + 1.5);
        $pdf->MultiCell(46, 4.4, $headline, 0, 'L');
        $y = $pdf->GetY() + 5;
        $pdf->SetFont('helvetica', '', 8);
        foreach ($contactFlat as $c) {
            $pdf->SetXY(8, $y);
            $pdf->MultiCell(46, 4, $c, 0, 'L');
            $y = $pdf->GetY() + 1.4;
        }

        $pdf->SetY(16);
        $pdf->SetX(72);
        $pdf->SetTextColor(...CV_NAVY);
        $pdf->SetFont('helvetica', 'B', 17);
        $pdf->Cell(120, 8, $name, 0, 2);
        $pdf->SetFont('helvetica', '', 10.5);
        $pdf->SetTextColor(...CV_GREEN);
        $pdf->Cell(120, 5.5, $headline, 0, 2);
        $pdf->Ln(2);
        $pdf->SetDrawColor(...CV_GOLD);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(72, $pdf->GetY(), 192, $pdf->GetY());
        $pdf->Ln(6);
    } elseif ($template === 'diplomatic') {
        // Logo centré, double filet, puis photo à gauche et identité à droite.
        $logo = cv_pdf_logo('logo-horizontal');
        $yRule = 15;
        if ($logo !== null) {
            $pdf->Image($logo, (210 - 62) / 2, 11, 62);
            $yRule = 11 + 62 * 154 / 751 + 3.5;
        }
        $pdf->SetDrawColor(...CV_GOLD);
        $pdf->SetLineWidth(0.9);
        $pdf->Line(18, $yRule, 192, $yRule);
        $pdf->SetDrawColor(...CV_NAVY);
        $pdf->SetLineWidth(0.25);
        $pdf->Line(18, $yRule + 1.5, 192, $yRule + 1.5);

        $yTop = $yRule + 7;
        $hasPhoto = $photoTmp !== null;
        if ($hasPhoto) {
            $pdf->Image($photoTmp, 18, $yTop, 30, 40, 'JPEG');
        }
        $tx = $hasPhoto ? 54.0 : 18.0;
        $tw = $hasPhoto ? 138.0 : 174.0;
        $al = $hasPhoto ? 'L' : 'C';

        $pdf->SetXY($tx, $yTop + 1);
        $pdf->SetTextColor(...CV_NAVY);
        $pdf->SetFont('times', 'B', 21);
        $pdf->Cell($tw, 9, CV_PDF::upper($name), 0, 2, $al);
        $pdf->SetFont('times', 'I', 12.5);
        $pdf->SetTextColor(...CV_GREEN);
        $pdf->Cell($tw, 6.5, $headline, 0, 2, $al);
        $pdf->Ln(2.5);
        $pdf->SetFont('helvetica', '', 8.8);
        $pdf->SetTextColor(...CV_GREY);
        foreach ($contactLines as $line) {
            $pdf->SetX($tx);
            $pdf->Cell($tw, 4.4, $line, 0, 2, $al);
        }
        $yEnd = max($pdf->GetY(), $hasPhoto ? $yTop + 40 : 0) + 4;
        $pdf->SetDrawColor(205, 210, 217);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(18, $yEnd, 192, $yEnd);
        $pdf->SetY($yEnd + 6);
    } else {
        // classic : logo en haut à gauche, photo à droite.
        $logo = cv_pdf_logo('logo-horizontal');
        $yName = 18;
        if ($logo !== null) {
            $pdf->Image($logo, 18, 11, 50);
            $yName = 11 + 50 * 154 / 751 + 6;
        }
        $pdf->SetTextColor(...CV_NAVY);
        $pdf->SetFont('helvetica', 'B', 19);
        $pdf->SetXY(18, $yName);
        $pdf->Cell(126, 8, $name, 0, 2);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(...CV_GREEN);
        $pdf->Cell(126, 6, $headline, 0, 2);
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', '', 8.8);
        $pdf->SetTextColor(...CV_GREY);
        foreach ($contactLines as $line) {
            $pdf->SetX(18);
            $pdf->Cell(126, 4.4, $line, 0, 2);
        }
        if ($photoTmp !== null) {
            $pdf->Image($photoTmp, 156, 12, 34, 45.3, 'JPEG');
        }
        $pdf->SetY(max($pdf->GetY() + 3, $photoTmp !== null ? 61 : 0));
        $pdf->SetDrawColor(...CV_NAVY);
        $pdf->SetLineWidth(0.6);
        $pdf->Line(18, $pdf->GetY(), 192, $pdf->GetY());
        $pdf->SetLineWidth(0.2);
        $pdf->Ln(6);
    }

    // ------------------------------------------------------------------
    //  RUBRIQUES
    // ------------------------------------------------------------------
    foreach ($cv['sections'] as $section) {
        $key = (string) $section['key'];
        $items = (array) ($section['data']['items'] ?? []);

        if ($key === 'summary') {
            $text = trim((string) ($section['data']['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $pdf->sectionTitle((string) $section['title']);
            $pdf->paragraph($text);
            $pdf->sectionEnd();
            continue;
        }
        if ($items === []) {
            continue;
        }

        $pdf->sectionTitle((string) $section['title']);

        if ($key === 'skills') {
            $names = array_values(array_filter(
                array_map(static fn ($r) => trim((string) ($r['name'] ?? '')), $items),
                static fn ($v) => $v !== ''
            ));
            $pdf->skillGrid($names);
            $pdf->sectionEnd();
            continue;
        }

        foreach ($items as $it) {
            switch ($key) {
                case 'experience':
                    $pdf->entry(
                        cv_pdf_range($it, $labels['present']),
                        cv_pdf_pick($it, 'role', $lang) !== '' ? cv_pdf_pick($it, 'role', $lang) : cv_pdf_pick($it, 'org', $lang),
                        cv_pdf_pick($it, 'role', $lang) !== '' ? cv_pdf_pick($it, 'org', $lang) : '',
                        cv_pdf_bullets(cv_pdf_pick($it, 'desc', $lang))
                    );
                    break;
                case 'education':
                    $period = (string) ($it['started'] ?? '');
                    $end = !empty($it['is_current']) ? $labels['now'] : (string) ($it['ended'] ?? '');
                    if ($period !== '' && $end !== '' && $end !== $period) {
                        $period .= ' – ' . $end;
                    } elseif ($period === '') {
                        $period = $end;
                    }
                    $pdf->entry(
                        $period,
                        cv_pdf_pick($it, 'degree', $lang),
                        cv_pdf_pick($it, 'school', $lang),
                        cv_pdf_bullets(cv_pdf_pick($it, 'desc', $lang))
                    );
                    break;
                case 'languages':
                    $level = $lang === 'en'
                        ? ((string) ($it['level_en'] ?? '') !== '' ? (string) $it['level_en'] : (string) ($it['level_fr'] ?? ''))
                        : ((string) ($it['level_fr'] ?? '') !== '' ? (string) $it['level_fr'] : (string) ($it['level_en'] ?? ''));
                    $pdf->keyValue((string) ($it['name'] ?? ''), $level);
                    break;
                case 'certifications':
                    $pdf->entry(
                        (string) ($it['year'] ?? ''),
                        cv_pdf_pick($it, 'name', $lang),
                        trim((string) ($it['issuer'] ?? ''))
                    );
                    break;
                case 'associations':
                    $role = cv_pdf_pick($it, 'role', $lang);
                    $pdf->entry(
                        cv_pdf_range($it, $labels['present']),
                        $role !== '' ? $role : cv_pdf_pick($it, 'org', $lang),
                        $role !== '' ? cv_pdf_pick($it, 'org', $lang) : '',
                        cv_pdf_bullets(cv_pdf_pick($it, 'desc', $lang))
                    );
                    break;
                case 'distinctions':
                    $pdf->entry(
                        (string) ($it['year'] ?? ''),
                        cv_pdf_pick($it, 'title', $lang),
                        trim((string) ($it['issuer'] ?? '')),
                        cv_pdf_bullets(cv_pdf_pick($it, 'desc', $lang))
                    );
                    break;
                case 'publications':
                    $pdf->entry(
                        (string) ($it['year'] ?? ''),
                        cv_pdf_pick($it, 'title', $lang),
                        cv_pdf_pick($it, 'venue', $lang)
                    );
                    break;
            }
        }
        $pdf->sectionEnd();
    }

    if ($photoTmp !== null) {
        @unlink($photoTmp);
    }

    return $pdf->Output($output, 'CV.pdf');
}
