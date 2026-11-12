<?php
/**
 * =============================================================================
 *  Portfolio SOADAN Koffi Sylvain (SKS) — Couche d'accès aux contenus
 * =============================================================================
 *  Fonctions de lecture des modules de contenu (articles, projets, galerie,
 *  CV, profil, paramètres). Toutes les requêtes sont préparées (PDO).
 *
 *  Cette couche est purement additive : elle ne modifie aucun comportement
 *  existant. Les pages statiques d'origine continuent de fonctionner telles
 *  quelles ; les contenus dynamiques s'y ajoutent.
 * =============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'database.php';

// -----------------------------------------------------------------------------
//  Paramètres du site
// -----------------------------------------------------------------------------

/**
 * Retourne tous les paramètres du site sous forme de tableau clé => valeur.
 *
 * @return array<string,string>
 */
function settings_all(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    try {
        $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        $cache = [];
        foreach ($rows as $r) {
            $cache[$r['setting_key']] = (string) $r['setting_value'];
        }
    } catch (Throwable $e) {
        log_event('error', 'settings_all: unavailable');
        $cache = [];
    }
    return $cache;
}

/** Retourne un paramètre (ou une valeur par défaut). */
function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return ($all[$key] ?? '') !== '' ? (string) $all[$key] : $default;
}

/** Met à jour (ou insère) un paramètre. */
function setting_set(PDO $pdo, string $key, string $value): void
{
    $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    )->execute([':k' => $key, ':v' => $value]);
}

// -----------------------------------------------------------------------------
//  Catégories
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> */
function categories_all(): array
{
    return db()->query('SELECT * FROM categories ORDER BY sort_order, id')->fetchAll();
}

/** Retourne le libellé d'une catégorie dans la langue courante. */
function category_label(array $cat, string $lang): string
{
    $field = $lang === 'en' ? 'name_en' : 'name_fr';
    $label = (string) ($cat[$field] ?? '');
    return $label !== '' ? $label : (string) ($cat['name_fr'] ?? '');
}

// -----------------------------------------------------------------------------
//  Articles
// -----------------------------------------------------------------------------

/**
 * Indique si la colonne bilingue $field existe sur la table $table.
 * Sert à rester compatible avec les bases pas encore migrées en 003.
 */
function db_column_exists(string $table, string $field): bool
{
    static $cache = [];
    $key = $table . '.' . $field;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    try {
        if (db_driver() === 'sqlite') {
            $cols = db()->query('PRAGMA table_info(' . $table . ')')->fetchAll(PDO::FETCH_COLUMN, 1);
            return $cache[$key] = in_array($field, $cols, true);
        }
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c'
        );
        $stmt->execute([':t' => $table, ':c' => $field]);
        return $cache[$key] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return $cache[$key] = false;
    }
}

/**
 * Complétude EN d'une ligne bilingue : 'ok' | 'missing' | 'none' (pas de
 * colonnes _en — base non migrée). Utilisée pour les indicateurs d'admin
 * et pour le filtrage des pages anglaises.
 */
function bilingual_status(array $row): string
{
    if (!isset($row['title_en'])) {
        return 'none';
    }
    $hasAny = false;
    foreach ($row as $k => $v) {
        if (is_string($k) && str_ends_with($k, '_en') && trim((string) $v) !== '') {
            $hasAny = true;
            break;
        }
    }
    return $hasAny ? 'ok' : 'missing';
}

/**
 *Vue normalisée d'une ligne bilingue pour l'affichage dans $lang :
 * retourne title/excerpt/content_html/seo_… contenant la valeur de la
 * langue demandée. Principe : si la version $lang est vide alors que la
 * ligne possède des colonnes bilingues, les champs restent VIDES (jamais
 * de français dans la page anglaise) — l'appelant filtre la ligne.
 *
 * @param array<string,mixed> $row
 * @return array<string,mixed> mêmes clés, valeurs localisées
 */
function bilingual_pick(array $row, string $lang): array
{
    if ($lang !== 'en' || bilingual_status($row) !== 'ok') {
        return $row;
    }
    $out = $row;
    foreach ($row as $k => $v) {
        if (is_string($k) && str_ends_with($k, '_en')) {
            $base = substr($k, 0, -3);
            $out[$base] = $v;          // champ affiché dans la langue EN
            $out[$k] = $v;             // conservé pour référence
        }
    }
    // Slug anglais distinct s'il existe (page détail EN).
    if (!empty($row['slug_en'])) {
        $out['slug'] = $row['slug_en'];
    }
    return $out;
}

