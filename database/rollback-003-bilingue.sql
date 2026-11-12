-- =============================================================================
--  Portfolio SKS — Rollback de la migration 003 (bilinguisme côte à côte)
-- =============================================================================
--  ATTENTION : destructif.
--    * Retire les colonnes _en ajoutées (articles, projects, gallery_photos,
--      news_items) — le contenu anglais de ces colonnes est PERDU ;
--    * les anciennes lignes articles (lang='en') supprimées lors de la
--      fusion ne sont PAS restaurées (leur contenu avait été déplacé).
-- =============================================================================
SET NAMES utf8mb4;
ALTER TABLE `articles` DROP INDEX IF EXISTS `uq_articles_slug_en`,
    DROP COLUMN IF EXISTS `slug_en`,
    DROP COLUMN IF EXISTS `title_en`,
    DROP COLUMN IF EXISTS `excerpt_en`,
    DROP COLUMN IF EXISTS `content_html_en`,
    DROP COLUMN IF EXISTS `seo_title_en`,
    DROP COLUMN IF EXISTS `seo_description_en`;
ALTER TABLE `projects` DROP COLUMN IF EXISTS `title_en`,
    DROP COLUMN IF EXISTS `role_en`,
    DROP COLUMN IF EXISTS `summary_en`,
    DROP COLUMN IF EXISTS `description_en`,
    DROP COLUMN IF EXISTS `objectives_en`,
    DROP COLUMN IF EXISTS `results_en`,
    DROP COLUMN IF EXISTS `partners_en`;
ALTER TABLE `gallery_photos` DROP COLUMN IF EXISTS `alt_fr`,
    DROP COLUMN IF EXISTS `alt_en`,
    DROP COLUMN IF EXISTS `banner_label_fr`,
    DROP COLUMN IF EXISTS `banner_label_en`;
ALTER TABLE `news_items` DROP INDEX IF EXISTS `uq_news_slug_en`,
    DROP COLUMN IF EXISTS `slug_en`,
    DROP COLUMN IF EXISTS `title_en`,
    DROP COLUMN IF EXISTS `excerpt_en`,
    DROP COLUMN IF EXISTS `body_html_en`,
    DROP COLUMN IF EXISTS `location_en`;
-- =============================================================================
--  Fin du rollback.
-- =============================================================================