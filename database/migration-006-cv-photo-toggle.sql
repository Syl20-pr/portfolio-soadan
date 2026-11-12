-- =============================================================================
--  Portfolio SKS — Migration 006 : CV — interrupteur photo + réglages
-- =============================================================================
--  cv_photo.show_on_pdf : la photo peut être affichée ou non sur le PDF
--  (elle reste visible sur les deux CV, FR et EN, lorsqu'elle est activée).
--  Idempotent (colonne vérifiée) et réversible.
-- =============================================================================
SET NAMES utf8mb4;
SET @col := (
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'cv_photo'
            AND COLUMN_NAME = 'show_on_pdf'
    );
SET @ddl := IF(
        @col = 0,
        'ALTER TABLE `cv_photo`
         ADD COLUMN `show_on_pdf` TINYINT(1) NOT NULL DEFAULT 1 AFTER `crop_zoom`',
        'SELECT 1'
    );
PREPARE s
FROM @ddl;
EXECUTE s;
DEALLOCATE PREPARE s;
-- =============================================================================
--  Fin de migration — 1 colonne, 0 donnée modifiée.
-- =============================================================================