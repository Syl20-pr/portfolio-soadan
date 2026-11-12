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
 * Liste des articles publiés (ou tous si $onlyPublished = false).
 *
 * @return array<int,array<string,mixed>>
 */
function articles_list(bool $onlyPublished = true, int $limit = 100): array
{
    $sql = 'SELECT a.*, c.slug AS category_slug, c.name_fr AS category_fr, c.name_en AS category_en
            FROM articles a LEFT JOIN categories c ON c.id = a.category_id';
    $params = [];
    if ($onlyPublished) {
        $sql .= ' WHERE a.status = "published"';
    }
    $sql .= ' ORDER BY a.published_at DESC, a.id DESC LIMIT ' . max(1, $limit);
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Retourne un article par son slug (publié uniquement côté public). */
function article_by_slug(string $slug, bool $onlyPublished = true): ?array
{
    $sql = 'SELECT a.*, c.slug AS category_slug, c.name_fr AS category_fr, c.name_en AS category_en
            FROM articles a LEFT JOIN categories c ON c.id = a.category_id
            WHERE a.slug = :slug';
    if ($onlyPublished) {
        $sql .= ' AND a.status = "published"';
    }
    $sql .= ' LIMIT 1';
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
function article_save(PDO $pdo, array $data, array $tags): int
{
    if (!empty($data['id'])) {
        $pdo->prepare(
            'UPDATE articles SET
                slug = :slug, title = :title, excerpt = :excerpt, content_html = :content,
                cover_image = :cover, category_id = :category, status = :status,
                published_at = :published, seo_title = :seo_title, seo_description = :seo_desc,
                lang = :lang
             WHERE id = :id'
        )->execute([
            ':slug' => $data['slug'], ':title' => $data['title'], ':excerpt' => $data['excerpt'],
            ':content' => $data['content'], ':cover' => $data['cover_image'],
            ':category' => $data['category_id'], ':status' => $data['status'],
            ':published' => $data['published_at'], ':seo_title' => $data['seo_title'],
            ':seo_desc' => $data['seo_description'], ':lang' => $data['lang'], ':id' => (int) $data['id'],
        ]);
        $articleId = (int) $data['id'];
        $pdo->prepare('DELETE FROM article_tags WHERE article_id = :id')->execute([':id' => $articleId]);
    } else {
        $pdo->prepare(
            'INSERT INTO articles
                (slug, lang, title, excerpt, content_html, cover_image, category_id, status,
                 published_at, seo_title, seo_description)
             VALUES
                (:slug, :lang, :title, :excerpt, :content, :cover, :category, :status,
                 :published, :seo_title, :seo_desc)'
        )->execute([
            ':slug' => $data['slug'], ':lang' => $data['lang'], ':title' => $data['title'],
            ':excerpt' => $data['excerpt'], ':content' => $data['content'], ':cover' => $data['cover_image'],
            ':category' => $data['category_id'], ':status' => $data['status'],
            ':published' => $data['published_at'], ':seo_title' => $data['seo_title'],
            ':seo_desc' => $data['seo_description'],
        ]);
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

/** @return array<int,array<string,mixed>> */
function projects_list(bool $onlyPublished = true): array
{
    $sql = 'SELECT * FROM projects';
    if ($onlyPublished) {
        $sql .= ' WHERE status = "published"';
    }
    $sql .= ' ORDER BY sort_order, id DESC';
    return db()->query($sql)->fetchAll();
}

/** Retourne un projet (par id ou par slug). */
function project_get(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/** Insère ou met à jour un projet. @return int */
function project_save(PDO $pdo, array $data): int
{
    if (!empty($data['id'])) {
        $pdo->prepare(
            'UPDATE projects SET slug = :slug, title = :title, role = :role, summary = :summary,
                description_html = :descr, objectives_html = :obj, results_html = :res,
                partners = :partners, image = :image, external_url = :url,
                date_start = :ds, date_end = :de, status = :status, sort_order = :sort
             WHERE id = :id'
        )->execute([
            ':slug' => $data['slug'], ':title' => $data['title'], ':role' => $data['role'],
            ':summary' => $data['summary'], ':descr' => $data['description_html'],
            ':obj' => $data['objectives_html'], ':res' => $data['results_html'],
            ':partners' => $data['partners'], ':image' => $data['image'],
            ':url' => $data['external_url'], ':ds' => $data['date_start'], ':de' => $data['date_end'],
            ':status' => $data['status'], ':sort' => $data['sort_order'], ':id' => (int) $data['id'],
        ]);
        return (int) $data['id'];
    }
    $pdo->prepare(
        'INSERT INTO projects (slug, title, role, summary, description_html, objectives_html,
            results_html, partners, image, external_url, date_start, date_end, status, sort_order)
         VALUES (:slug, :title, :role, :summary, :descr, :obj, :res, :partners, :image,
            :url, :ds, :de, :status, :sort)'
    )->execute([
        ':slug' => $data['slug'], ':title' => $data['title'], ':role' => $data['role'],
        ':summary' => $data['summary'], ':descr' => $data['description_html'],
        ':obj' => $data['objectives_html'], ':res' => $data['results_html'],
        ':partners' => $data['partners'], ':image' => $data['image'], ':url' => $data['external_url'],
        ':ds' => $data['date_start'], ':de' => $data['date_end'],
        ':status' => $data['status'], ':sort' => $data['sort_order'],
    ]);
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
    $speed   = (int) setting('banner_speed_seconds', '40');
    $speed   = ($speed >= 10 && $speed <= 300) ? $speed : 40;

    $slides = [];
    if ($enabled) {
        try {
            $stmt = db()->prepare(
                'SELECT id, filename, caption, caption_en, alt_text, banner_label
                 FROM gallery_photos WHERE show_home = 1 AND approved = 1
                 ORDER BY sort_order, id DESC'
            );
            $stmt->execute();
            $slides = $stmt->fetchAll();
        } catch (Throwable $e) {
            log_event('error', 'banner_data: photos unavailable');
            $slides = [];
        }
    }

    // Drapeaux CHOISIS UNIQUEMENT (jamais tous par défaut) intercalés
    // toutes les 5 images environ, sans dépasser le nombre de photos.
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
        'flags_mode'    => 'selected',
        'flags_codes'   => $chosen,
        'speed_seconds' => $speed,
        'photos'        => [$mixed],
    ];
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
function news_list(bool $onlyPublished = true, ?string $category = null, int $limit = 60): array
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
    return $stmt->fetchAll();
}

/** Retourne une publication par slug. */
function news_by_slug(string $slug, bool $onlyPublished = true): ?array
{
    $sql = 'SELECT * FROM news_items WHERE slug = :slug';
    if ($onlyPublished) {
        $sql .= ' AND status = \'published\'';
    }
    $sql .= ' LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
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
