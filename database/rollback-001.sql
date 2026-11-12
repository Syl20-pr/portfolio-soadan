-- =============================================================================
--  Portfolio SKS — ROLLBACK de la migration 001 (modules de contenu)
-- =============================================================================
--  Supprime les tables créées par migration-001-content.sql.
--  Les tables d'origine du site (messages, contact_rate_limit, login_attempts)
--  ne sont JAMAIS touchées par ce script.
-- =============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `article_tags`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `articles`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `gallery_photos`;
DROP TABLE IF EXISTS `cv_documents`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `education`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `certifications`;
DROP TABLE IF EXISTS `distinctions`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `admin_users`;
SET FOREIGN_KEY_CHECKS = 1;
-- Attention : exécuter ce rollback efface les contenus ajoutés depuis
-- l'administration. Faites un dump de la base avant (voir documentation).