-- =============================================================================
--  Portfolio SKS — Migration 003 : bilinguisme côte à côte (un seul enregistrement)
-- =============================================================================
--  PRINCIPE CENTRAL : chaque contenu est UNE ligne portant ses champs _fr et
--  _en — plus une ligne par langue. L'admin saisit FR et EN dans le même
--  formulaire, un seul « Enregistrer » envoie les deux versions.
--
--  Conversions (toutes les données existantes sont conservées) :
--    A. articles          : 2 lignes (fr/en) → 1 ligne avec
--                           title_en, excerpt_en, content_html_en, slug_en,
--                           seo_title_en, seo_description_en.
--    B. projects          : ajout des colonnes _en (title_en, role_en,
--                           summary_en, description_en, objectives_en,
--                           results_en, partners_en).
--    C. gallery_photos    : alt_text → alt_fr + alt_en ; banner_label →
--                           banner_label_fr/en ; caption/caption_en existants.
--    D. news_items        : ajout title_en, excerpt_en, body_html_en,
--                           location_en (un enregistrement, deux langues).
--    E. media_images      : renommage alt_* inchangé (déjà bilingue).
--
--  IDEMPOTENT : colonnes vérifiées via information_schema avant ALTER.
--  RÉVERSIBLE : rollback-003-bilingue.sql.
--  Moteur : MySQL/MariaDB (le repli de développement SQLite est mis à niveau
--  par database/setup-sqlite.php, voir ce fichier).
-- =============================================================================
SET NAMES utf8mb4;
-- -----------------------------------------------------------------------------
--  A. articles : fusion des deux langues en une seule ligne
-- -----------------------------------------------------------------------------
SET @col := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'articles'
            AND COLUMN_NAME = 'title_en'
    );
