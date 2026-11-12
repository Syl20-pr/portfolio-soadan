<?php
/**
 * =============================================================================
 *  Portfolio SKS — Administration : CV dynamique (builder)
 * =============================================================================
 *  Module complet : photo professionnelle (import, prévisualisation, recadrage,
 *  remplacement, suppression), informations personnelles, CRUD bilingue des
 *  rubriques, réorganisation, modèle, brouillon/publier, génération PDF.
 *
 *  Sécurité : require_admin(), CSRF sur chaque action, uploads contrôlés
 *  (MIME réel via finfo, taille, noms aléatoires), aucune fonction d'édition
 *  exposée côté public.
 * =============================================================================
 */

declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'auth.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'content.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'cvdata.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upload.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'cvpdf.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '_layout.php';

require_admin();

/**
 * Génère et publie le CV d'une langue (remplace l'ancien PDF publié).
 * Deux CV indépendants : si un champ EN est vide, il le reste — jamais de
 * texte français dans le PDF anglais. La publication EN échoue avec un
 * message clair tant que la version anglaise est incomplète.
 *
 * @return array{ok:bool,msg:string}
 */
function cv_publish_lang(PDO $pdo, string $lang): array
{
    $cv = cv_build($lang);
    if (trim((string) ($cv['personal']['full_name'] ?? '')) === '') {
        return ['ok' => false, 'msg' => 'Renseignez le nom complet avant de publier le CV ' . strtoupper($lang) . '.'];
    }
    if ($lang === 'en') {
        // Garde-fou : la version anglaise doit être rédigée en anglais.
        $missing = cv_en_missing_fields($cv);
        if ($missing !== []) {
            return ['ok' => false, 'msg' => 'CV EN non publié — champs anglais manquants : '
                . implode(', ', $missing) . '. Complétez-les côté EN (jamais remplis automatiquement).'];
        }
    }
    $dir = ROOT_PATH . '/uploads/cv';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $file = 'cv_' . $lang . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $pdf = cv_pdf_render($cv, (string) $cv['template'], 'S');
    file_put_contents($dir . '/' . $file, $pdf);
    // Retire l'ancien PDF publié (le lien public est stable : cvgenerate.php).
    $old = cv_state_get($lang);
    if (!empty($old['published_file'])) {
        @unlink($dir . '/' . basename((string) $old['published_file']));
    }
    cv_state_publish($lang, $file);
    // Historique (réutilise cv_documents de la migration 001).
    $version = (int) $pdo->query('SELECT COALESCE(MAX(version),0)+1 FROM cv_documents WHERE lang = ' . $pdo->quote($lang))->fetchColumn();
    $pdo->prepare('UPDATE cv_documents SET is_active = 0 WHERE lang = :l')->execute([':l' => $lang]);
    $pdo->prepare('INSERT INTO cv_documents (lang, filename, original_name, version, is_active, uploaded_by)
        VALUES (:l, :f, :o, :v, 1, :u)')->execute([
        ':l' => $lang, ':f' => $file, ':o' => 'CV généré automatiquement',
        ':v' => $version, ':u' => ((int) ($_SESSION['admin_id'] ?? 0)) > 0 ? (int) $_SESSION['admin_id'] : null,
    ]);
    return ['ok' => true, 'msg' => 'CV ' . strtoupper($lang) . ' publié'];
}

$flash = '';
$flashType = 'ok';

// Langue de travail (onglets FR / EN indépendants).
$lang = normalize_lang($_REQUEST['lang'] ?? 'fr');
$otherLang = $lang === 'fr' ? 'en' : 'fr';

