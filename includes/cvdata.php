<?php
/**
 * =============================================================================
 *  Portfolio SKS — Couche d'accès : CV dynamique (migration 002)
 * =============================================================================
 *  Réutilise :
 *    - experiences / education / skills / certifications / distinctions (migr. 001)
 *    - cv_documents (historique PDF, migr. 001)
 *  Ajoute : cv_photo, cv_languages, cv_associations, cv_publications,
 *           cv_sections, cv_state, cv_personal.
 *  Toutes les requêtes sont préparées (PDO).
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'content.php';

// -----------------------------------------------------------------------------
//  Photo professionnelle
// -----------------------------------------------------------------------------

/** Photo CV active (ou null). */
function cv_photo_get(): ?array
{
    $row = db()->query('SELECT * FROM cv_photo ORDER BY id DESC LIMIT 1')->fetch();
    return $row !== false ? $row : null;
}

/** Définit la photo (remplace la précédente, une seule ligne conservée). */
function cv_photo_set(string $filename, string $originalName): void
{
    $pdo = db();
    $old = cv_photo_get();
    if ($old !== null) {
        upload_delete('image', (string) $old['filename']);
        $pdo->exec('DELETE FROM cv_photo');
    }
    $pdo->prepare('INSERT INTO cv_photo (filename, original_name) VALUES (:f, :o)')
        ->execute([':f' => $filename, ':o' => mb_substr($originalName, 0, 160, 'UTF-8')]);
}

/** Met à jour les paramètres de recadrage (pourcentages). */
function cv_photo_set_crop(float $x, float $y, float $zoom): void
{
    db()->prepare('UPDATE cv_photo SET crop_x = :x, crop_y = :y, crop_zoom = :z')
        ->execute([':x' => min(100, max(0, $x)), ':y' => min(100, max(0, $y)), ':z' => min(300, max(50, $zoom))]);
}

/** Supprime la photo. */
function cv_photo_delete(): void
{
    $old = cv_photo_get();
    if ($old !== null) {
        upload_delete('image', (string) $old['filename']);
        db()->exec('DELETE FROM cv_photo');
    }
}

// -----------------------------------------------------------------------------
//  Informations personnelles (par langue)
// -----------------------------------------------------------------------------

/** @return array<string,mixed> */
function cv_personal_get(string $lang): array
{
    $stmt = db()->prepare('SELECT * FROM cv_personal WHERE lang = :l');
    $stmt->execute([':l' => $lang === 'en' ? 'en' : 'fr']);
    $row = $stmt->fetch();
    return $row !== false ? $row : [];
}

/** Enregistre les informations personnelles d'une langue. */
function cv_personal_save(string $lang, array $data): void
{
    $lang = $lang === 'en' ? 'en' : 'fr';
    $clean = static function ($v, int $max): string {
        $v = is_scalar($v) ? (string) $v : '';
        $v = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '');
        return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
    };
    db()->prepare(
        'INSERT INTO cv_personal (lang, full_name, headline, summary, email, phone, location, website, linkedin, nationality)
         VALUES (:lang, :fn, :hl, :su, :em, :ph, :lo, :we, :li, :na)
         ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name), headline = VALUES(headline), summary = VALUES(summary),
            email = VALUES(email), phone = VALUES(phone), location = VALUES(location),
            website = VALUES(website), linkedin = VALUES(linkedin), nationality = VALUES(nationality)'
    )->execute([
        ':lang' => $lang,
        ':fn' => $clean($data['full_name'] ?? '', 160),
        ':hl' => $clean($data['headline'] ?? '', 200),
        ':su' => $clean($data['summary'] ?? '', 2000),
        ':em' => $clean($data['email'] ?? '', 254),
        ':ph' => $clean($data['phone'] ?? '', 40),
        ':lo' => $clean($data['location'] ?? '', 160),
        ':we' => $clean($data['website'] ?? '', 300),
        ':li' => $clean($data['linkedin'] ?? '', 300),
        ':na' => $clean($data['nationality'] ?? '', 80),
    ]);
}

// -----------------------------------------------------------------------------
//  Rubriques (ordre + visibilité + titre, par langue)
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> triées par sort_order */
function cv_sections_get(string $lang): array
{
    $stmt = db()->prepare('SELECT * FROM cv_sections WHERE lang = :l ORDER BY sort_order, id');
    $stmt->execute([':l' => $lang === 'en' ? 'en' : 'fr']);
    return $stmt->fetchAll();
}

