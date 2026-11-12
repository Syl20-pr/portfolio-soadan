-- =============================================================================
--  Portfolio SKS — Migration 004 : Vitrine multimédia
-- =============================================================================
--  Modules livrés :
--    A. Bandeau animé d'accueil  : options par photo (gallery_photos) +
--       réglages dans site_settings (aucune table parallèle).
--    B. Images supplémentaires   : table media_images partagée entre les
--       articles et les projets (galerie façon LinkedIn).
--    C. Rubrique « Actualités »  : news_items (vidéos, capsules, podcasts,
--       liens intégrés OU fichiers MP4/MP3) + news_comments modérés.
--    D. Limites d'upload audio/vidéo configurables : constantes .env, écrites
--       dans site_settings pour référence (documentation hébergeur incluse).
--
--  PRINCIPES :
--    * ADDITIF : aucune donnée existante n'est modifiée ni supprimée ;
--    * IDEMPOTENT : réexécution sans erreur (colonnes vérifiées via
--      information_schema, tables via CREATE TABLE IF NOT EXISTS,
--      settings via INSERT ... ON DUPLICATE KEY UPDATE no-op) ;
--    * RÉVERSIBLE : rollback-004-multimedia.sql ;
--    * Compatible MySQL 5.7+/MariaDB et SQLite (voir setup-sqlite.php).
-- =============================================================================
SET NAMES utf8mb4;
-- -----------------------------------------------------------------------------
--  A1. gallery_photos : options du bandeau d'accueil
--      show_home    : photo affichée dans le bandeau animé de l'accueil ;
--      caption_en   : légende anglaise (la colonne caption reste la FR) ;
--      banner_label : étiquette courte facultative du bandeau.
-- -----------------------------------------------------------------------------
SET @col_exists := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'gallery_photos'
            AND COLUMN_NAME = 'show_home'
    );
SET @ddl := IF(
        @col_exists = 0,
        'ALTER TABLE `gallery_photos`
         ADD COLUMN `show_home` TINYINT(1) NOT NULL DEFAULT 0 AFTER `approved`,
         ADD COLUMN `caption_en` VARCHAR(255) NOT NULL DEFAULT '''' AFTER `caption`,
         ADD COLUMN `banner_label` VARCHAR(120) NOT NULL DEFAULT '''' AFTER `alt_text`,
         ADD KEY `idx_gallery_home` (`show_home`, `approved`, `sort_order`)',
        'SELECT 1'
    );
PREPARE stmt
FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
-- Cas où la table existe déjà mais sans l'index (réexécutions partielles).
SET @idx_exists := (
        SELECT COUNT(*)
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'gallery_photos'
            AND INDEX_NAME = 'idx_gallery_home'
    );
SET @ddl := IF(
        @idx_exists = 0,
        'ALTER TABLE `gallery_photos` ADD KEY `idx_gallery_home` (`show_home`, `approved`, `sort_order`)',
        'SELECT 1'
    );