try {
    $pdo = db();

    // =====================================================================
    //  Actions POST
    // =====================================================================
    if (is_post()) {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $flash = 'Session expirée. Merci de réessayer.';
            $flashType = 'err';
        } else {
            $action = (string) ($_POST['action'] ?? '');
            $clean = static function ($v, int $max): string {
                $v = is_scalar($v) ? trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '') : '';
                return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
            };

            switch ($action) {

                // ---------------------------------------------------------
                case 'photo_upload':
                    $up = upload_handle($_FILES['photo'] ?? null, 'image');
                    if (!$up['ok']) {
                        $msg = ['bad_type' => 'Format d\'image non supporté (JPEG, PNG, WebP attendus).',
                                'too_large' => 'Image trop volumineuse (5 Mo maximum).',
                                'upload_error' => 'Échec du téléversement.',
                                'move_failed' => 'Échec d\'enregistrement sur le serveur.'][$up['error']] ?? 'Échec du téléversement.';
                        $flash = $msg;
                        $flashType = 'err';
                    } else {
                        cv_photo_set($up['filename'], (string) ($_FILES['photo']['name'] ?? ''));
                        $flash = 'Photo téléversée. Ajustez le recadrage puis enregistrez.';
                    }
                    break;

                case 'photo_crop':
                    $photo = cv_photo_get();
                    if ($photo === null) {
                        $flash = 'Aucune photo à recadrer.';
                        $flashType = 'err';
                    } else {
                        cv_photo_set_crop(
                            (float) ($_POST['crop_x'] ?? 50),
                            (float) ($_POST['crop_y'] ?? 50),
                            (float) ($_POST['crop_zoom'] ?? 100)
                        );
                        $flash = 'Recadrage enregistré.';
                    }
                    break;

                case 'photo_delete':
                    cv_photo_delete();
                    $flash = 'Photo supprimée.';
                    break;

                // ---------------------------------------------------------
                case 'personal_save':
                    $personal = [
                        'full_name' => $clean($_POST['full_name'] ?? '', 160),
                        'headline' => $clean($_POST['headline'] ?? '', 200),
                        'summary' => $clean($_POST['summary'] ?? '', 2000),
                        'email' => $clean($_POST['email'] ?? '', 254),
                        'phone' => $clean($_POST['phone'] ?? '', 40),
                        'location' => $clean($_POST['location'] ?? '', 160),
                        'website' => $clean($_POST['website'] ?? '', 300),
                        'linkedin' => $clean($_POST['linkedin'] ?? '', 300),
                        'nationality' => $clean($_POST['nationality'] ?? '', 80),
                    ];
                    if ($personal['email'] !== '' && filter_var($personal['email'], FILTER_VALIDATE_EMAIL) === false) {
                        $flash = 'Adresse e-mail invalide.';
                        $flashType = 'err';
                    } else {
                        if ($personal['website'] !== '' && filter_var($personal['website'], FILTER_VALIDATE_URL) === false) {
                            $personal['website'] = '';
                        }
                        if ($personal['linkedin'] !== '' && filter_var($personal['linkedin'], FILTER_VALIDATE_URL) === false) {
                            $personal['linkedin'] = '';
                        }
                        cv_personal_save($lang, $personal);
                        $flash = 'Informations personnelles enregistrées (' . strtoupper($lang) . ').';
                    }
                    break;

                // ---------------------------------------------------------
                case 'sections_save':
                    $order = array_values(array_filter(array_map('strval', (array) ($_POST['order'] ?? [])), static fn ($k) => preg_match('/^[a-z_]{3,30}$/', $k)));
                    $titles = array_map(static fn ($t) => $clean($t, 120), (array) ($_POST['titles'] ?? []));
                    $invisible = array_values(array_filter((array) ($_POST['invisible'] ?? []), static fn ($k) => preg_match('/^[a-z_]{3,30}$/', $k)));
                    cv_sections_save($lang, $order, $titles, $invisible);
                    $flash = 'Ordre et visibilité des rubriques enregistrés (' . strtoupper($lang) . ').';
                    break;

                // ---------------------------------------------------------
                case 'spoken_save':
                    $id = (int) ($_POST['id'] ?? 0);
                    cv_spoken_language_save([
                        'name' => $clean($_POST['name'] ?? '', 60),
                        'level_fr' => $clean($_POST['level_fr'] ?? '', 60),
                        'level_en' => $clean($_POST['level_en'] ?? '', 60),
                    ], $id);
                    $flash = $id > 0 ? 'Langue mise à jour.' : 'Langue ajoutée.';
                    break;

                case 'spoken_delete':
                    $pdo->prepare('DELETE FROM cv_languages WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
                    $flash = 'Langue supprimée.';
                    break;

                // ---------------------------------------------------------
                case 'assoc_save':
                    $id = (int) ($_POST['id'] ?? 0);
                    cv_association_save([
                        'org_fr' => $clean($_POST['org_fr'] ?? '', 200),
                        'org_en' => $clean($_POST['org_en'] ?? '', 200),
                        'role_fr' => $clean($_POST['role_fr'] ?? '', 200),
                        'role_en' => $clean($_POST['role_en'] ?? '', 200),
                        'desc_fr' => $clean($_POST['desc_fr'] ?? '', 2000),
                        'desc_en' => $clean($_POST['desc_en'] ?? '', 2000),
                        'date_start' => trim((string) ($_POST['date_start'] ?? '')),
                        'date_end' => trim((string) ($_POST['date_end'] ?? '')),
                        'is_current' => isset($_POST['is_current']),
                        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                    ], $id);
                    $flash = $id > 0 ? 'Engagement mis à jour.' : 'Engagement ajouté.';
                    break;

                case 'assoc_delete':
                    $pdo->prepare('DELETE FROM cv_associations WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
                    $flash = 'Engagement supprimé.';
                    break;

                // ---------------------------------------------------------
                case 'pub_save':
                    $id = (int) ($_POST['id'] ?? 0);
                    $url = $clean($_POST['url'] ?? '', 400);
                    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
                        $flash = 'URL de publication invalide.';
                        $flashType = 'err';
                    } else {
                        cv_publication_save([
                            'title_fr' => $clean($_POST['title_fr'] ?? '', 255),
                            'title_en' => $clean($_POST['title_en'] ?? '', 255),
                            'venue_fr' => $clean($_POST['venue_fr'] ?? '', 200),
                            'venue_en' => $clean($_POST['venue_en'] ?? '', 200),
                            'year' => (int) ($_POST['year'] ?? 0),
                            'url' => $url,
                            'article_id' => (int) ($_POST['article_id'] ?? 0),
                            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                        ], $id);
                        $flash = $id > 0 ? 'Publication mise à jour.' : 'Publication ajoutée.';
                    }
                    break;

                case 'pub_delete':
                    $pdo->prepare('DELETE FROM cv_publications WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
                    $flash = 'Publication retirée du CV.';
                    break;

                case 'manual_en_save':
                    if ($lang !== 'en') {
                        $flash = 'Les traductions manuelles sont réservées à la version anglaise.';
                        $flashType = 'err';
                        break;
                    }
                    $manualSchemas = [
                        'experience' => ['experiences', ['org_en' => 200, 'role_en' => 200, 'desc_en' => 2000]],
                        'education' => ['education', ['school_en' => 200, 'degree_en' => 200, 'desc_en' => 2000]],
                        'skill' => ['skills', ['name_en' => 120]],
                        'certification' => ['certifications', ['name_en' => 200]],
                        'distinction' => ['distinctions', ['title_en' => 200, 'desc_en' => 400]],
                    ];
                    $kind = (string) ($_POST['section'] ?? '');
                    $id = (int) ($_POST['id'] ?? 0);
                    if (!isset($manualSchemas[$kind]) || $id < 1) {
                        $flash = 'Élément de traduction invalide.';
                        $flashType = 'err';
                        break;
                    }
                    [$table, $fields] = $manualSchemas[$kind];
                    $exists = $pdo->prepare('SELECT 1 FROM ' . $table . ' WHERE id = :id');
                    $exists->execute([':id' => $id]);
                    if ($exists->fetchColumn() === false) {
                        $flash = 'Élément introuvable ; rechargez la page.';
                        $flashType = 'err';
                        break;
                    }
                    $assignments = [];
                    $values = [':id' => $id];
                    foreach ($fields as $field => $max) {
                        $assignments[] = $field . ' = :' . $field;
                        $values[':' . $field] = $clean($_POST[$field] ?? '', $max);
                    }
                    $pdo->prepare('UPDATE ' . $table . ' SET ' . implode(', ', $assignments) . ' WHERE id = :id')
                        ->execute($values);
                    $flash = 'Traduction anglaise enregistrée.';
                    break;

                // ---------------------------------------------------------
                case 'template_save':
                    cv_state_set_template($lang, (string) ($_POST['template'] ?? 'classic'));
                    $flash = 'Modèle mis à jour (' . strtoupper($lang) . ').';
                    break;

                // ---------------------------------------------------------
                case 'publish_both':
                    // Publication en une action : FR puis EN (indépendants).
                    $rFr = cv_publish_lang($pdo, 'fr');
                    $rEn = cv_publish_lang($pdo, 'en');
                    if ($rFr['ok'] && $rEn['ok']) {
                        $flash = 'CV publié : les deux versions (FR et EN) remplacent les précédentes.';
                    } elseif ($rFr['ok'] && !$rEn['ok']) {
                        $flash = 'CV FR publié. ' . $rEn['msg'];
                        $flashType = 'err';
                    } else {
                        $flash = $rFr['msg'] . ' ' . $rEn['msg'];
                        $flashType = 'err';
                    }
                    break;

                // ---------------------------------------------------------
                case 'generate_draft':
                    $cv = cv_build($lang);
                    $dir = ROOT_PATH . '/uploads/cv';

                // ---------------------------------------------------------
                case 'generate_draft':
                    $cv = cv_build($lang);
                    $dir = ROOT_PATH . '/uploads/cv';
                    if (!is_dir($dir)) @mkdir($dir, 0775, true);
                    $draftDir = $dir . '/drafts';
                    if (!is_dir($draftDir)) @mkdir($draftDir, 0775, true);
                    $file = 'draft_' . $lang . '_' . bin2hex(random_bytes(6)) . '.pdf';
                    $pdf = cv_pdf_render($cv, (string) $cv['template'], 'S');
                    file_put_contents($draftDir . '/' . $file, $pdf);
                    // Nettoie les anciens brouillons de cette langue.
                    foreach (glob($draftDir . '/draft_' . $lang . '_*.pdf') ?: [] as $old) {
                        if (basename((string) $old) !== $file) @unlink((string) $old);
                    }
                    $pdo->prepare('UPDATE cv_state SET draft_file = :f WHERE lang = :l')
                        ->execute([':f' => 'drafts/' . $file, ':l' => $lang]);
                    $flash = 'Brouillon généré — vérifiez-le avant publication.';
                    break;

                case 'publish':
                    $res = cv_publish_lang($pdo, $lang);
                    if (!$res['ok']) {
                        $flash = $res['msg'];
                        $flashType = 'err';
                        break;
                    }
                    $flash = $res['msg'] . ' : il est désormais téléchargeable depuis le site.';
                    if ($lang === 'fr') {
                        // Le CV anglais suit automatiquement : mêmes données, traduites, même modèle.
                        cv_state_set_template('en', (string) (cv_state_get('fr')['template'] ?? 'diplomatic'));
                        $resEn = cv_publish_lang($pdo, 'en');
                        $flash .= $resEn['ok']
                            ? ' La version anglaise a été traduite et publiée automatiquement.'
                            : ' ' . $resEn['msg'];
                        if (!$resEn['ok']) {
                            $flashType = 'err';
                        }
                    }
                    break;

                case 'unpublish':
                    cv_state_unpublish($lang);
                    $flash = 'Version publiée retirée : le site sert à nouveau l\'ancien CV (PDF d\'origine).';
                    break;
            }
        }
        header('Location: cv-builder.php?lang=' . $lang . '&flash=' . rawurlencode($flash) . '&ft=' . $flashType, true, 303);
        exit;
    }
} catch (Throwable $e) {
    log_event('error', 'Admin cv-builder: failure', ['detail' => $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()]);
    $flash = $flash === '' ? 'Une erreur est survenue.' : $flash;
    $flashType = 'err';
}