/** Titres de rubrique par défaut (utilisés si le champ titre est vide). */
function cv_section_default_titles(): array
{
    return [
        'fr' => [
            'summary' => 'Profil professionnel', 'experience' => 'Expérience professionnelle',
            'education' => 'Formation', 'skills' => 'Compétences', 'languages' => 'Langues',
            'certifications' => 'Certifications', 'publications' => 'Publications',
            'associations' => 'Engagements associatifs', 'distinctions' => 'Distinctions',
        ],
        'en' => [
            'summary' => 'Professional Profile', 'experience' => 'Professional Experience',
            'education' => 'Education', 'skills' => 'Skills', 'languages' => 'Languages',
            'certifications' => 'Certifications', 'publications' => 'Publications',
            'associations' => 'Community Engagement', 'distinctions' => 'Honours & Awards',
        ],
    ];
}

/** Réordonne / rend visible-invisible / renomme les rubriques d'une langue. */
function cv_sections_save(string $lang, array $order, array $titles, array $invisible): void
{
    $lang = $lang === 'en' ? 'en' : 'fr';
    $pdo = db();
    $stmt = $pdo->prepare(
        'UPDATE cv_sections SET sort_order = :o, title = :t, visible = :v WHERE section_key = :k AND lang = :l'
    );
    $clean = static function ($v): string {
        $v = is_scalar($v) ? trim((string) $v) : '';
        return function_exists('mb_substr') ? mb_substr($v, 0, 120, 'UTF-8') : substr($v, 0, 120);
    };
    foreach ($order as $i => $key) {
        $stmt->execute([
            ':o' => ($i + 1) * 10,
            ':t' => $clean($titles[$key] ?? ''),
            ':v' => in_array($key, $invisible, true) ? 0 : 1,
            ':k' => $key,
            ':l' => $lang,
        ]);
    }
}

// -----------------------------------------------------------------------------
//  Langues parlées
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> */
function cv_spoken_languages(): array
{
    return db()->query('SELECT * FROM cv_languages ORDER BY sort_order, id')->fetchAll();
}

function cv_spoken_language_save(array $data, int $id = 0): void
{
    $clean = static function ($v, int $max): string {
        $v = is_scalar($v) ? trim((string) $v) : '';
        return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
    };
    if ($id > 0) {
        db()->prepare('UPDATE cv_languages SET name = :n, level_fr = :lf, level_en = :le WHERE id = :id')
            ->execute([':n' => $clean($data['name'] ?? '', 60), ':lf' => $clean($data['level_fr'] ?? '', 60),
                       ':le' => $clean($data['level_en'] ?? '', 60), ':id' => $id]);
    } else {
        db()->prepare('INSERT INTO cv_languages (name, level_fr, level_en, sort_order) VALUES (:n, :lf, :le, :o)')
            ->execute([':n' => $clean($data['name'] ?? '', 60), ':lf' => $clean($data['level_fr'] ?? '', 60),
                       ':le' => $clean($data['level_en'] ?? '', 60), ':o' => (int) ($data['sort_order'] ?? 0)]);
    }
}

// -----------------------------------------------------------------------------
//  Engagements associatifs
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> */
function cv_associations_all(): array
{
    return db()->query('SELECT * FROM cv_associations ORDER BY sort_order, date_start DESC, id DESC')->fetchAll();
}

