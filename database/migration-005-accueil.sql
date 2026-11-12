-- =============================================================================
--  Portfolio SKS — Migration 005 : accueil (photos dédiées + vidéo de présentation)
-- =============================================================================
--  A. home_photos : rubrique DÉDIÉE aux photos du diaporama d'accueil
--     (distincte de la galerie). Une ligne par photo, légende et texte
--     alternatif FR/EN, activation, ordre.
--     Repli : tant que home_photos est vide, le diaporama continue de
--     réutiliser les photos de galerie marquées « accueil » (aucune rupture).
--  B. home_video : UNE vidéo par langue (FR / EN), indépendantes.
--     Sources exclusives : embed (YouTube/Vimeo) OU fichier MP4 téléversé
--     (+ affiche poster + sous-titres WebVTT facultatifs).
--  C. Réglages site_settings : durée d'affichage, transition, interrupteur vidéo.
--
--  IDEMPOTENT (CREATE IF NOT EXISTS / colonnes vérifiées) et RÉVERSIBLE
--  (rollback-005-accueil.sql). Aucune donnée existante modifiée.
-- =============================================================================
SET NAMES utf8mb4;
-- -----------------------------------------------------------------------------
--  A. Photos d'accueil
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `home_photos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(160) NOT NULL COMMENT 'fichier uploads/images (nom aléatoire)',
  `caption_fr` VARCHAR(255) NOT NULL DEFAULT '',
  `caption_en` VARCHAR(255) NOT NULL DEFAULT '',
  `alt_fr` VARCHAR(255) NOT NULL DEFAULT '',
  `alt_en` VARCHAR(255) NOT NULL DEFAULT '',
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_home_photos` (`active`, `sort_order`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Photos du diaporama d accueil (rubrique dédiée).';
-- -----------------------------------------------------------------------------
--  B. Vidéo de présentation (une ligne par langue)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `home_video` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lang` ENUM('fr', 'en') NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `source_kind` ENUM('embed', 'file') NOT NULL DEFAULT 'embed',
  `embed_url` VARCHAR(500) DEFAULT NULL,
  `media_file` VARCHAR(160) DEFAULT NULL COMMENT 'MP4 téléversé (uploads/media)',
  `media_mime` VARCHAR(60) DEFAULT NULL,
  `poster_image` VARCHAR(160) DEFAULT NULL COMMENT 'affiche (uploads/images)',
  `vtt_file` VARCHAR(160) DEFAULT NULL COMMENT 'sous-titres WebVTT (uploads/media)',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `excerpt` VARCHAR(500) NOT NULL DEFAULT '',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_home_video_lang` (`lang`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Vidéo de présentation de l accueil (une par langue).';
-- -----------------------------------------------------------------------------
--  C. Réglages (valeurs par défaut ; idempotent)
--    home_slide_seconds : durée d'affichage de CHAQUE photo (secondes) ;
--    home_transition    : 'fade' (fondu) ou 'slide' (glissement) ;
--    home_video_enabled : interrupteur global d'affichage de la vidéo.
-- -----------------------------------------------------------------------------
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('home_slide_seconds', '6'),
  ('home_transition', 'fade'),
  ('home_video_enabled', '1') ON DUPLICATE KEY
UPDATE `setting_key` = `setting_key`;
-- =============================================================================
--  Fin de migration — 2 nouvelles tables, 3 réglages, 0 donnée modifiée.
-- =============================================================================