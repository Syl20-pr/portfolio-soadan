-- =============================================================================
--  Portfolio SOADAN Koffi Sylvain (SKS) — Migration 001 : modules de contenu
-- =============================================================================
--  PRINCIPES :
--    * ADDITIF UNIQUEMENT : aucune table existante n'est modifiée ni supprimée.
--    * Idempotent : réexécuter ce script ne produit aucune erreur
--      (CREATE TABLE IF NOT EXISTS partout).
--    * Réversible via database/rollback-001.sql.
--
--  Import : phpMyAdmin (Hostinger) ou :
--      mysql -u portfolio_user -p portfolio_sks < database/migration-001-content.sql
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- -----------------------------------------------------------------------------
--  Table : admin_users
--  Comptes administrateurs (mots de passe hashés via password_hash / bcrypt).
--  Le compte initial reste configurable via .env (ADMIN_EMAIL/HASH), la table
--  prend le relais dès qu'un compte y est créé depuis l'espace d'administration.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `email`         VARCHAR(254)     NOT NULL,
    `password_hash` VARCHAR(255)     NOT NULL,
    `name`          VARCHAR(120)     NOT NULL DEFAULT '',
    `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comptes administrateurs du portfolio.';

-- -----------------------------------------------------------------------------
--  Table : categories
--  Catégories éditoriales (bilingues).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`       VARCHAR(80)  NOT NULL,
    `name_fr`    VARCHAR(120) NOT NULL,
    `name_en`    VARCHAR(120) NOT NULL,
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catégories éditoriales des publications.';

-- -----------------------------------------------------------------------------
--  Table : articles
--  Publications dynamiques. Les deux policy briefs existants restent servis
--  par leurs pages statiques ; ils figurent ici comme lignes « imported ».
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`             VARCHAR(160)    NOT NULL,
    `lang`             ENUM('fr','en') NOT NULL DEFAULT 'fr',
    `title`            VARCHAR(255)    NOT NULL,
    `excerpt`          VARCHAR(500)    NOT NULL DEFAULT '',
    `content_html`     MEDIUMTEXT      NOT NULL,
    `cover_image`      VARCHAR(255)    DEFAULT NULL,
    `category_id`      INT UNSIGNED    DEFAULT NULL,
    `status`           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `published_at`     DATETIME        DEFAULT NULL,
    `source_page`      VARCHAR(160)    DEFAULT NULL
        COMMENT 'Chemin de la page statique d’origine si la publication a été importée.',
    `seo_title`        VARCHAR(160)    DEFAULT NULL,
    `seo_description`  VARCHAR(320)    DEFAULT NULL,
    `views`            INT UNSIGNED    NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_article_slug` (`slug`),
    KEY `idx_articles_status`  (`status`, `published_at`),
    KEY `idx_articles_category`(`category_id`),
    CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Publications (articles) du portfolio.';

-- -----------------------------------------------------------------------------
--  Tables : tags / article_tags (relation N:M)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
    `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(80)  NOT NULL,
    `name` VARCHAR(80)  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mots-clés des publications.';

CREATE TABLE IF NOT EXISTS `article_tags` (
    `article_id` BIGINT UNSIGNED NOT NULL,
    `tag_id`     INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`article_id`, `tag_id`),
    KEY `idx_at_tag` (`tag_id`),
    CONSTRAINT `fk_at_article` FOREIGN KEY (`article_id`)
        REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_at_tag` FOREIGN KEY (`tag_id`)
        REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Association articles <-> tags.';