// =====================================================================
//  Données d'affichage
// =====================================================================
$cvExperiences = $cvEducation = $cvSkills = $cvCertifications = $cvDistinctions = [];
try {
    $photo = cv_photo_get();
    $personal = cv_personal_get($lang);
    $sections = cv_sections_get($lang);
    $cvExperiences = experiences_all();
    $cvEducation = education_all();
    $cvSkills = skills_all();
    $cvCertifications = certifications_all();
    $cvDistinctions = distinctions_all();
    $spokens = cv_spoken_languages();
    $assocs = cv_associations_all();
    $pubs = cv_publications_all();
    $state = cv_state_get($lang);
    $stateOther = cv_state_get($otherLang);
    $defaultTitles = cv_section_default_titles();
} catch (Throwable $e) {
    log_event('error', 'Admin cv-builder: data unavailable');
}

admin_header('CV dynamique', 'cv');
?>
<?php
// Flash persistant via la redirection (message après opérations POST).
$flashQS = isset($_GET['flash']) ? (string) $_GET['flash'] : '';
if ($flash === '' && $flashQS !== '') {
    $flash = mb_substr($flashQS, 0, 500, 'UTF-8');
    $flashType = ($_GET['ft'] ?? 'ok') === 'err' ? 'err' : 'ok';
}
?>
<?php if ($flash !== ''): ?>
  <div class="adm-alert <?= e($flashType) ?>"><?= e($flash) ?></div>