/**
 * Liste des articles publiés (ou tous si $onlyPublished = false).
 * Chaque ligne porte ses champs FR et EN (une ligne = les deux langues).
 * $lang !== null : filtre — les articles sans version anglaise n'apparaissent
 * PAS sur les pages anglaises (jamais de français affiché en EN).
 *
 * @return array<int,array<string,mixed>>
 */
function articles_list(bool $onlyPublished = true, int $limit = 100, ?string $lang = null): array
{
    $sql = 'SELECT a.*, c.slug AS category_slug, c.name_fr AS category_fr, c.name_en AS category_en
            FROM articles a LEFT JOIN categories c ON c.id = a.category_id';
    $where = [];
    $params = [];
    if ($onlyPublished) {
        $where[] = 'a.status = "published"';
    }
    $sql .= $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $sql .= ' ORDER BY a.published_at DESC, a.id DESC LIMIT ' . max(1, $limit);
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if ($lang === 'en') {
        $rows = array_values(array_filter($rows, static fn ($r) => bilingual_status($r) === 'ok'));
    }
    return $rows;
}

/**
 * Retourne un article par son slug (publié uniquement côté public).
 * $lang='en' : cherche d'abord slug_en (ligne bilingue dont la version EN
 * est complète), puis le slug FR pour compatibilité des anciens liens.
 */
