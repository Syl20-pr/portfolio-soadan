-- =============================================================================
--  Portfolio SKS — Rollback de la migration 005 (accueil : photos + vidéo)
-- =============================================================================
--  ATTENTION : destructif. Les fichiers téléversés (uploads/) ne sont PAS
--  supprimés du disque ; seules les tables et réglages sont retirés.
-- =============================================================================
SET NAMES utf8mb4;
DROP TABLE IF EXISTS `home_video`;
DROP TABLE IF EXISTS `home_photos`;
DELETE FROM `site_settings`
WHERE `setting_key` IN (
        'home_slide_seconds',
        'home_transition',
        'home_video_enabled'
    );
-- =============================================================================
--  Fin du rollback.
-- =============================================================================