<?php endif; ?>

<div class="adm-flex" style="justify-content:space-between;margin-bottom:14px">
  <div class="adm-flex" style="gap:8px">
    <a class="adm-btn <?= $lang === 'fr' ? '' : 'secondary' ?>" href="cv-builder.php?lang=fr">Version française</a>
    <a class="adm-btn <?= $lang === 'en' ? '' : 'secondary' ?>" href="cv-builder.php?lang=en">Version anglaise</a>
  </div>
  <div class="adm-flex" style="gap:8px">
    <span class="adm-muted"><?= strtoupper($lang) ?> : <?= $state['status'] === 'published' ? '<strong>publié</strong>' : 'brouillon' ?></span>
    <a class="adm-btn small" href="../cvgenerate.php?lang=<?= e($lang) ?>&mode=preview" target="_blank" rel="noopener">Aperçu PDF ↗</a>
  </div>
</div>

<!-- =========================== PHOTO =========================== -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Photo professionnelle</h2>
  <?php if ($photo === null): ?>
    <form method="POST" enctype="multipart/form-data" class="adm-flex" style="align-items:flex-end">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="photo_upload">
      <div class="adm-field" style="margin-bottom:0">
        <label for="photo">Image (JPEG/PNG/WebP — 5 Mo max)</label>
        <input id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" required>
      </div>
      <button type="submit" class="adm-btn">Téléverser</button>
    </form>
  <?php else: ?>
    <div class="adm-flex" style="align-items:flex-start;gap:24px">
      <div style="text-align:center">
        <img id="cvPhotoPrev" src="../uploads/images/<?= e($photo['filename']) ?>" alt="Photo professionnelle"
             style="width:150px;height:200px;object-fit:none;object-position:<?= e((string) $photo['crop_x']) ?>% <?= e((string) $photo['crop_y']) ?>%;border:1px solid var(--line);border-radius:4px;background:#f6f7f9">
        <p class="adm-help">Aperçu recadré (format 3:4)</p>
      </div>
      <div style="flex:1;min-width:320px">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="photo_crop">
          <div class="adm-field">
            <label>Recadrage horizontal <span class="adm-muted" id="cropXVal"><?= e((string) $photo['crop_x']) ?>%</span></label>
            <input type="range" name="crop_x" id="cropX" min="0" max="100" step="1" value="<?= e((string) $photo['crop_x']) ?>"
                   oninput="document.getElementById('cropXVal').textContent=this.value+'%';updatePhotoCrop()">
          </div>
          <div class="adm-field">
            <label>Recadrage vertical <span class="adm-muted" id="cropYVal"><?= e((string) $photo['crop_y']) ?>%</span></label>
            <input type="range" name="crop_y" id="cropY" min="0" max="100" step="1" value="<?= e((string) $photo['crop_y']) ?>"
                   oninput="document.getElementById('cropYVal').textContent=this.value+'%';updatePhotoCrop()">
          </div>
          <div class="adm-field">
            <label>Zoom <span class="adm-muted" id="cropZVal"><?= e((string) $photo['crop_zoom']) ?>%</span></label>
            <input type="range" name="crop_zoom" id="cropZ" min="50" max="300" step="5" value="<?= e((string) $photo['crop_zoom']) ?>"
                   oninput="document.getElementById('cropZVal').textContent=this.value+'%';updatePhotoCrop()">
          </div>
          <div class="adm-flex">
            <button type="submit" class="adm-btn">Enregistrer le recadrage</button>
          </div>
        </form>
        <form method="POST" enctype="multipart/form-data" class="adm-flex" style="margin-top:14px;align-items:flex-end">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="photo_upload">
          <div class="adm-field" style="margin-bottom:0">
            <label for="photo">Remplacer la photo</label>
            <input id="photo" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
          </div>
          <button type="submit" class="adm-btn secondary">Remplacer</button>
        </form>
        <form method="POST" style="margin-top:12px" onsubmit="return confirm('Supprimer la photo professionnelle ?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="photo_delete">
          <button type="submit" class="adm-btn danger small">Supprimer la photo</button>
        </form>
      </div>
    </div>
    <script>
      function updatePhotoCrop() {
        var img = document.getElementById('cvPhotoPrev');
        if (!img) return;
        img.style.objectPosition =
          document.getElementById('cropX').value + '% ' + document.getElementById('cropY').value + '%';
        img.style.objectFit = document.getElementById('cropZ').value > 100 ? 'none' : 'cover';
        img.style.width = (150 * Math.min(3, document.getElementById('cropZ').value / 100)) + 'px';
        img.style.height = (200 * Math.min(3, document.getElementById('cropZ').value / 100)) + 'px';
        img.style.clipPath = 'inset(0 0 0 0)';
        img.parentElement.style.width = '150px';
        img.parentElement.style.height = '200px';
        img.parentElement.style.overflow = 'hidden';
        img.style.position = 'absolute';
        img.style.left = '0'; img.style.top = '0';
        img.parentElement.style.position = 'relative';
      }
    </script>
  <?php endif; ?>