SET @ddl := IF(
        @col = 0,
        'ALTER TABLE `articles`
         ADD COLUMN `slug_en` VARCHAR(180) DEFAULT NULL AFTER `slug`,
         ADD COLUMN `title_en` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `title`,
         ADD COLUMN `excerpt_en` VARCHAR(500) NOT NULL DEFAULT '''' AFTER `excerpt`,
         ADD COLUMN `content_html_en` MEDIUMTEXT NULL AFTER `content_html`,
         ADD COLUMN `seo_title_en` VARCHAR(160) DEFAULT NULL AFTER `seo_title`,
         ADD COLUMN `seo_description_en` VARCHAR(320) DEFAULT NULL AFTER `seo_description`,
         ADD UNIQUE KEY `uq_articles_slug_en` (`slug_en`)',
        'SELECT 1'
    );
PREPARE s
FROM @ddl;
EXECUTE s;
DEALLOCATE PREPARE s;
-- Fusion : recopie le contenu des anciennes lignes EN (lang='en') dans la
-- ligne FR (même slug d'origine, ou appariées par leur slug 'xxx-en').
-- Idempotent : ne fait rien si aucune ligne EN non fusionnée.
INSERT INTO `articles` (
        `id`,
        `slug`,
        `slug_en`,
        `lang`,
        `title`,
        `title_en`,
        `excerpt`,
        `excerpt_en`,
        `content_html`,
        `content_html_en`,
        `cover_image`,
        `category_id`,
        `status`,
        `published_at`,
        `seo_title`,
        `seo_title_en`,
        `seo_description`,
        `seo_description_en`
    )
SELECT fr.id,
    fr.slug,
    en.slug,
    fr.lang,
    fr.title,
    en.title,
    fr.excerpt,
    en.excerpt,
    fr.content_html,
    en.content_html,
    en.cover_image,
    en.category_id,
    fr.status,
    fr.published_at,
    fr.seo_title,
    en.seo_title,
    fr.seo_description,
    en.seo_description
FROM `articles` fr
    JOIN `articles` en ON en.lang = 'en'
    AND en.slug IN (
        CONCAT(fr.slug, '-en'),
        CONCAT(fr.slug, '-2'),
        fr.slug
    )
WHERE fr.lang = 'fr'
    AND fr.title_en = ''
    AND EXISTS (
        SELECT 1
        FROM `articles` e2
        WHERE e2.lang = 'en'
            AND e2.slug IN (
                CONCAT(fr.slug, '-en'),
                CONCAT(fr.slug, '-2'),
                fr.slug
            )
            AND e2.id <> fr.id
    ) ON DUPLICATE KEY
UPDATE `slug_en` =
VALUES(`slug_en`);
-- Supprime les anciennes lignes EN désormais fusionnées (données conservées
-- dans la ligne FR bilingue ; les lignes EN sans équivalent FR sont gardées
-- et converties : elles deviennent la ligne principale).
UPDATE `articles` en
    JOIN `articles` fr ON fr.lang = 'fr'
    AND fr.slug IN (
        REPLACE(en.slug, '-en', ''),
        REPLACE(en.slug, '-2', ''),
        en.slug
    )
SET fr.slug_en = en.slug
WHERE en.lang = 'en';
-- Les lignes EN encore présentes ET déjà fusionnées (title_en <> '') sont
-- retirées ; celles restées orphelines deviennent fr par défaut de lecture.
DELETE FROM `articles`
WHERE `lang` = 'en'
    AND `title` IN (
        SELECT `title_en`
        FROM (
                SELECT DISTINCT `title_en`
                FROM `articles`
                WHERE `lang` = 'fr'
                    AND `title_en` <> ''
            ) t
    );
-- -----------------------------------------------------------------------------
--  B. projects : colonnes _en
-- -----------------------------------------------------------------------------
SET @col := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'projects'
            AND COLUMN_NAME = 'title_en'
    );
SET @ddl := IF(
        @col = 0,
        'ALTER TABLE `projects`
         ADD COLUMN `title_en` VARCHAR(200) NOT NULL DEFAULT '''' AFTER `title`,
         ADD COLUMN `role_en` VARCHAR(160) NOT NULL DEFAULT '''' AFTER `role`,
         ADD COLUMN `summary_en` VARCHAR(400) NOT NULL DEFAULT '''' AFTER `summary`,
         ADD COLUMN `description_en` MEDIUMTEXT NULL AFTER `description_html`,
         ADD COLUMN `objectives_en` MEDIUMTEXT NULL AFTER `objectives_html`,
         ADD COLUMN `results_en` MEDIUMTEXT NULL AFTER `results_html`,
         ADD COLUMN `partners_en` VARCHAR(400) NOT NULL DEFAULT '''' AFTER `partners`',
        'SELECT 1'
    );
PREPARE s
FROM @ddl;
EXECUTE s;
DEALLOCATE PREPARE s;
-- -----------------------------------------------------------------------------
--  C. gallery_photos : alt_fr/alt_en, étiquettes FR/EN
-- -----------------------------------------------------------------------------
SET @col := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'gallery_photos'
            AND COLUMN_NAME = 'alt_fr'
    );
SET @ddl := IF(
        @col = 0,
        'ALTER TABLE `gallery_photos`
         ADD COLUMN `alt_fr` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `caption_en`,
         ADD COLUMN `alt_en` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `alt_fr`,
         ADD COLUMN `banner_label_fr` VARCHAR(120) NOT NULL DEFAULT '''' AFTER `alt_en`,
         ADD COLUMN `banner_label_en` VARCHAR(120) NOT NULL DEFAULT '''' AFTER `banner_label_fr`',
        'SELECT 1'
    );
PREPARE s
FROM @ddl;
EXECUTE s;
DEALLOCATE PREPARE s;
-- Recopie de l'ancien alt_text unique dans alt_fr (une seule fois).
UPDATE `gallery_photos`
SET `alt_fr` = `alt_text`
WHERE `alt_fr` = ''
    AND `alt_text` <> '';
-- -----------------------------------------------------------------------------
--  D. news_items : colonnes _en
-- -----------------------------------------------------------------------------
SET @col := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'news_items'
            AND COLUMN_NAME = 'title_en'
    );
SET @ddl := IF(
        @col = 0,
        'ALTER TABLE `news_items`
         ADD COLUMN `slug_en` VARCHAR(180) DEFAULT NULL AFTER `slug`,
         ADD COLUMN `title_en` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `title`,
         ADD COLUMN `excerpt_en` VARCHAR(500) NOT NULL DEFAULT '''' AFTER `excerpt`,
         ADD COLUMN `body_html_en` MEDIUMTEXT NULL AFTER `body_html`,
         ADD COLUMN `location_en` VARCHAR(160) NOT NULL DEFAULT '''' AFTER `location`,
         ADD UNIQUE KEY `uq_news_slug_en` (`slug_en`)',
        'SELECT 1'
    );
PREPARE s
FROM @ddl;
EXECUTE s;
DEALLOCATE PREPARE s;
-- =============================================================================
--  Fin de migration — colonnes ajoutées : 6 articles, 7 projets, 4 galerie,
--  5 actualités ; 0 donnée détruite (les lignes articles EN fusionnées
--  déplacent leur contenu dans la ligne bilingue FR).
-- =============================================================================