/** Insère ou met à jour un engagement associatif. */
function cv_association_save(array $data, int $id = 0): void
{
    $clean = static function ($v, int $max): string {
        $v = is_scalar($v) ? trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '') : '';
        return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
    };
    $ds = trim((string) ($data['date_start'] ?? ''));
    $de = trim((string) ($data['date_end'] ?? ''));
    $vals = [
        ':o1' => $clean($data['org_fr'] ?? '', 200), ':o2' => $clean($data['org_en'] ?? '', 200),
        ':r1' => $clean($data['role_fr'] ?? '', 200), ':r2' => $clean($data['role_en'] ?? '', 200),
        ':d1' => $clean($data['desc_fr'] ?? '', 2000), ':d2' => $clean($data['desc_en'] ?? '', 2000),
        ':ds' => $ds !== '' && strtotime($ds) ? date('Y-m-d', strtotime($ds)) : null,
        ':de' => $de !== '' && strtotime($de) ? date('Y-m-d', strtotime($de)) : null,
        ':cur' => isset($data['is_current']) ? 1 : 0,
        ':so' => (int) ($data['sort_order'] ?? 0),
    ];
    if ($id > 0) {
        db()->prepare(
            'UPDATE cv_associations SET org_fr = :o1, org_en = :o2, role_fr = :r1, role_en = :r2,
                desc_fr = :d1, desc_en = :d2, date_start = :ds, date_end = :de,
                is_current = :cur, sort_order = :so WHERE id = :id'
        )->execute($vals + [':id' => $id]);
    } else {
        db()->prepare(
            'INSERT INTO cv_associations (org_fr, org_en, role_fr, role_en, desc_fr, desc_en,
                date_start, date_end, is_current, sort_order)
             VALUES (:o1, :o2, :r1, :r2, :d1, :d2, :ds, :de, :cur, :so)'
        )->execute($vals);
    }
}

// -----------------------------------------------------------------------------
//  Publications du CV
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> */
function cv_publications_all(): array
{
    return db()->query('SELECT * FROM cv_publications ORDER BY sort_order, year DESC, id DESC')->fetchAll();
}

