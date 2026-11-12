-- =============================================================================
--  Portfolio SKS — ROLLBACK de la migration 002 (CV dynamique)
-- =============================================================================
--  Supprime uniquement les tables créées par migration-002-cv-dynamique.sql.
--  Toutes les autres tables (d'origine et migration 001) sont préservées.
-- =============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `cv_personal`;
DROP TABLE IF EXISTS `cv_state`;
DROP TABLE IF EXISTS `cv_sections`;
DROP TABLE IF EXISTS `cv_publications`;
DROP TABLE IF EXISTS `cv_associations`;
DROP TABLE IF EXISTS `cv_languages`;
DROP TABLE IF EXISTS `cv_photo`;
SET FOREIGN_KEY_CHECKS = 1;