</div>

<!-- ======================= INFORMATIONS PERSONNELLES ======================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Informations personnelles — <?= strtoupper($lang) ?></h2>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="personal_save">
    <div class="adm-grid2">
      <div class="adm-field"><label>Nom complet (universel)</label>
        <input class="adm-input" name="full_name" maxlength="160" value="<?= e($personal['full_name'] ?? '') ?>"></div>
      <div class="adm-field"><label>Titre professionnel (<?= strtoupper($lang) ?>)</label>
        <input class="adm-input" name="headline" maxlength="200" value="<?= e($personal['headline'] ?? '') ?>"></div>
    </div>
    <div class="adm-field"><label>Profil / résumé (<?= strtoupper($lang) ?>)</label>
      <textarea class="adm-textarea" rows="5" name="summary" maxlength="2000"><?= e($personal['summary'] ?? '') ?></textarea></div>
    <div class="adm-grid2">
      <div class="adm-field"><label>Email</label><input class="adm-input" name="email" maxlength="254" value="<?= e($personal['email'] ?? '') ?>"></div>
      <div class="adm-field"><label>Téléphone</label><input class="adm-input" name="phone" maxlength="40" value="<?= e($personal['phone'] ?? '') ?>"></div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field"><label>Localisation</label><input class="adm-input" name="location" maxlength="160" value="<?= e($personal['location'] ?? '') ?>"></div>
      <div class="adm-field"><label>Nationalité</label><input class="adm-input" name="nationality" maxlength="80" value="<?= e($personal['nationality'] ?? '') ?>"></div>
    </div>
    <div class="adm-grid2">
      <div class="adm-field"><label>Site web (URL)</label><input class="adm-input" name="website" maxlength="300" value="<?= e($personal['website'] ?? '') ?>"></div>
      <div class="adm-field"><label>LinkedIn (URL)</label><input class="adm-input" name="linkedin" maxlength="300" value="<?= e($personal['linkedin'] ?? '') ?>"></div>
    </div>
    <button type="submit" class="adm-btn">Enregistrer</button>
  </form>
</div>