-- -----------------------------------------------------------------------------
--  Table : projects
--  Projets dynamiques. Les 6 fiches statiques existantes restent servies par
--  projects/*.php ; les nouveaux projets sont gérés ici.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`           VARCHAR(160)    NOT NULL,
    `title`          VARCHAR(200)    NOT NULL,
    `role`           VARCHAR(160)    DEFAULT NULL,
    `summary`        VARCHAR(400)    NOT NULL DEFAULT '',
    `description_html` TEXT          NOT NULL,
    `objectives_html`  TEXT          NOT NULL DEFAULT '',
    `results_html`     TEXT          NOT NULL DEFAULT '',
    `partners`       VARCHAR(400)    NOT NULL DEFAULT '',
    `image`          VARCHAR(255)    DEFAULT NULL,
    `external_url`   VARCHAR(400)    DEFAULT NULL,
    `date_start`     DATE            DEFAULT NULL,
    `date_end`       DATE            DEFAULT NULL,
    `status`         ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `sort_order`     SMALLINT        NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_project_slug` (`slug`),
    KEY `idx_projects_status` (`status`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Projets dynamiques du portfolio.';

-- -----------------------------------------------------------------------------
--  Table : gallery_photos
--  Galerie photographique administrative. Une photo n'est visible sur le site
--  public qu'une fois approuvée (approved = 1).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_photos` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename`     VARCHAR(160)    NOT NULL,
    `caption`      VARCHAR(255)    NOT NULL DEFAULT '',
    `alt_text`     VARCHAR(255)    NOT NULL DEFAULT '',
    `category`     VARCHAR(80)     NOT NULL DEFAULT 'conferences'
        COMMENT 'conferences|diplomatic|association|training|projects|speeches',
    `location`     VARCHAR(160)    NOT NULL DEFAULT '',
    `taken_on`     DATE            DEFAULT NULL,
    `approved`     TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order`   SMALLINT        NOT NULL DEFAULT 0,
    `uploaded_by`  INT UNSIGNED    DEFAULT NULL,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gallery_approved` (`approved`, `category`, `sort_order`),
    CONSTRAINT `fk_gallery_uploader` FOREIGN KEY (`uploaded_by`)
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Galerie photographique professionnelle.';

-- -----------------------------------------------------------------------------
--  Table : cv_documents
--  CV français et anglais téléversés + historique des versions.
--  is_active = 1 désigne la version actuellement publiée pour une langue.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_documents` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lang`          ENUM('fr','en') NOT NULL,
    `filename`      VARCHAR(160)    NOT NULL,
    `original_name` VARCHAR(160)    NOT NULL DEFAULT '',
    `version`       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 0,
    `uploaded_by`   INT UNSIGNED    DEFAULT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cv_active` (`lang`, `is_active`),
    CONSTRAINT `fk_cv_uploader` FOREIGN KEY (`uploaded_by`)
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Documents CV (versions successives FR/EN).';

-- -----------------------------------------------------------------------------
--  Tables : profil / parcours (contenus bilingues administrables)
--  experience, education, skills, certifications, languages, distinctions
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `experiences` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `org_fr`     VARCHAR(200)    NOT NULL,
    `org_en`     VARCHAR(200)    NOT NULL DEFAULT '',
    `role_fr`    VARCHAR(200)    NOT NULL,
    `role_en`    VARCHAR(200)    NOT NULL DEFAULT '',
    `desc_fr`    TEXT            NOT NULL DEFAULT '',
    `desc_en`    TEXT            NOT NULL DEFAULT '',
    `location`   VARCHAR(160)    NOT NULL DEFAULT '',
    `date_start` DATE            DEFAULT NULL,
    `date_end`   DATE            DEFAULT NULL COMMENT 'NULL = en cours',
    `is_current` TINYINT(1)      NOT NULL DEFAULT 0,
    `sort_order` SMALLINT        NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_exp_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Expériences professionnelles.';

CREATE TABLE IF NOT EXISTS `education` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_fr`  VARCHAR(200) NOT NULL,
    `school_en`  VARCHAR(200) NOT NULL DEFAULT '',
    `degree_fr`  VARCHAR(200) NOT NULL,
    `degree_en`  VARCHAR(200) NOT NULL DEFAULT '',
    `desc_fr`    TEXT         NOT NULL DEFAULT '',
    `desc_en`    TEXT         NOT NULL DEFAULT '',
    `started`    YEAR         DEFAULT NULL,
    `ended`      YEAR         DEFAULT NULL COMMENT 'NULL = en cours',
    `is_current` TINYINT(1)   NOT NULL DEFAULT 0,
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_edu_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Formations.';

CREATE TABLE IF NOT EXISTS `skills` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `group_name` VARCHAR(80)  NOT NULL DEFAULT 'expertise'
        COMMENT 'expertise|technical|languages_soft',
    `level`      VARCHAR(40)  NOT NULL DEFAULT '',
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_skills_sort` (`group_name`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Compétences et expertises.';

CREATE TABLE IF NOT EXISTS `certifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_fr`    VARCHAR(200) NOT NULL,
    `name_en`    VARCHAR(200) NOT NULL DEFAULT '',
    `issuer`     VARCHAR(160) NOT NULL DEFAULT '',
    `year`       YEAR         DEFAULT NULL,
    `url`        VARCHAR(400) DEFAULT NULL,
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_cert_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Certifications et formations complémentaires.';

CREATE TABLE IF NOT EXISTS `distinctions` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title_fr`   VARCHAR(200) NOT NULL,
    `title_en`   VARCHAR(200) NOT NULL DEFAULT '',
    `issuer`     VARCHAR(160) NOT NULL DEFAULT '',
    `year`       YEAR         DEFAULT NULL,
    `desc_fr`    VARCHAR(400) NOT NULL DEFAULT '',
    `desc_en`    VARCHAR(400) NOT NULL DEFAULT '',
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_dist_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Distinctions et récompenses.';

-- -----------------------------------------------------------------------------
--  Table : site_settings
--  Paramètres généraux, coordonnées, textes de présentation (clé/valeur).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
    `setting_key`   VARCHAR(80)  NOT NULL,
    `setting_value` TEXT         NOT NULL,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Paramètres du site (clé/valeur).';

-- =============================================================================
--  DONNÉES INITIALES
-- =============================================================================

-- Catégories éditoriales officielles (identiques dans les deux langues).
INSERT INTO `categories` (`slug`, `name_fr`, `name_en`, `sort_order`) VALUES
    ('diplomatie-relations-internationales', 'Diplomatie et relations internationales',      'Diplomacy and International Relations', 1),
    ('gouvernance-numerique-ia',             'Gouvernance numérique et intelligence artificielle', 'Digital Governance and AI',       2),
    ('cybersecurite-transformation-digitale','Cybersécurité et transformation digitale',     'Cybersecurity and Digital Transformation', 3),
    ('leadership-engagement-citoyen',        'Leadership et engagement citoyen',             'Leadership and Civic Engagement', 4),
    ('droit-institutions-politiques-publiques','Droit, institutions et politiques publiques','Law, Institutions and Public Policy', 5)
ON DUPLICATE KEY UPDATE `name_fr` = VALUES(`name_fr`), `name_en` = VALUES(`name_en`);

-- Les deux publications existantes sont référencées dans la base
-- (elles continuent d'être servies par leurs pages statiques d'origine ;
-- source_page mémorise leur chemin pour la continuité des URL).
INSERT INTO `articles` (`slug`, `lang`, `title`, `excerpt`, `content_html`, `status`, `published_at`, `source_page`, `category_id`)
SELECT 'gouvernance-ia-afrique-souverainete-strategique', 'fr',
       'Gouvernance de l''IA en Afrique : de la dépendance technologique à la souveraineté stratégique',
       'Comment les États africains peuvent-ils encadrer les algorithmes critiques et préserver leurs patrimoines de données.',
       '', 'published', '2026-10-01 00:00:00', 'publications/policy-brief-01.php', c.id
FROM `categories` c WHERE c.slug = 'gouvernance-numerique-ia'
ON DUPLICATE KEY UPDATE `source_page` = VALUES(`source_page`);

INSERT INTO `articles` (`slug`, `lang`, `title`, `excerpt`, `content_html`, `status`, `published_at`, `source_page`, `category_id`)
SELECT 'policy-brief-02', 'fr', 'Policy Brief n° 02 — SOADAN Koffi Sylvain', '', '', 'published', NOW(), 'publications/policy-brief-02.php', c.id
FROM `categories` c WHERE c.slug = 'gouvernance-numerique-ia'
ON DUPLICATE KEY UPDATE `source_page` = VALUES(`source_page`);

-- Paramètres par défaut (valeurs reprises du site existant).
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
    ('site_name',        'SOADAN Koffi Sylvain — Portfolio'),
    ('contact_email',    'sylvainsoadan3@gmail.com'),
    ('contact_location', 'Lomé, Togo — Afrique de l''Ouest'),
    ('contact_linkedin', ''),
    ('contact_x',        ''),
    ('bio_fr',           ''),
    ('bio_en',           '')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- =============================================================================
--  Fin de migration — 12 nouvelles tables, 0 table modifiée.
-- =============================================================================