function cv_publication_save(array $data, int $id = 0): void
{
    $clean = static function ($v, int $max): string {
        $v = is_scalar($v) ? trim((string) $v) : '';
        return function_exists('mb_substr') ? mb_substr($v, 0, $max, 'UTF-8') : substr($v, 0, $max);
    };
    $url = $clean($data['url'] ?? '', 400);
    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
        $url = '';
    }
    $aid = (int) ($data['article_id'] ?? 0);
    $vals = [
        ':t1' => $clean($data['title_fr'] ?? '', 255), ':t2' => $clean($data['title_en'] ?? '', 255),
        ':v1' => $clean($data['venue_fr'] ?? '', 200), ':v2' => $clean($data['venue_en'] ?? '', 200),
        ':y' => (int) ($data['year'] ?? 0) ?: null,
        ':u' => $url,
        ':a' => $aid > 0 ? $aid : null,
        ':so' => (int) ($data['sort_order'] ?? 0),
    ];
    if ($id > 0) {
        db()->prepare('UPDATE cv_publications SET title_fr = :t1, title_en = :t2, venue_fr = :v1,
            venue_en = :v2, year = :y, url = :u, article_id = :a, sort_order = :so WHERE id = :id')
            ->execute($vals + [':id' => $id]);
    } else {
        db()->prepare('INSERT INTO cv_publications (title_fr, title_en, venue_fr, venue_en, year, url, article_id, sort_order)
            VALUES (:t1, :t2, :v1, :v2, :y, :u, :a, :so)')->execute($vals);
    }
}

// -----------------------------------------------------------------------------
//  État du CV (brouillon / publié / modèle)
// -----------------------------------------------------------------------------

/** @return array<string,mixed> */
function cv_state_get(string $lang): array
{
    $stmt = db()->prepare('SELECT * FROM cv_state WHERE lang = :l');
    $stmt->execute([':l' => $lang === 'en' ? 'en' : 'fr']);
    $row = $stmt->fetch();
    return $row !== false ? $row : ['template' => 'diplomatic', 'status' => 'draft',
        'published_file' => null, 'draft_file' => null];
}

/** Change le modèle. */
function cv_state_set_template(string $lang, string $template): void
{
    $allowed = ['classic', 'modern', 'diplomatic'];
    if (!in_array($template, $allowed, true)) {
        return;
    }
    db()->prepare('UPDATE cv_state SET template = :t WHERE lang = :l')->execute([':t' => $template, ':l' => $lang]);
}

/** Marque le CV comme publié avec son fichier PDF. */
function cv_state_publish(string $lang, string $file): void
{
    db()->prepare(
        'UPDATE cv_state SET status = "published", published_file = :f, published_at = NOW() WHERE lang = :l'
    )->execute([':f' => $file, ':l' => $lang]);
}

/** Remet en brouillon (les modifications ne sont plus publiées). */
function cv_state_unpublish(string $lang): void
{
    db()->prepare('UPDATE cv_state SET status = "draft" WHERE lang = :l')->execute([':l' => $lang]);
}

/**
 * URL publique du CV généré pour une langue :
 *   - version publiée si disponible,
 *   - sinon repli sur l'historique cv_documents (migration 001),
 *   - sinon repli sur les PDF statiques d'origine (documents/CV_SKS*.pdf).
 */
function cv_generated_href(string $lang): string
{
    $state = cv_state_get($lang);
    if (($state['status'] ?? '') === 'published' && !empty($state['published_file'])) {
        return 'uploads/cv/' . rawurlencode((string) $state['published_file']);
    }
    return cv_public_href($lang); // repli migration 001 puis PDF statiques
}

// -----------------------------------------------------------------------------
//  Agrégat complet utilisé par le générateur PDF
// -----------------------------------------------------------------------------

/**
 * Rassemble toutes les données d'un CV dans une langue, dans l'ordre des
 * rubriques défini par l'administrateur.
 *
 * @return array{personal:array, photo:?array, template:string, sections:array<int,array>}
 */
function cv_build(string $lang, bool $autoTranslate = null): array
{
    // Deux CV indépendants (FR / EN) : AUCUNE traduction automatique.
    // Le paramètre $autoTranslate est conservé pour compatibilité d'appel
    // mais ignoré — les champs EN vides restent vides, jamais remplacés
    // par du français.
    $lang = $lang === 'en' ? 'en' : 'fr';
    $sectionRows = cv_sections_get($lang);
    $state = cv_state_get($lang);

    $personal = cv_personal_get($lang);
    $data = [
        'summary' => ['text' => (string) ($personal['summary'] ?? '')],
        'experience' => ['items' => experiences_all()],
        'education' => ['items' => education_all()],
        'skills' => ['items' => skills_all()],
        'languages' => ['items' => cv_spoken_languages()],
        'certifications' => ['items' => certifications_all()],
        'publications' => ['items' => cv_publications_all()],
        'associations' => ['items' => cv_associations_all()],
        'distinctions' => ['items' => distinctions_all()],
    ];

    // Deux CV indépendants : aucun remplissage automatique côté EN.
    $translation = null;

    $sections = [];
    foreach ($sectionRows as $row) {
        $key = (string) $row['section_key'];
        // Alias : la base utilise « profile » pour la rubrique de résumé.
        if ($key === 'profile') {
            $key = 'summary';
        }
        if (!isset($data[$key]) || !(int) $row['visible']) {
            continue;
        }
        $defaultTitles = cv_section_default_titles();
        $sections[] = [
            'key' => $key,
            'title' => (string) $row['title'] !== '' ? (string) $row['title']
                : ($defaultTitles[$lang][$key] ?? $key),
            'data' => $data[$key],
        ];
    }

    return [
        'personal' => $personal,
        'translation' => $translation,
        'photo' => cv_photo_get(),
        'template' => (string) ($state['template'] ?? 'diplomatic'),
        'sections' => $sections,
        'lang' => $lang,
    ];
}

/**
 * Liste des champs anglais encore vides d'un CV (deux CV indépendants).
 * Sert de garde-fou à la publication : un CV EN incomplet n'est jamais
 * publié, et aucun texte français n'y est substitué.
 *
 * @param array<string,mixed> $cv  résultat de cv_build('en')
 * @return string[] libellés lisibles des champs manquants
 */
function cv_en_missing_fields(array $cv): array
{
    $missing = [];
    $p = $cv['personal'] ?? [];
    foreach ([
        'Titre professionnel (EN)' => 'headline',
        'Résumé (EN)'              => 'summary',
    ] as $label => $key) {
        if (trim((string) ($p[$key] ?? '')) === '') {
            $missing[] = $label;
        }
    }
    foreach (($cv['sections'] ?? []) as $sec) {
        foreach (($sec['data']['items'] ?? []) as $item) {
            // Chaque rubrique a ses champs _en ; on signale les vides.
            foreach ($item as $k => $v) {
                if (is_string($k) && str_ends_with($k, '_en') && trim($v) === '') {
                    $missing[] = $sec['title'] . ' — ' . $k;
                    break;
                }
            }
        }
    }
    return array_values(array_unique($missing));
}
