-- =============================================================================
--  Portfolio SKS — Rollback de la migration 004 (vitrine multimédia)
-- =============================================================================
--  ATTENTION : destructif. Supprime les objets créés par la migration 004.
--  Les colonnes ajoutées à gallery_photos sont retirées ; les fichiers
--  téléversés (uploads/) ne sont PAS supprimés du disque.
-- =============================================================================
SET NAMES utf8mb4;
DROP TABLE IF EXISTS `comment_rate_limit`;
DROP TABLE IF EXISTS `news_comments`;
DROP TABLE IF EXISTS `news_items`;
-- Images supplémentaires d'articles et de projets.
DROP TABLE IF EXISTS `media_images`;
-- Colonnes du bandeau (conditional drop via procédure non portable en pur SQL :
-- sur MySQL 8 / MariaDB 10.5+, utiliser ALTER TABLE ... IF EXISTS).
ALTER TABLE `gallery_photos` DROP INDEX IF EXISTS `idx_gallery_home`;
ALTER TABLE `gallery_photos` DROP COLUMN IF EXISTS `show_home`;
ALTER TABLE `gallery_photos` DROP COLUMN IF EXISTS `caption_en`;
ALTER TABLE `gallery_photos` DROP COLUMN IF EXISTS `banner_label`;
-- Réglages créés par la migration 004.
DELETE FROM `site_settings`
WHERE `setting_key` IN (
        'banner_enabled',
        'banner_show_flags',
        'banner_flags_mode',
        'banner_flags_codes',
        'banner_speed_seconds',
        'banner_two_rows',
        'news_max_video_mb',
        'news_max_audio_mb'
    );
-- =============================================================================
--  Fin du rollback.
-- =============================================================================