function article_by_slug(string $slug, bool $onlyPublished = true, string $lang = 'fr'): ?array
{
    $sql = 'SELECT a.*, c.slug AS category_slug, c.name_fr AS category_fr, c.name_en AS category_en
            FROM articles a LEFT JOIN categories c ON c.id = a.category_id
            WHERE (a.slug = :slug OR a.slug_en = :slug)';
    if ($onlyPublished) {
        $sql .= ' AND a.status = "published"';
    }
    $sql .= ' ORDER BY (a.slug = :slug) DESC LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/** Retourne un article par identifiant (administration). */
function article_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/** Tags d'un article (noms simples). @return string[] */
function article_tags(int $articleId): array
{
    $stmt = db()->prepare(
        'SELECT t.name, t.slug FROM article_tags at
         JOIN tags t ON t.id = at.tag_id WHERE at.article_id = :id ORDER BY t.name'
    );
    $stmt->execute([':id' => $articleId]);
    return $stmt->fetchAll();
}

/**
 * Enregistre (insère ou met à jour) un article et ses tags.
 *
 * @param array<string,mixed> $data champs validés
 * @param string[]            $tags mots-clés
 * @return int identifiant de l'article
 */
/**
 * Enregistre un article : UNE ligne porte les deux langues (migration 003).
 * $data contient les champs FR (_fr implicites) ET les champs *_en facultatifs
 * (title_en, excerpt_en, content_html_en, seo_title_en, seo_description_en,
 * slug_en). Un seul « Enregistrer » écrit les deux versions.
 */
function article_save(PDO $pdo, array $data, array $tags): int
{
    $hasBilingual = db_column_exists('articles', 'title_en');

    $enCols = [
        'slug_en'          => ':slug_en',
        'title_en'         => ':title_en',
        'excerpt_en'       => ':excerpt_en',
        'content_html_en'  => ':content_en',
        'seo_title_en'     => ':seo_title_en',
        'seo_description_en' => ':seo_desc_en',
    ];

    if (!empty($data['id'])) {
        $sets = 'slug = :slug, title = :title, excerpt = :excerpt, content_html = :content,
                cover_image = :cover, category_id = :category, status = :status,
                published_at = :published, seo_title = :seo_title, seo_description = :seo_desc';
        $params = [
            ':slug' => $data['slug'], ':title' => $data['title'], ':excerpt' => $data['excerpt'],
            ':content' => $data['content'], ':cover' => $data['cover_image'],
            ':category' => $data['category_id'], ':status' => $data['status'],
            ':published' => $data['published_at'], ':seo_title' => $data['seo_title'],
            ':seo_desc' => $data['seo_description'], ':id' => (int) $data['id'],
        ];
        if ($hasBilingual) {
            foreach ($enCols as $col => $ph) {
                $sets .= ', ' . $col . ' = ' . $ph;
                $params[$ph] = (string) ($data[$col] ?? '');
            }
        }
        $pdo->prepare('UPDATE articles SET ' . $sets . ' WHERE id = :id')->execute($params);
        $articleId = (int) $data['id'];
        $pdo->prepare('DELETE FROM article_tags WHERE article_id = :id')->execute([':id' => $articleId]);
    } else {
        $cols = ['slug', 'lang', 'title', 'excerpt', 'content_html', 'cover_image', 'category_id',
                 'status', 'published_at', 'seo_title', 'seo_description'];
        $phs   = [':slug', ':lang', ':title', ':excerpt', ':content', ':cover', ':category',
                  ':status', ':published', ':seo_title', ':seo_desc'];
        $params = [
            ':slug' => $data['slug'], ':lang' => $data['lang'] ?? 'fr', ':title' => $data['title'],
            ':excerpt' => $data['excerpt'], ':content' => $data['content'], ':cover' => $data['cover_image'],
            ':category' => $data['category_id'], ':status' => $data['status'],
            ':published' => $data['published_at'], ':seo_title' => $data['seo_title'],
            ':seo_desc' => $data['seo_description'],
        ];
        if ($hasBilingual) {
            foreach ($enCols as $col => $ph) {
                $cols[] = $col;
                $phs[] = $ph;
                $params[$ph] = (string) ($data[$col] ?? '');
            }
        }
        $pdo->prepare(
            'INSERT INTO articles (' . implode(', ', $cols) . ')
             VALUES (' . implode(', ', $phs) . ')'
        )->execute($params);
        $articleId = (int) $pdo->lastInsertId();
    }

    // Tags : crée les tags manquants puis associe.
    foreach ($tags as $name) {
        $name = trim($name);
        if ($name === '' || mb_strlen($name, 'UTF-8') > 80) {
            continue;
        }
        $slug = slugify($name);
        if ($slug === '') {
            continue;
        }
        $pdo->prepare('INSERT IGNORE INTO tags (slug, name) VALUES (:s, :n)')
            ->execute([':s' => $slug, ':n' => $name]);
        $tagId = (int) $pdo->query('SELECT id FROM tags WHERE slug = ' . $pdo->quote($slug))->fetchColumn();
        $pdo->prepare('INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (:a, :t)')
            ->execute([':a' => $articleId, ':t' => $tagId]);
    }
    return $articleId;
}

/** Génère un slug URL-safe à partir d'un texte (FR/EN). */
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if (is_string($trans) && $trans !== '') {
        $text = $trans;
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return mb_substr($text, 0, 150, 'UTF-8');
}

// -----------------------------------------------------------------------------
//  Projets
// -----------------------------------------------------------------------------

/**
 * Liste des projets publiés. $lang='en' : ne retient que les projets dont
 * la version anglaise est complète (jamais de français sur les pages EN).
 * @return array<int,array<string,mixed>>
 */
function projects_list(bool $onlyPublished = true, ?string $lang = null): array
{
    $sql = 'SELECT * FROM projects';
    if ($onlyPublished) {
        $sql .= ' WHERE status = "published"';
    }
    $sql .= ' ORDER BY sort_order, id DESC';
    $rows = db()->query($sql)->fetchAll();
    if ($lang === 'en') {
        $rows = array_values(array_filter($rows, static fn ($r) => bilingual_status($r) === 'ok'));
    }
    return $rows;
}

/** Retourne un projet (par id ou par slug). */
function project_get(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * Insère ou met à jour un projet : UNE ligne porte FR + EN (migration 003).
 * Champs EN facultatifs : title_en, role_en, summary_en, description_en,
 * objectives_en, results_en, partners_en. @return int
 */
function project_save(PDO $pdo, array $data): int
{
    $hasBilingual = db_column_exists('projects', 'title_en');
    $enMap = [
        'title_en' => ':title_en', 'role_en' => ':role_en', 'summary_en' => ':summary_en',
        'description_en' => ':descr_en', 'objectives_en' => ':obj_en',
        'results_en' => ':res_en', 'partners_en' => ':partners_en',
    ];

    $baseCols = ['slug', 'title', 'role', 'summary', 'description_html', 'objectives_html',
                 'results_html', 'partners', 'image', 'external_url', 'date_start', 'date_end',
                 'status', 'sort_order'];
    $basePh   = [':slug', ':title', ':role', ':summary', ':descr', ':obj', ':res', ':partners',
                 ':image', ':url', ':ds', ':de', ':status', ':sort'];
    $baseParams = [
        ':slug' => $data['slug'], ':title' => $data['title'], ':role' => $data['role'],
        ':summary' => $data['summary'], ':descr' => $data['description_html'],
        ':obj' => $data['objectives_html'], ':res' => $data['results_html'],
        ':partners' => $data['partners'], ':image' => $data['image'],
        ':url' => $data['external_url'], ':ds' => $data['date_start'], ':de' => $data['date_end'],
        ':status' => $data['status'], ':sort' => $data['sort_order'],
    ];

    $enCols = [];
    $enPhs = [];
    if ($hasBilingual) {
        foreach ($enMap as $col => $ph) {
            $enCols[] = $col;
            $enPhs[] = $ph;
            $baseParams[$ph] = (string) ($data[$col] ?? '');
        }
    }

    if (!empty($data['id'])) {
        $sets = [];
        foreach ($baseCols as $i => $c) {
            $sets[] = $c . ' = ' . $basePh[$i];
        }
        foreach ($enCols as $i => $c) {
            $sets[] = $c . ' = ' . $enPhs[$i];
        }
        $baseParams[':id'] = (int) $data['id'];
        $pdo->prepare('UPDATE projects SET ' . implode(', ', $sets) . ' WHERE id = :id')->execute($baseParams);
        return (int) $data['id'];
    }
    $pdo->prepare(
        'INSERT INTO projects (' . implode(', ', array_merge($baseCols, $enCols)) . ')
         VALUES (' . implode(', ', array_merge($basePh, $enPhs)) . ')'
    )->execute($baseParams);
    return (int) $pdo->lastInsertId();
}

// -----------------------------------------------------------------------------
//  Galerie
//  Le bandeau animé de l'accueil RÉUTILISE gallery_photos : une photo
//  apparaît dans le bandeau si show_home = 1 ET approved = 1.
// -----------------------------------------------------------------------------

/**
 * Slides du diaporama d'accueil.
 * UNE SEULE liste : les photos marquées « Afficher sur l'accueil »
 * (show_home = 1, approved = 1), dans l'ordre de tri défini en admin.
 * Les drapeaux, s'il y en a, sont INTERCALÉS parmi les photos — uniquement
 * ceux explicitement cochés dans l'admin (banner_flags_codes). Par défaut :
 * aucun drapeau. Aucune ligne de drapeaux défilante n'est produite.
 *
 * @return array{enabled:bool, flags_mode:string, flags_codes:string[],
 *               speed_seconds:int, photos:array<int,array<int,array<string,mixed>>>}
 */
function banner_data(): array
{
    $enabled = setting('banner_enabled', '1') === '1';
    $speed   = (int) setting('home_slide_seconds', '6'); // durée PAR PHOTO
    $speed   = ($speed >= 2 && $speed <= 60) ? $speed : 6;

    $slides  = [];
    $fallback = false; // image de secours si aucune photo

    if ($enabled) {
        try {
            // 1) Rubrique dédiée « Photos d'accueil » (migration 005).
            if (db_column_exists('home_photos', 'filename')) {
                $stmt = db()->query(
                    'SELECT id, filename, caption_fr AS caption, caption_en, alt_fr AS alt_text
                     FROM home_photos WHERE active = 1 ORDER BY sort_order, id DESC'
                );
                $slides = $stmt->fetchAll();
            }
            // 2) Repli : photos de galerie marquées accueil (comportement antérieur).
            if ($slides === [] && db_column_exists('gallery_photos', 'show_home')) {
                $stmt = db()->prepare(
                    'SELECT id, filename, caption, caption_en, alt_text
                     FROM gallery_photos WHERE show_home = 1 AND approved = 1
                     ORDER BY sort_order, id DESC'
                );
                $stmt->execute();
                $slides = $stmt->fetchAll();
            }
        } catch (Throwable $e) {
            log_event('error', 'banner_data: photos unavailable');
            $slides = [];
        }
    }
    $fallback = ($slides === []); // l'accueil garde une image de secours

    // Drapeaux facultatifs : UNIQUEMENT ceux cochés en admin, intercalés
    // toutes les 5 photos. Par défaut : aucun.
    $chosen = [];
    foreach (explode(',', setting('banner_flags_codes', '')) as $code) {
        $code = strtoupper(trim($code));
        if (preg_match('/^[A-Z]{2}$/', $code)) {
            $chosen[] = $code;
        }
    }
    $mixed = [];
    if ($chosen !== [] && $slides !== []) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'un_members.php';
        $available = array_flip(un_member_codes_available());
        $chosen = array_values(array_filter($chosen, static fn ($c) => isset($available[$c])));
        $ci = 0;
        foreach ($slides as $p) {
            $mixed[] = $p;
            if (count($mixed) % 5 === 0 && $ci < count($chosen)) {
                $mixed[] = ['is_flag' => true, 'flag_code' => $chosen[$ci++]];
            }
        }
    } else {
        $mixed = $slides;
    }

    return [
        'enabled'       => $enabled,
        'transition'    => setting('home_transition', 'fade') === 'slide' ? 'slide' : 'fade',
        'slide_seconds' => $speed,
        'flags_codes'   => $chosen,
        'photos'        => [$mixed],
        'fallback'      => $fallback,
    ];
}

// -----------------------------------------------------------------------------
//  Vidéo de présentation de l'accueil (migration 005)
// -----------------------------------------------------------------------------

/** Vidéo de présentation pour une langue, ou null si absente/désactivée. */
function home_video_get(string $lang): ?array
{
    if (setting('home_video_enabled', '1') !== '1' || !db_column_exists('home_video', 'lang')) {
        return null;
    }
    try {
        $stmt = db()->prepare('SELECT * FROM home_video WHERE lang = :l LIMIT 1');
        $stmt->execute([':l' => $lang === 'en' ? 'en' : 'fr']);
        $row = $stmt->fetch();
        if ($row === false || !(int) $row['enabled']) {
            return null; // pas de vidéo EN → rien sur la page EN
        }
        if ($row['source_kind'] === 'embed' && !news_embed_valid((string) $row['embed_url'])) {
            return null;
        }
        if ($row['source_kind'] === 'file' && empty($row['media_file'])) {
            return null;
        }
        return $row;
    } catch (Throwable $e) {
        log_event('error', 'home_video_get: unavailable');
        return null;
    }
}

// -----------------------------------------------------------------------------
//  Images supplémentaires (articles / projets) — table media_images
// -----------------------------------------------------------------------------

/** Images supplémentaires d'un parent ('article'|'project'). @return array<int,array<string,mixed>> */
function media_images_for(string $parentType, int $parentId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM media_images WHERE parent_type = :t AND parent_id = :id ORDER BY sort_order, id'
    );
    $stmt->execute([':t' => $parentType === 'project' ? 'project' : 'article', ':id' => $parentId]);
    return $stmt->fetchAll();
}