PREPARE stmt
FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
-- -----------------------------------------------------------------------------
--  B. media_images : images supplémentaires des articles et projets.
--     parent_type = 'article' | 'project' — aucune rupture : la colonne
--     articles.cover_image / projects.image reste la source du visuel principal.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `media_images` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_type` ENUM('article', 'project') NOT NULL,
    `parent_id` BIGINT UNSIGNED NOT NULL,
    `filename` VARCHAR(160) NOT NULL COMMENT 'fichier dans uploads/images (nom aléatoire)',
    `caption_fr` VARCHAR(255) NOT NULL DEFAULT '',
    `caption_en` VARCHAR(255) NOT NULL DEFAULT '',
    `alt_fr` VARCHAR(255) NOT NULL DEFAULT '',
    `alt_en` VARCHAR(255) NOT NULL DEFAULT '',
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_media_parent` (`parent_type`, `parent_id`, `sort_order`),
    CONSTRAINT `fk_media_article` FOREIGN KEY (`parent_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Images supplémentaires des articles et projets (parent_type).';
-- -----------------------------------------------------------------------------
--  C. Rubrique « Actualités » : vidéos, capsules, podcasts.
--     Deux sources exclusives : source_kind='embed' (lien YouTube/Vimeo/
--     Facebook/TikTok/Spotify/Apple) OU source_kind='file' (MP4/MP3 court
--     téléversé). Aucun hébergement de flux distant chez nous.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(180) NOT NULL,
    `lang` ENUM('fr', 'en') NOT NULL DEFAULT 'fr',
    `title` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'description courte (cartes, OG)',
    `body_html` TEXT NOT NULL DEFAULT '' COMMENT 'texte complet (HTML simple)',
    `type` ENUM('video', 'clip', 'podcast') NOT NULL DEFAULT 'video' COMMENT 'video = émission/replay, clip = capsule/extrait, podcast = audio',
    `category` VARCHAR(40) NOT NULL DEFAULT 'conference' COMMENT 'conference|communaute|debat|tele|podcast|sortie',
    `source_kind` ENUM('embed', 'file') NOT NULL DEFAULT 'embed',
    `embed_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL d intégration (plate-forme externe)',
    `media_file` VARCHAR(160) DEFAULT NULL COMMENT 'fichier MP4/MP3 téléversé (uploads/media)',
    `media_mime` VARCHAR(60) DEFAULT NULL,
    `poster_image` VARCHAR(160) DEFAULT NULL COMMENT 'miniature (uploads/images)',
    `event_date` DATE DEFAULT NULL,
    `location` VARCHAR(160) NOT NULL DEFAULT '',
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `comments_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'désactivés par défaut',
    `published_at` DATETIME DEFAULT NULL,
    `seo_title` VARCHAR(160) DEFAULT NULL,
    `seo_description` VARCHAR(320) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_news_slug` (`slug`),
    KEY `idx_news_list` (`status`, `published_at`),
    KEY `idx_news_cat` (`category`, `status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Actualités multimédia : vidéos, capsules et podcasts.';
-- -----------------------------------------------------------------------------
--  C2. Commentaires d'actualités : modérés AVANT publication (statut pending),
--      désactivables publication par publication (news_items.comments_enabled).
--      Anti-spam : CSRF + champ piège + rate-limit (comment_rate_limit),
--      hachage d'IP (jamais l'IP en clair).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news_comments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `news_id` BIGINT UNSIGNED NOT NULL,
    `author_name` VARCHAR(120) NOT NULL,
    `author_email` VARCHAR(254) NOT NULL DEFAULT '',
    `body` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `ip_hash` CHAR(64) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_nc_item` (`news_id`, `status`, `created_at`),
    CONSTRAINT `fk_nc_news` FOREIGN KEY (`news_id`) REFERENCES `news_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Commentaires modérés des publications Actualités.';
-- Rate-limit des commentaires (même principe que contact_rate_limit).
CREATE TABLE IF NOT EXISTS `comment_rate_limit` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_hash` CHAR(64) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_crl_ip_created` (`ip_hash`, `created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Historique de soumission des commentaires (anti-spam).';
-- -----------------------------------------------------------------------------
--  D. Réglages du bandeau + limites média (clés site_settings, idempotent).
--     Valeurs par défaut : bandeau activé, drapeaux affichés (tous), vitesse 40 s.
-- -----------------------------------------------------------------------------
-- banner_flags_mode   : 'all' = les 193 États membres, 'selected' = liste banner_flags_codes ;
-- banner_flags_codes   : codes ISO-2 séparés par des virgules (mode selected) ;
-- banner_speed_seconds : durée d'un aller simple d'une rangée (secondes).
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('banner_enabled', '1'),
    ('banner_show_flags', '1'),
    ('banner_flags_mode', 'all'),
    ('banner_flags_codes', ''),
    ('banner_speed_seconds', '40'),
    ('banner_two_rows', '1'),
    ('news_max_video_mb', '64'),
    ('news_max_audio_mb', '24') ON DUPLICATE KEY
UPDATE `setting_key` = `setting_key`;
-- =============================================================================
--  Fin de migration — 4 nouvelles tables (media_images, news_items,
--  news_comments, comment_rate_limit), 1 table étendue (gallery_photos),
--  0 donnée détruite.
-- =============================================================================