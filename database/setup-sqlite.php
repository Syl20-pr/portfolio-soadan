<?php
/**
 * =============================================================================
 *  Portfolio SKS — Initialisation SQLite (développement local sans MySQL)
 * =============================================================================
 *  Ce script crée la base SQLite database/portfolio_sks.sqlite avec TOUTES les
 *  tables du schéma MySQL (schema.sql + migration-001 + migration-002) et
 *  insère les données initiales (catégories, settings, cv_sections, etc.).
 *
 *  Usage : php portfolio-soadan/database/setup-sqlite.php
 * =============================================================================
 */

declare(strict_types=1);

$dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'portfolio_sks.sqlite';

echo "=== Initialisation SQLite : portfolio_sks.sqlite ===\n";
echo "Chemin : $dbPath\n\n";

try {
    $pdo = new PDO('sqlite:' . $dbPath, '', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA foreign_keys=ON');
    echo "✔ Connexion SQLite établie.\n";
} catch (Exception $e) {
    echo "✘ Erreur connexion : " . $e->getMessage() . "\n";
    exit(1);
}

// =============================================================================
//  SCHÉMA — toutes les tables
// =============================================================================

$tables = [

    // -------------------------------------------------------------------------
    //  schema.sql
    // -------------------------------------------------------------------------
    'messages' => "
        CREATE TABLE IF NOT EXISTS messages (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT    NOT NULL,
            email       TEXT    NOT NULL,
            phone       TEXT    DEFAULT NULL,
            subject     TEXT    DEFAULT NULL,
            message     TEXT    NOT NULL,
            language    TEXT    NOT NULL DEFAULT 'fr',
            collab_type TEXT    DEFAULT NULL,
            ip_hash     TEXT    DEFAULT NULL,
            user_agent  TEXT    DEFAULT NULL,
            status      TEXT    NOT NULL DEFAULT 'new',
            created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'contact_rate_limit' => "
        CREATE TABLE IF NOT EXISTS contact_rate_limit (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_hash    TEXT    NOT NULL,
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'login_attempts' => "
        CREATE TABLE IF NOT EXISTS login_attempts (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_hash    TEXT    NOT NULL,
            successful INTEGER NOT NULL DEFAULT 0,
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",

    // -------------------------------------------------------------------------
    //  migration-001-content.sql
    // -------------------------------------------------------------------------
    'admin_users' => "
        CREATE TABLE IF NOT EXISTS admin_users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            email         TEXT    NOT NULL UNIQUE,
            password_hash TEXT    NOT NULL,
            name          TEXT    NOT NULL DEFAULT '',
            is_active     INTEGER NOT NULL DEFAULT 1,
            created_at    TEXT    NOT NULL DEFAULT (datetime('now')),
            updated_at    TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'categories' => "
        CREATE TABLE IF NOT EXISTS categories (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            slug       TEXT    NOT NULL UNIQUE,
            name_fr    TEXT    NOT NULL,
            name_en    TEXT    NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'articles' => "
        CREATE TABLE IF NOT EXISTS articles (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            slug            TEXT    NOT NULL UNIQUE,
            lang            TEXT    NOT NULL DEFAULT 'fr',
            title           TEXT    NOT NULL,
            excerpt         TEXT    NOT NULL DEFAULT '',
            content_html    TEXT    NOT NULL DEFAULT '',
            cover_image     TEXT    DEFAULT NULL,
            category_id     INTEGER DEFAULT NULL REFERENCES categories(id) ON DELETE SET NULL,
            status          TEXT    NOT NULL DEFAULT 'draft',
            published_at    TEXT    DEFAULT NULL,
            source_page     TEXT    DEFAULT NULL,
            seo_title       TEXT    DEFAULT NULL,
            seo_description TEXT    DEFAULT NULL,
            views           INTEGER NOT NULL DEFAULT 0,
            created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
            updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'tags' => "
        CREATE TABLE IF NOT EXISTS tags (
            id   INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT    NOT NULL UNIQUE,
            name TEXT    NOT NULL
        )
    ",
    'article_tags' => "
        CREATE TABLE IF NOT EXISTS article_tags (
            article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
            tag_id     INTEGER NOT NULL REFERENCES tags(id)     ON DELETE CASCADE,
            PRIMARY KEY (article_id, tag_id)
        )
    ",
    'projects' => "
        CREATE TABLE IF NOT EXISTS projects (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            slug             TEXT    NOT NULL UNIQUE,
            title            TEXT    NOT NULL,
            role             TEXT    DEFAULT NULL,
            summary          TEXT    NOT NULL DEFAULT '',
            description_html TEXT    NOT NULL DEFAULT '',
            objectives_html  TEXT    NOT NULL DEFAULT '',
            results_html     TEXT    NOT NULL DEFAULT '',
            partners         TEXT    NOT NULL DEFAULT '',
            image            TEXT    DEFAULT NULL,
            external_url     TEXT    DEFAULT NULL,
            date_start       TEXT    DEFAULT NULL,
            date_end         TEXT    DEFAULT NULL,
            status           TEXT    NOT NULL DEFAULT 'draft',
            sort_order       INTEGER NOT NULL DEFAULT 0,
            created_at       TEXT    NOT NULL DEFAULT (datetime('now')),
            updated_at       TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'gallery_photos' => "
        CREATE TABLE IF NOT EXISTS gallery_photos (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            filename    TEXT    NOT NULL,
            caption     TEXT    NOT NULL DEFAULT '',
            alt_text    TEXT    NOT NULL DEFAULT '',
            category    TEXT    NOT NULL DEFAULT 'conferences',
            location    TEXT    NOT NULL DEFAULT '',
            taken_on    TEXT    DEFAULT NULL,
            approved    INTEGER NOT NULL DEFAULT 0,
            sort_order  INTEGER NOT NULL DEFAULT 0,
            uploaded_by INTEGER DEFAULT NULL REFERENCES admin_users(id) ON DELETE SET NULL,
            created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'cv_documents' => "
        CREATE TABLE IF NOT EXISTS cv_documents (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            lang          TEXT    NOT NULL,
            filename      TEXT    NOT NULL,
            original_name TEXT    NOT NULL DEFAULT '',
            version       INTEGER NOT NULL DEFAULT 1,
            is_active     INTEGER NOT NULL DEFAULT 0,
            uploaded_by   INTEGER DEFAULT NULL REFERENCES admin_users(id) ON DELETE SET NULL,
            created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'experiences' => "
        CREATE TABLE IF NOT EXISTS experiences (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            org_fr     TEXT    NOT NULL,
            org_en     TEXT    NOT NULL DEFAULT '',
            role_fr    TEXT    NOT NULL,
            role_en    TEXT    NOT NULL DEFAULT '',
            desc_fr    TEXT    NOT NULL DEFAULT '',
            desc_en    TEXT    NOT NULL DEFAULT '',
            location   TEXT    NOT NULL DEFAULT '',
            date_start TEXT    DEFAULT NULL,
            date_end   TEXT    DEFAULT NULL,
            is_current INTEGER NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'education' => "
        CREATE TABLE IF NOT EXISTS education (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            school_fr  TEXT    NOT NULL,
            school_en  TEXT    NOT NULL DEFAULT '',
            degree_fr  TEXT    NOT NULL,
            degree_en  TEXT    NOT NULL DEFAULT '',
            desc_fr    TEXT    NOT NULL DEFAULT '',
            desc_en    TEXT    NOT NULL DEFAULT '',
            started    INTEGER DEFAULT NULL,
            ended      INTEGER DEFAULT NULL,
            is_current INTEGER NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'skills' => "
        CREATE TABLE IF NOT EXISTS skills (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT    NOT NULL,
            name_en    TEXT    NOT NULL DEFAULT '',
            group_name TEXT    NOT NULL DEFAULT 'expertise',
            level      TEXT    NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'certifications' => "
        CREATE TABLE IF NOT EXISTS certifications (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name_fr    TEXT    NOT NULL,
            name_en    TEXT    NOT NULL DEFAULT '',
            issuer     TEXT    NOT NULL DEFAULT '',
            year       INTEGER DEFAULT NULL,
            url        TEXT    DEFAULT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'distinctions' => "
        CREATE TABLE IF NOT EXISTS distinctions (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            title_fr   TEXT    NOT NULL,
            title_en   TEXT    NOT NULL DEFAULT '',
            issuer     TEXT    NOT NULL DEFAULT '',
            year       INTEGER DEFAULT NULL,
            desc_fr    TEXT    NOT NULL DEFAULT '',
            desc_en    TEXT    NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'site_settings' => "
        CREATE TABLE IF NOT EXISTS site_settings (
            setting_key   TEXT NOT NULL PRIMARY KEY,
            setting_value TEXT NOT NULL DEFAULT '',
            updated_at    TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ",

    // -------------------------------------------------------------------------
    //  migration-002-cv-dynamique.sql
    // -------------------------------------------------------------------------
    'cv_photo' => "
        CREATE TABLE IF NOT EXISTS cv_photo (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            filename      TEXT    NOT NULL,
            original_name TEXT    NOT NULL DEFAULT '',
            crop_x        REAL    NOT NULL DEFAULT 50.0,
            crop_y        REAL    NOT NULL DEFAULT 50.0,
            crop_zoom     REAL    NOT NULL DEFAULT 100.0,
            uploaded_at   TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'cv_languages' => "
        CREATE TABLE IF NOT EXISTS cv_languages (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT    NOT NULL,
            level_fr   TEXT    NOT NULL DEFAULT '',
            level_en   TEXT    NOT NULL DEFAULT '',
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'cv_associations' => "
        CREATE TABLE IF NOT EXISTS cv_associations (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            org_fr     TEXT    NOT NULL,
            org_en     TEXT    NOT NULL DEFAULT '',
            role_fr    TEXT    NOT NULL DEFAULT '',
            role_en    TEXT    NOT NULL DEFAULT '',
            desc_fr    TEXT    NOT NULL DEFAULT '',
            desc_en    TEXT    NOT NULL DEFAULT '',
            date_start TEXT    DEFAULT NULL,
            date_end   TEXT    DEFAULT NULL,
            is_current INTEGER NOT NULL DEFAULT 0,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'cv_publications' => "
        CREATE TABLE IF NOT EXISTS cv_publications (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            title_fr   TEXT    NOT NULL,
            title_en   TEXT    NOT NULL DEFAULT '',
            venue_fr   TEXT    NOT NULL DEFAULT '',
            venue_en   TEXT    NOT NULL DEFAULT '',
            year       INTEGER DEFAULT NULL,
            url        TEXT    DEFAULT NULL,
            article_id INTEGER DEFAULT NULL REFERENCES articles(id) ON DELETE SET NULL,
            sort_order INTEGER NOT NULL DEFAULT 0
        )
    ",
    'cv_sections' => "
        CREATE TABLE IF NOT EXISTS cv_sections (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            section_key TEXT    NOT NULL,
            lang        TEXT    NOT NULL,
            title       TEXT    NOT NULL DEFAULT '',
            visible     INTEGER NOT NULL DEFAULT 1,
            sort_order  INTEGER NOT NULL DEFAULT 0,
            UNIQUE (section_key, lang)
        )
    ",
    'cv_state' => "
        CREATE TABLE IF NOT EXISTS cv_state (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            lang           TEXT    NOT NULL UNIQUE,
            template       TEXT    NOT NULL DEFAULT 'diplomatic',
            status         TEXT    NOT NULL DEFAULT 'draft',
            published_at   TEXT    DEFAULT NULL,
            published_file TEXT    DEFAULT NULL,
            draft_file     TEXT    DEFAULT NULL,
            updated_at     TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'cv_personal' => "
        CREATE TABLE IF NOT EXISTS cv_personal (
            lang        TEXT NOT NULL PRIMARY KEY,
            full_name   TEXT NOT NULL DEFAULT '',
            headline    TEXT NOT NULL DEFAULT '',
            summary     TEXT NOT NULL DEFAULT '',
            email       TEXT NOT NULL DEFAULT '',
            phone       TEXT NOT NULL DEFAULT '',
            location    TEXT NOT NULL DEFAULT '',
            website     TEXT NOT NULL DEFAULT '',
            linkedin    TEXT NOT NULL DEFAULT '',
            nationality TEXT NOT NULL DEFAULT '',
            updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ",
    // -------------------------------------------------------------------------
    //  migration-004-multimedia.sql
    // -------------------------------------------------------------------------
    'media_images' => "
        CREATE TABLE IF NOT EXISTS media_images (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_type TEXT    NOT NULL CHECK (parent_type IN ('article','project')),
            parent_id   INTEGER NOT NULL,
            filename    TEXT    NOT NULL,
            caption_fr  TEXT    NOT NULL DEFAULT '',
            caption_en  TEXT    NOT NULL DEFAULT '',
            alt_fr      TEXT    NOT NULL DEFAULT '',
            alt_en      TEXT    NOT NULL DEFAULT '',
            sort_order  INTEGER NOT NULL DEFAULT 0,
            created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'news_items' => "
        CREATE TABLE IF NOT EXISTS news_items (
            id               INTEGER PRIMARY KEY AUTOINCREMENT,
            slug             TEXT    NOT NULL UNIQUE,
            lang             TEXT    NOT NULL DEFAULT 'fr',
            title            TEXT    NOT NULL,
            excerpt          TEXT    NOT NULL DEFAULT '',
            body_html        TEXT    NOT NULL DEFAULT '',
            type             TEXT    NOT NULL DEFAULT 'video' CHECK (type IN ('video','clip','podcast')),
            category         TEXT    NOT NULL DEFAULT 'conference',
            source_kind      TEXT    NOT NULL DEFAULT 'embed' CHECK (source_kind IN ('embed','file')),
            embed_url        TEXT,
            media_file       TEXT,
            media_mime       TEXT,
            poster_image     TEXT,
            event_date       TEXT,
            location         TEXT    NOT NULL DEFAULT '',
            status           TEXT    NOT NULL DEFAULT 'draft',
            comments_enabled INTEGER NOT NULL DEFAULT 0,
            published_at     TEXT,
            seo_title        TEXT,
            seo_description  TEXT,
            created_at       TEXT    NOT NULL DEFAULT (datetime('now')),
            updated_at       TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'news_comments' => "
        CREATE TABLE IF NOT EXISTS news_comments (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            news_id      INTEGER NOT NULL REFERENCES news_items(id) ON DELETE CASCADE,
            author_name  TEXT    NOT NULL,
            author_email TEXT    NOT NULL DEFAULT '',
            body         TEXT    NOT NULL,
            status       TEXT    NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
            ip_hash      TEXT,
            user_agent   TEXT,
            created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
    'comment_rate_limit' => "
        CREATE TABLE IF NOT EXISTS comment_rate_limit (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_hash    TEXT    NOT NULL,
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ",
];

// Créer les tables
foreach ($tables as $name => $sql) {
    $pdo->exec($sql);
    echo "  ✔ Table $name\n";
}

// Mise à niveau sans perte pour les bases SQLite créées avant les traductions manuelles.
$skillColumns = $pdo->query('PRAGMA table_info(skills)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('name_en', $skillColumns, true)) {
    $pdo->exec("ALTER TABLE skills ADD COLUMN name_en TEXT NOT NULL DEFAULT ''");
    echo "  ✔ Colonne skills.name_en ajoutée\n";
}

echo "\n";

// =============================================================================
//  DONNÉES INITIALES
// =============================================================================

$pdo->exec('BEGIN');

// Catégories
$cats = [
    ['diplomatie-relations-internationales', 'Diplomatie et relations internationales',       'Diplomacy and International Relations',    1],
    ['gouvernance-numerique-ia',             'Gouvernance numérique et intelligence artificielle', 'Digital Governance and AI',           2],
    ['cybersecurite-transformation-digitale','Cybersécurité et transformation digitale',      'Cybersecurity and Digital Transformation', 3],
    ['leadership-engagement-citoyen',        'Leadership et engagement citoyen',              'Leadership and Civic Engagement',          4],
    ['droit-institutions-politiques-publiques','Droit, institutions et politiques publiques', 'Law, Institutions and Public Policy',      5],
];
$stmtCat = $pdo->prepare(
    'INSERT OR IGNORE INTO categories (slug, name_fr, name_en, sort_order) VALUES (?,?,?,?)'
);
foreach ($cats as $c) {
    $stmtCat->execute($c);
}
echo "✔ Catégories insérées.\n";

// Récupère l'id de la catégorie gouvernance
$catId = (int) $pdo->query(
    "SELECT id FROM categories WHERE slug='gouvernance-numerique-ia' LIMIT 1"
)->fetchColumn();

// Articles (policy briefs existants)
$stmtArt = $pdo->prepare(
    "INSERT OR IGNORE INTO articles
        (slug, lang, title, excerpt, content_html, status, published_at, source_page, category_id)
     VALUES (?,?,?,?,?,?,?,?,?)"
);
$stmtArt->execute([
    'gouvernance-ia-afrique-souverainete-strategique', 'fr',
    'Gouvernance de l\'IA en Afrique : de la dépendance technologique à la souveraineté stratégique',
    'Comment les États africains peuvent-ils encadrer les algorithmes critiques et préserver leurs patrimoines de données.',
    '', 'published', '2026-10-01 00:00:00', 'publications/policy-brief-01.php', $catId,
]);
$stmtArt->execute([
    'policy-brief-02', 'fr', 'Policy Brief n° 02 — SOADAN Koffi Sylvain',
    '', '', 'published', date('Y-m-d H:i:s'), 'publications/policy-brief-02.php', $catId,
]);
echo "✔ Articles initiaux insérés.\n";

// Site settings
$settings = [
    ['site_name',        'SOADAN Koffi Sylvain — Portfolio'],
    ['contact_email',    'sylvainsoadan3@gmail.com'],
    ['contact_location', 'Lomé, Togo — Afrique de l\'Ouest'],
    ['contact_linkedin', ''],
    ['contact_x',        ''],
    ['bio_fr',           ''],
    ['bio_en',           ''],
];
$stmtSet = $pdo->prepare(
    'INSERT OR IGNORE INTO site_settings (setting_key, setting_value) VALUES (?,?)'
);
foreach ($settings as $s) {
    $stmtSet->execute($s);
}
echo "✔ Paramètres du site insérés.\n";

// cv_sections
$sections = [
    ['summary',        'fr', 1, 1], ['summary',        'en', 1, 1],
    ['experience',     'fr', 1, 2], ['experience',     'en', 1, 2],
    ['education',      'fr', 1, 3], ['education',      'en', 1, 3],
    ['skills',         'fr', 1, 4], ['skills',         'en', 1, 4],
    ['languages',      'fr', 1, 5], ['languages',      'en', 1, 5],
    ['certifications', 'fr', 1, 6], ['certifications', 'en', 1, 6],
    ['publications',   'fr', 1, 7], ['publications',   'en', 1, 7],
    ['associations',   'fr', 1, 8], ['associations',   'en', 1, 8],
    ['distinctions',   'fr', 1, 9], ['distinctions',   'en', 1, 9],
];
$stmtSec = $pdo->prepare(
    'INSERT OR IGNORE INTO cv_sections (section_key, lang, visible, sort_order) VALUES (?,?,?,?)'
);
foreach ($sections as $s) {
    $stmtSec->execute($s);
}
echo "✔ Sections CV insérées.\n";

// cv_personal
$pdo->exec("INSERT OR IGNORE INTO cv_personal (lang, full_name) VALUES ('fr',''),('en','')");
echo "✔ cv_personal initialisé.\n";

// cv_state
$pdo->exec(
    "INSERT OR IGNORE INTO cv_state (lang, template, status) VALUES
        ('fr','diplomatic','draft'),
        ('en','diplomatic','draft')"
);
echo "✔ cv_state initialisé.\n";

$pdo->exec('COMMIT');

echo "\n✅ Base SQLite créée avec succès !\n";
echo "   Fichier : $dbPath\n";

// ============================================================================
//  Migration 004 — colonnes gallery_photos + réglages du bandeau (idempotent)
// ============================================================================
$gCols = $pdo->query('PRAGMA table_info(gallery_photos)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('show_home', $gCols, true)) {
    $pdo->exec("ALTER TABLE gallery_photos ADD COLUMN show_home INTEGER NOT NULL DEFAULT 0");
    echo "  ✔ Colonne gallery_photos.show_home ajoutée\n";
}
if (!in_array('caption_en', $gCols, true)) {
    $pdo->exec("ALTER TABLE gallery_photos ADD COLUMN caption_en TEXT NOT NULL DEFAULT ''");
    echo "  ✔ Colonne gallery_photos.caption_en ajoutée\n";
}
if (!in_array('banner_label', $gCols, true)) {
    $pdo->exec("ALTER TABLE gallery_photos ADD COLUMN banner_label TEXT NOT NULL DEFAULT ''");
    echo "  ✔ Colonne gallery_photos.banner_label ajoutée\n";
}
$pdo->exec('CREATE INDEX IF NOT EXISTS idx_gallery_home ON gallery_photos (show_home, approved, sort_order)');

$bannerSettings = [
    ['banner_enabled', '1'],
    ['banner_show_flags', '1'],
    ['banner_flags_mode', 'all'],
    ['banner_flags_codes', ''],
    ['banner_speed_seconds', '40'],
    ['banner_two_rows', '1'],
    ['news_max_video_mb', '64'],
    ['news_max_audio_mb', '24'],
];
foreach ($bannerSettings as $bs) {
    $stmtSet->execute($bs);
}
echo "✔ Réglages du bandeau multimédia insérés.\n";

// ============================================================================
//  Migration 003 — bilinguisme côte à côte (idempotent)
// ============================================================================
function sqlite_add_col(PDO $pdo, string $table, string $col, string $def): void
{
    $cols = $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array($col, $cols, true)) {
        $pdo->exec("ALTER TABLE $table ADD COLUMN $col $def");
        echo "  ✔ $table.$col ajouté\n";
    }
}

sqlite_add_col($pdo, 'articles', 'slug_en', 'TEXT');
sqlite_add_col($pdo, 'articles', 'title_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'articles', 'excerpt_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'articles', 'content_html_en', 'TEXT');
sqlite_add_col($pdo, 'articles', 'seo_title_en', 'TEXT');
sqlite_add_col($pdo, 'articles', 'seo_description_en', 'TEXT');

sqlite_add_col($pdo, 'projects', 'title_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'projects', 'role_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'projects', 'summary_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'projects', 'description_en', 'TEXT');
sqlite_add_col($pdo, 'projects', 'objectives_en', 'TEXT');
sqlite_add_col($pdo, 'projects', 'results_en', 'TEXT');
sqlite_add_col($pdo, 'projects', 'partners_en', "TEXT NOT NULL DEFAULT ''");

sqlite_add_col($pdo, 'gallery_photos', 'alt_fr', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'gallery_photos', 'alt_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'gallery_photos', 'banner_label_fr', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'gallery_photos', 'banner_label_en', "TEXT NOT NULL DEFAULT ''");

sqlite_add_col($pdo, 'news_items', 'slug_en', 'TEXT');
sqlite_add_col($pdo, 'news_items', 'title_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'news_items', 'excerpt_en', "TEXT NOT NULL DEFAULT ''");
sqlite_add_col($pdo, 'news_items', 'body_html_en', 'TEXT');
sqlite_add_col($pdo, 'news_items', 'location_en', "TEXT NOT NULL DEFAULT ''");
echo "✔ Migration 003 (bilinguisme) appliquée.\n";