<?php if ($lang === 'en'): ?>
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Traductions manuelles EN — sans clé API</h2>
  <p class="adm-help">Complétez les champs anglais enregistrés ici avant de publier. Les valeurs EN saisies sont prioritaires et ne sont pas écrasées par la traduction automatique.</p>
  <p class="adm-help">Si une rubrique ne contient aucun élément, ajoutez d’abord son contenu source dans <a href="profile.php">Profil &amp; Paramètres</a>, puis revenez ici pour enregistrer sa version anglaise.</p>

  <h3>Expériences</h3>
  <?php if ($cvExperiences === []): ?><p class="adm-muted">Aucune expérience dans le CV dynamique.</p><?php endif; ?>
  <?php foreach ($cvExperiences as $item): ?>
    <form method="POST" class="adm-card" style="background:var(--ivory);padding:12px;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="manual_en_save">
      <input type="hidden" name="section" value="experience">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <strong><?= e($item['org_fr']) ?> — <?= e($item['role_fr']) ?></strong>
      <div class="adm-grid2">
        <div class="adm-field"><label>Organisation (EN)</label><input class="adm-input" name="org_en" maxlength="200" value="<?= e($item['org_en']) ?>"></div>
        <div class="adm-field"><label>Role (EN)</label><input class="adm-input" name="role_en" maxlength="200" value="<?= e($item['role_en']) ?>"></div>
      </div>
      <div class="adm-field"><label>Description (EN)</label><textarea class="adm-textarea" rows="2" name="desc_en" maxlength="2000"><?= e($item['desc_en']) ?></textarea></div>
      <button type="submit" class="adm-btn small">Enregistrer</button>
    </form>
  <?php endforeach; ?>

  <h3>Formation</h3>
  <?php if ($cvEducation === []): ?><p class="adm-muted">Aucune formation dans le CV dynamique.</p><?php endif; ?>
  <?php foreach ($cvEducation as $item): ?>
    <form method="POST" class="adm-card" style="background:var(--ivory);padding:12px;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="manual_en_save">
      <input type="hidden" name="section" value="education">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <strong><?= e($item['school_fr']) ?> — <?= e($item['degree_fr']) ?></strong>
      <div class="adm-grid2">
        <div class="adm-field"><label>School (EN)</label><input class="adm-input" name="school_en" maxlength="200" value="<?= e($item['school_en']) ?>"></div>
        <div class="adm-field"><label>Degree (EN)</label><input class="adm-input" name="degree_en" maxlength="200" value="<?= e($item['degree_en']) ?>"></div>
      </div>
      <div class="adm-field"><label>Description (EN)</label><textarea class="adm-textarea" rows="2" name="desc_en" maxlength="2000"><?= e($item['desc_en']) ?></textarea></div>
      <button type="submit" class="adm-btn small">Enregistrer</button>
    </form>
  <?php endforeach; ?>

  <h3>Compétences</h3>
  <?php if ($cvSkills === []): ?><p class="adm-muted">Aucune compétence dans le CV dynamique.</p><?php endif; ?>
  <?php foreach ($cvSkills as $item): ?>
    <form method="POST" class="adm-flex" style="align-items:flex-end;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="manual_en_save">
      <input type="hidden" name="section" value="skill">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <div class="adm-field" style="flex:1"><label><?= e($item['name']) ?> — Name (EN)</label>
        <input class="adm-input" name="name_en" maxlength="120" value="<?= e($item['name_en'] ?? '') ?>"></div>
      <button type="submit" class="adm-btn small">Enregistrer</button>
    </form>
  <?php endforeach; ?>

  <h3>Certifications</h3>
  <?php if ($cvCertifications === []): ?><p class="adm-muted">Aucune certification dans le CV dynamique.</p><?php endif; ?>
  <?php foreach ($cvCertifications as $item): ?>
    <form method="POST" class="adm-flex" style="align-items:flex-end;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="manual_en_save">
      <input type="hidden" name="section" value="certification">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <div class="adm-field" style="flex:1"><label><?= e($item['name_fr']) ?> — Name (EN)</label>
        <input class="adm-input" name="name_en" maxlength="200" value="<?= e($item['name_en']) ?>"></div>
      <button type="submit" class="adm-btn small">Enregistrer</button>
    </form>
  <?php endforeach; ?>

  <h3>Distinctions</h3>
  <?php if ($cvDistinctions === []): ?><p class="adm-muted">Aucune distinction dans le CV dynamique.</p><?php endif; ?>
  <?php foreach ($cvDistinctions as $item): ?>
    <form method="POST" class="adm-card" style="background:var(--ivory);padding:12px;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="manual_en_save">
      <input type="hidden" name="section" value="distinction">
      <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
      <strong><?= e($item['title_fr']) ?></strong>
      <div class="adm-field"><label>Title (EN)</label><input class="adm-input" name="title_en" maxlength="200" value="<?= e($item['title_en']) ?>"></div>
      <div class="adm-field"><label>Description (EN)</label><textarea class="adm-textarea" rows="2" name="desc_en" maxlength="400"><?= e($item['desc_en']) ?></textarea></div>
      <button type="submit" class="adm-btn small">Enregistrer</button>
    </form>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- =========================== RUBRIQUES =========================== -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Ordre et visibilité des rubriques — <?= strtoupper($lang) ?></h2>
  <p class="adm-help">Utilisez les flèches ↑ ↓ pour réorganiser. Décochez « visible » pour masquer une rubrique du CV <?= strtoupper($lang) ?> uniquement.</p>
  <form method="POST" id="sectionsForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="sections_save">
    <input type="hidden" name="order" id="sectionsOrder" value="">
    <table class="adm-table">
      <thead><tr><th>Ordre</th><th>Rubrique</th><th>Titre affiché (facultatif)</th><th>Visible</th></tr></thead>
      <tbody id="sectionsBody">
        <?php foreach ($sections as $s): ?>
          <tr data-key="<?= e($s['section_key']) ?>">
            <td style="white-space:nowrap">
              <button type="button" class="adm-btn small secondary sec-up" title="Monter">&uarr;</button>
              <button type="button" class="adm-btn small secondary sec-down" title="Descendre">&darr;</button>
            </td>
            <td><strong><?= e($defaultTitles[$lang][$s['section_key']] ?? $s['section_key']) ?></strong></td>
            <td><input class="adm-input" name="titles[<?= e($s['section_key']) ?>]" maxlength="120" value="<?= e($s['title']) ?>" placeholder="<?= e($defaultTitles[$lang][$s['section_key']] ?? '') ?>"></td>
            <td><input type="checkbox" name="invisible[]" value="<?= e($s['section_key']) ?>" <?= $s['visible'] ? '' : 'checked' ?> style="transform:scale(1.2)"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="adm-btn" style="margin-top:12px">Enregistrer l'ordre</button>
  </form>
  <script>
    (function () {
      function reorder(dir, btn) {
        var row = btn.closest('tr'), tbody = document.getElementById('sectionsBody');
        if (dir === 'up' && row.previousElementSibling) tbody.insertBefore(row, row.previousElementSibling);
        if (dir === 'down' && row.nextElementSibling) tbody.insertBefore(row.nextElementSibling, row);
      }
      document.querySelectorAll('.sec-up').forEach(function (b) { b.addEventListener('click', function () { reorder('up', b); }); });
      document.querySelectorAll('.sec-down').forEach(function (b) { b.addEventListener('click', function () { reorder('down', b); }); });
      document.getElementById('sectionsForm').addEventListener('submit', function () {
        var keys = [];
        document.querySelectorAll('#sectionsBody tr').forEach(function (tr) { keys.push(tr.dataset.key); });
        document.getElementById('sectionsOrder').value = JSON.stringify(keys);
      });
    })();
  </script>
</div>