/** Ajoute une image supplémentaire. @return int identifiant créé */
function media_image_add(string $parentType, int $parentId, string $filename, array $captions = []): int
{
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO media_images (parent_type, parent_id, filename, caption_fr, caption_en, alt_fr, alt_en)
         VALUES (:t, :id, :f, :cf, :ce, :af, :ae)'
    )->execute([
        ':t'  => $parentType === 'project' ? 'project' : 'article',
        ':id' => $parentId,
        ':f'  => $filename,
        ':cf' => (string) ($captions['caption_fr'] ?? ''),
        ':ce' => (string) ($captions['caption_en'] ?? ''),
        ':af' => (string) ($captions['alt_fr'] ?? (string) ($captions['caption_fr'] ?? '')),
        ':ae' => (string) ($captions['alt_en'] ?? (string) ($captions['caption_en'] ?? '')),
    ]);
    return (int) $pdo->lastInsertId();
}

/** Supprime une image supplémentaire (fichier inclus). */
function media_image_delete(int $id): void
{
    $stmt = db()->prepare('SELECT filename FROM media_images WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $fn = (string) ($stmt->fetchColumn() ?: '');
    if ($fn !== '') {
        upload_delete('image', $fn);
        db()->prepare('DELETE FROM media_images WHERE id = :id')->execute([':id' => $id]);
    }
}

/** Photos publiques (approuvées uniquement). @return array<int,array<string,mixed>> */
function gallery_public(?string $category = null): array
{
    $sql = 'SELECT * FROM gallery_photos WHERE approved = 1';
    $params = [];
    if ($category !== null && $category !== '') {
        $sql .= ' AND category = :cat';
        $params[':cat'] = $category;
    }
    $sql .= ' ORDER BY sort_order, taken_on DESC, id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Toutes les photos (administration). @return array<int,array<string,mixed>> */
function gallery_all(): array
{
    return db()->query('SELECT * FROM gallery_photos ORDER BY sort_order, id DESC')->fetchAll();
}

/** Libellés des catégories de galerie (FR/EN). @return array<string,array<string,string>> */
function gallery_categories(): array
{
    return [
        'conferences' => ['fr' => 'Conférences et rencontres', 'en' => 'Conferences and meetings'],
        'diplomatic'  => ['fr' => 'Activités diplomatiques',    'en' => 'Diplomatic activities'],
        'association' => ['fr' => 'Engagement associatif',      'en' => 'Community engagement'],
        'training'    => ['fr' => 'Formations et certifications', 'en' => 'Training and certifications'],
        'projects'    => ['fr' => 'Projets et initiatives',     'en' => 'Projects and initiatives'],
        'speeches'    => ['fr' => 'Interventions publiques',    'en' => 'Public speaking'],
    ];
}

// -----------------------------------------------------------------------------
//  CV
// -----------------------------------------------------------------------------

/** Retourne le CV actif pour une langue ('fr'|'en'), ou null si aucun. */
function cv_active(string $lang): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM cv_documents WHERE lang = :lang AND is_active = 1 ORDER BY version DESC LIMIT 1'
    );
    $stmt->execute([':lang' => $lang === 'en' ? 'en' : 'fr']);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * Chemin public du CV à proposer au visiteur.
 * Repli automatique sur les PDF d'origine (documents/CV_SKS*.pdf) si aucun
 * CV n'a été téléversé — préserve le comportement actuel du site.
 */
function cv_public_href(string $lang): string
{
    $cv = cv_active($lang);
    if ($cv !== null) {
        return 'uploads/cv/' . rawurlencode((string) $cv['filename']);
    }
    return $lang === 'en' ? CV_FILE_EN : CV_FILE_FR;
}

/** Historique des versions d'un CV. @return array<int,array<string,mixed>> */
function cv_history(?string $lang = null): array
{
    $sql = 'SELECT * FROM cv_documents';
    $params = [];
    if ($lang !== null) {
        $sql .= ' WHERE lang = :lang';
        $params[':lang'] = $lang;
    }
    $sql .= ' ORDER BY lang, version DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// -----------------------------------------------------------------------------
//  Profil / parcours
// -----------------------------------------------------------------------------

/** @return array<int,array<string,mixed>> */
function experiences_all(): array
{
    return db()->query('SELECT * FROM experiences ORDER BY sort_order, date_start DESC, id DESC')->fetchAll();
}

/** @return array<int,array<string,mixed>> */
function education_all(): array
{
    return db()->query('SELECT * FROM education ORDER BY sort_order, started DESC, id')->fetchAll();
}

/** @return array<int,array<string,mixed>> */
function skills_all(?string $group = null): array
{
    $sql = 'SELECT * FROM skills';
    $params = [];
    if ($group !== null && $group !== '') {
        $sql .= ' WHERE group_name = :g';
        $params[':g'] = $group;
    }
    $sql .= ' ORDER BY sort_order, id';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** @return array<int,array<string,mixed>> */
function certifications_all(): array
{
    return db()->query('SELECT * FROM certifications ORDER BY sort_order, year DESC, id')->fetchAll();
}

/** @return array<int,array<string,mixed>> */
function distinctions_all(): array
{
    return db()->query('SELECT * FROM distinctions ORDER BY sort_order, year DESC, id')->fetchAll();
}

// -----------------------------------------------------------------------------
//  Actualités — vidéos, capsules et podcasts (migration 004)
// -----------------------------------------------------------------------------

/** Libellés des types de publication (FR/EN). @return array<string,array<string,string>> */
function news_types(): array
{
    return [
        'video'   => ['fr' => 'Vidéo',   'en' => 'Video'],
        'clip'    => ['fr' => 'Capsule / extrait', 'en' => 'Clip / excerpt'],
        'podcast' => ['fr' => 'Podcast (audio)',   'en' => 'Podcast (audio)'],
    ];
}

/** Catégories éditoriales des Actualités (FR/EN). @return array<string,array<string,string>> */
function news_categories(): array
{
    return [
        'conference' => ['fr' => 'Conférence',            'en' => 'Conference'],
        'communaute' => ['fr' => 'Communauté',            'en' => 'Community'],
        'debat'      => ['fr' => 'Débat',                 'en' => 'Debate'],
        'tele'       => ['fr' => 'Télévision',            'en' => 'Television'],
        'podcast'    => ['fr' => 'Podcast',               'en' => 'Podcast'],
        'sortie'     => ['fr' => 'Sortie / activité',     'en' => 'Outing / activity'],
    ];
}

/** Domaines de lecteur autorisés (CSP : à refléter dans .htaccess). @return array<string,string> */
function news_embed_hosts(): array
{
    return [
        'youtube.com'     => 'youtube',
        'youtu.be'        => 'youtube',
        'youtube-nocookie.com' => 'youtube',
        'vimeo.com'       => 'vimeo',
        'player.vimeo.com' => 'vimeo',
        'facebook.com'    => 'facebook',
        'fb.watch'        => 'facebook',
        'tiktok.com'      => 'tiktok',
        'open.spotify.com' => 'spotify',
        'podcasts.apple.com' => 'apple',
        'soundcloud.com'  => 'soundcloud',
    ];
}

/**
 * Valide une URL d'intégration : ne l'accepte que si son domaine figure
 * dans la liste blanche (protection XSS / iframe arbitraire).
 */
function news_embed_valid(string $url): bool
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    if ($host === '' || stripos($url, 'https://') !== 0) {
        return false;
    }
    foreach (news_embed_hosts() as $allowed => $_) {
        if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
            return true;
        }
    }
    return false;
}

/** Liste des publications (admin ou public). @return array<int,array<string,mixed>> */
function news_list(bool $onlyPublished = true, ?string $category = null, int $limit = 60, ?string $lang = null): array
{
    $sql = 'SELECT * FROM news_items';
    $where = [];
    $params = [];
    if ($onlyPublished) {
        $where[] = 'status = \'published\'';
    }
    if ($category !== null && $category !== '') {
        $where[] = 'category = :cat';
        $params[':cat'] = $category;
    }
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY COALESCE(published_at, event_date, created_at) DESC, id DESC LIMIT ' . max(1, $limit);
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if ($lang === 'en') {
        $rows = array_values(array_filter($rows, static fn ($r) => bilingual_status($r) === 'ok'));
    }
    return $rows;
}

/**
 * Retourne une publication par slug. $lang='en' : cherche d'abord slug_en,
 * et retourne null si la publication n'a pas de version anglaise complète.
 */
function news_by_slug(string $slug, bool $onlyPublished = true, string $lang = 'fr'): ?array
{
    $sql = 'SELECT * FROM news_items WHERE (slug = :slug OR slug_en = :slug)';
    if ($onlyPublished) {
        $sql .= ' AND status = \'published\'';
    }
    $sql .= ' ORDER BY (slug = :slug) DESC LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch();
    if ($row === false) {
        return null;
    }
    if ($lang === 'en' && bilingual_status($row) !== 'ok') {
        return null; // pas de version anglaise : jamais de FR dans la page EN
    }
    return $row;
}

/** Retourne une publication par identifiant (administration). */
function news_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM news_items WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/** Commentaires approuvés d'une publication. @return array<int,array<string,mixed>> */
function news_comments_approved(int $newsId): array
{
    $stmt = db()->prepare(
        'SELECT id, author_name, body, created_at FROM news_comments
         WHERE news_id = :id AND status = \'approved\' ORDER BY created_at DESC'
    );
    $stmt->execute([':id' => $newsId]);
    return $stmt->fetchAll();
}

/** Nombre de commentaires en attente (badge admin). */
function news_comments_pending_count(): int
{
    try {
        return (int) db()->query('SELECT COUNT(*) FROM news_comments WHERE status = \'pending\'')->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}