<!-- ======================= CONTENUS DES RUBRIQUES ======================= -->

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Langues parlées <span class="adm-muted">(données universelles, niveaux FR/EN)</span></h2>
  <table class="adm-table">
    <thead><tr><th>Langue</th><th>Niveau (FR)</th><th>Level (EN)</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($spokens as $i => $sp): ?>
        <tr>
          <form method="POST"><?= csrf_field() ?>
            <input type="hidden" name="action" value="spoken_save"><input type="hidden" name="id" value="<?= (int) $sp['id'] ?>">
            <td><input class="adm-input" name="name" value="<?= e($sp['name']) ?>"></td>
            <td><input class="adm-input" name="level_fr" value="<?= e($sp['level_fr']) ?>"></td>
            <td><input class="adm-input" name="level_en" value="<?= e($sp['level_en']) ?>"></td>
            <td><button type="submit" class="adm-btn small">OK</button></td>
          </form>
          <td>
            <form method="POST" onsubmit="return confirm('Supprimer cette langue ?');"><?= csrf_field() ?>
              <input type="hidden" name="action" value="spoken_delete"><input type="hidden" name="id" value="<?= (int) $sp['id'] ?>">
              <button type="submit" class="adm-btn small danger">×</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <form method="POST"><?= csrf_field() ?>
          <input type="hidden" name="action" value="spoken_save">
          <td><input class="adm-input" name="name" placeholder="Ex. Anglais"></td>
          <td><input class="adm-input" name="level_fr" placeholder="Courant"></td>
          <td><input class="adm-input" name="level_en" placeholder="Fluent"></td>
          <td colspan="2"><button type="submit" class="adm-btn small">+ Ajouter</button></td>
        </form>
      </tr>
    </tbody>
  </table>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Engagements associatifs</h2>
  <?php foreach ($assocs as $a): ?>
    <form method="POST" class="adm-card" style="background:var(--ivory);padding:12px;margin-bottom:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="assoc_save"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
      <div class="adm-grid2">
        <div class="adm-field"><label>Organisation (FR)</label><input class="adm-input" name="org_fr" value="<?= e($a['org_fr']) ?>"></div>
        <div class="adm-field"><label>Organisation (EN)</label><input class="adm-input" name="org_en" value="<?= e($a['org_en']) ?>"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Rôle (FR)</label><input class="adm-input" name="role_fr" value="<?= e($a['role_fr']) ?>"></div>
        <div class="adm-field"><label>Role (EN)</label><input class="adm-input" name="role_en" value="<?= e($a['role_en']) ?>"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Description (FR)</label><textarea class="adm-textarea" rows="2" name="desc_fr"><?= e($a['desc_fr']) ?></textarea></div>
        <div class="adm-field"><label>Description (EN)</label><textarea class="adm-textarea" rows="2" name="desc_en"><?= e($a['desc_en']) ?></textarea></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Début</label><input type="date" class="adm-input" name="date_start" value="<?= e($a['date_start']) ?>"></div>
        <div class="adm-field"><label>Fin</label><input type="date" class="adm-input" name="date_end" value="<?= e($a['date_end']) ?>"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Ordre</label><input type="number" class="adm-input" name="sort_order" value="<?= (int) $a['sort_order'] ?>"></div>
        <div class="adm-field"><label>En cours</label><input type="checkbox" name="is_current" <?= $a['is_current'] ? 'checked' : '' ?>></div>
      </div>
      <div class="adm-flex">
        <button type="submit" class="adm-btn small">Enregistrer</button>
        <button type="submit" class="adm-btn small danger" form="assocDel<?= (int) $a['id'] ?>"
                onclick="return confirm('Supprimer cet engagement ?')">Supprimer</button>
      </div>
    </form>
    <form method="POST" id="assocDel<?= (int) $a['id'] ?>"><?= csrf_field() ?>
      <input type="hidden" name="action" value="assoc_delete"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"></form>
  <?php endforeach; ?>

  <details style="margin-top:10px">
    <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter un engagement</summary>
    <form method="POST" style="margin-top:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="assoc_save">
      <div class="adm-grid2">
        <div class="adm-field"><label>Organisation (FR) *</label><input class="adm-input" name="org_fr" required></div>
        <div class="adm-field"><label>Organisation (EN)</label><input class="adm-input" name="org_en"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Rôle (FR)</label><input class="adm-input" name="role_fr"></div>
        <div class="adm-field"><label>Role (EN)</label><input class="adm-input" name="role_en"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Début</label><input type="date" class="adm-input" name="date_start"></div>
        <div class="adm-field"><label>Fin (vide = en cours)</label><input type="date" class="adm-input" name="date_end"></div>
      </div>
      <button type="submit" class="adm-btn">Ajouter</button>
    </form>
  </details>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Publications du CV</h2>
  <table class="adm-table">
    <thead><tr><th>Titre (FR / EN)</th><th>Support (FR / EN)</th><th>Année</th><th>Ordre</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($pubs as $pb): ?>
        <tr>
          <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="pub_save"><input type="hidden" name="id" value="<?= (int) $pb['id'] ?>">
            <td>
              <input class="adm-input" name="title_fr" aria-label="Titre français" value="<?= e($pb['title_fr']) ?>">
              <input class="adm-input" name="title_en" aria-label="Title in English" value="<?= e($pb['title_en']) ?>">
            </td>
            <td>
              <input class="adm-input" name="venue_fr" aria-label="Support français" value="<?= e($pb['venue_fr']) ?>">
              <input class="adm-input" name="venue_en" aria-label="Venue in English" value="<?= e($pb['venue_en']) ?>">
            </td>
            <td><input type="number" class="adm-input" name="year" value="<?= e((string) ($pb['year'] ?? '')) ?>" style="width:80px"></td>
            <td><input type="number" class="adm-input" name="sort_order" value="<?= (int) $pb['sort_order'] ?>" style="width:70px"></td>
            <td><button type="submit" class="adm-btn small">OK</button></td>
          </form>
          <td>
            <form method="POST" onsubmit="return confirm('Retirer cette publication du CV ?');"><?= csrf_field() ?>
              <input type="hidden" name="action" value="pub_delete"><input type="hidden" name="id" value="<?= (int) $pb['id'] ?>">
              <button type="submit" class="adm-btn small danger">×</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <details style="margin-top:10px">
    <summary class="adm-btn secondary" style="cursor:pointer">+ Ajouter une publication</summary>
    <form method="POST" style="margin-top:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="pub_save">
      <div class="adm-grid2">
        <div class="adm-field"><label>Titre (FR) *</label><input class="adm-input" name="title_fr" required></div>
        <div class="adm-field"><label>Title (EN)</label><input class="adm-input" name="title_en"></div>
      </div>
      <div class="adm-grid2">
        <div class="adm-field"><label>Support (revue/conférence, FR)</label><input class="adm-input" name="venue_fr"></div>
        <div class="adm-field"><label>Année</label><input type="number" class="adm-input" name="year" min="1990" max="2100"></div>
      </div>
      <div class="adm-field"><label>URL</label><input class="adm-input" name="url" maxlength="400"></div>
      <button type="submit" class="adm-btn">Ajouter</button>
    </form>
  </details>
</div>

<!-- ========================= INDICATEUR FR/EN ========================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Version anglaise — complétude</h2>
  <?php
  $cvEn = cv_build('en');
  $missingEn = cv_en_missing_fields($cvEn);
  ?>
  <?php if ($missingEn === []): ?>
    <div class="adm-alert ok" style="margin-top:6px">EN complet — la version anglaise peut être publiée.</div>
  <?php else: ?>
    <div class="adm-alert err" style="margin-top:6px"><strong>EN manquant (<?= count($missingEn) ?> champ(s)) :</strong>
      <?= e(implode(' · ', array_slice($missingEn, 0, 8)) . (count($missingEn) > 8 ? ' …' : '')) ?><br>
      <span style="font-size:13px">Les CV français et anglais sont indépendants : aucun champ EN vide n’est jamais rempli par le français. Complétez la version anglaise ci-dessus.</span></div>
  <?php endif; ?>
</div>

<!-- ========================= MODÈLE & PUBLICATION ========================= -->
<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Modèle de CV — <?= strtoupper($lang) ?></h2>
  <form method="POST" class="adm-flex" style="align-items:flex-end">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="template_save">
    <div class="adm-field" style="margin-bottom:0">
      <label>Modèle</label>
      <select class="adm-select" name="template" style="width:auto">
        <option value="classic" <?= $state['template'] === 'classic' ? 'selected' : '' ?>>Classique — logo à gauche, photo à droite</option>
        <option value="modern" <?= $state['template'] === 'modern' ? 'selected' : '' ?>>Moderne — bande latérale navy, logo blanc</option>
        <option value="diplomatic" <?= $state['template'] === 'diplomatic' ? 'selected' : '' ?>>Diplomatique — logo centré, chronologie en colonnes (recommandé)</option>
      </select>
    </div>
    <button type="submit" class="adm-btn secondary">Appliquer le modèle</button>
  </form>
  <p class="adm-help">Trois modèles sobres, adaptés aux candidatures universitaires, diplomatiques et institutionnelles. La photo est intégrée automatiquement.</p>
</div>

<div class="adm-card">
  <h2 style="margin-top:0;font-size:18px;color:#0a243b">Brouillon et publication — <?= strtoupper($lang) ?></h2>
  <?php if (($state['draft_file'] ?? '') !== ''): ?>
    <p><a class="adm-btn small" href="../uploads/cv/<?= e($state['draft_file']) ?>" target="_blank" rel="noopener">
      Voir le dernier brouillon PDF ↗</a> <span class="adm-help">Généré le <?= e(date('d/m/Y H:i', strtotime((string) ($state['updated_at'] ?? 'now')))) ?></span></p>
  <?php endif; ?>
  <div class="adm-flex">
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="generate_draft">
      <button type="submit" class="adm-btn secondary">Enregistrer comme brouillon (génère un PDF d'aperçu)</button>
    </form>
    <form method="POST" onsubmit="return confirm('Publier le CV ? Génère FR et EN en une action ; chaque version remplace la précédente pour tous les visiteurs (sans cache).');">
      <?= csrf_field() ?><input type="hidden" name="action" value="publish_both">
      <button type="submit" class="adm-btn">Publier les deux CV (FR + EN)</button>
    </form>
    <form method="POST" onsubmit="return confirm('Publier uniquement le CV <?= strtoupper($lang) ?> ?');">
      <?= csrf_field() ?><input type="hidden" name="action" value="publish">
      <button type="submit" class="adm-btn secondary">Publier <?= strtoupper($lang) ?> seul</button>
    </form>
    <?php if ($state['status'] === 'published'): ?>
      <form method="POST" onsubmit="return confirm('Retirer la version publiée ? Le site servira à nouveau l\'ancien CV.');">
        <?= csrf_field() ?><input type="hidden" name="action" value="unpublish">
        <button type="submit" class="adm-btn danger">Dépublier</button>
      </form>
    <?php endif; ?>
  </div>
  <p class="adm-help">
    État actuel : <strong><?= $state['status'] === 'published' ? 'CV publié le ' . e(date('d/m/Y H:i', strtotime((string) $state['published_at']))) : 'brouillon (le site sert l\'ancien CV)' ?></strong>
    <?php if ($state['status'] === 'published' && !empty($state['published_file'])): ?>
      — <a href="../uploads/cv/<?= e($state['published_file']) ?>" target="_blank" rel="noopener">Voir le PDF publié ↗</a>
    <?php endif; ?>
    <?php if (trim((string) ($personal['full_name'] ?? '')) === ''): ?>
      <br><strong style="color:#b3261e">Attention : renseignez le nom complet pour pouvoir publier.</strong>
    <?php endif; ?>
  </p>
  <p class="adm-help">Version <?= strtoupper($otherLang) ?> : <?= $stateOther['status'] === 'published' ? 'publiée' : 'brouillon' ?>.</p>
</div>

<?php admin_footer(); ?>
