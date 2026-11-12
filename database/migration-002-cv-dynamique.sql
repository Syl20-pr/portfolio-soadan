-- =============================================================================
--  Portfolio SKS — Migration 002 : CV dynamique avec photo professionnelle
-- =============================================================================
--  PRINCIPES IDENTIQUES À LA MIGRATION 001 :
--    * ADDITIF : aucune table existante n'est modifiée ni supprimée ;
--    * idempotent (réexécution sans erreur) ;
--    * réversible via rollback-002.sql ;
--    * RÉUTILISE les tables de la migration 001 : experiences, education,
--      skills, certifications, distinctions, cv_documents, site_settings.
--      Aucune rubrique dupliquée : ce module s'appuie dessus.
-- =============================================================================
SET NAMES utf8mb4;
-- -----------------------------------------------------------------------------
--  Table : cv_photo
--  Photo professionnelle unique (une seule ligne active). Le fichier est
--  stocké dans uploads/images avec un nom aléatoire (voir includes/upload.php).
--  crop_* : paramètres de recadrage appliqués à l'affichage (ratio 3:4 conseillé).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_photo` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(160) NOT NULL,
    `original_name` VARCHAR(160) NOT NULL DEFAULT '',
    `crop_x` DECIMAL(6, 3) NOT NULL DEFAULT 50.000 COMMENT '% du bord gauche du recadrage',
    `crop_y` DECIMAL(6, 3) NOT NULL DEFAULT 50.000 COMMENT '% du bord supérieur du recadrage',
    `crop_zoom` DECIMAL(6, 3) NOT NULL DEFAULT 100.000 COMMENT '% de zoom (100 = image entière)',
    `uploaded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Photo professionnelle du CV (une seule ligne).';
-- -----------------------------------------------------------------------------
--  Table : cv_languages
--  Langues parlées avec niveau (rubrique « Langues » du CV).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_languages` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(60) NOT NULL COMMENT 'Nom de la langue (universel)',
    `level_fr` VARCHAR(60) NOT NULL DEFAULT '' COMMENT 'ex. Courant',
    `level_en` VARCHAR(60) NOT NULL DEFAULT '' COMMENT 'ex. Fluent',
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_lang_sort` (`sort_order`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Langues parlées du CV.';
-- -----------------------------------------------------------------------------
--  Table : cv_associations
--  Engagements associatifs / bénévolat (bilingue).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_associations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `org_fr` VARCHAR(200) NOT NULL,
    `org_en` VARCHAR(200) NOT NULL DEFAULT '',
    `role_fr` VARCHAR(200) NOT NULL DEFAULT '',
    `role_en` VARCHAR(200) NOT NULL DEFAULT '',
    `desc_fr` TEXT NOT NULL DEFAULT '',
    `desc_en` TEXT NOT NULL DEFAULT '',
    `date_start` DATE DEFAULT NULL,
    `date_end` DATE DEFAULT NULL COMMENT 'NULL = en cours',
    `is_current` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_assoc_sort` (`sort_order`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Engagements associatifs du CV.';
-- -----------------------------------------------------------------------------
--  Table : cv_publications
--  Publications listées spécifiquement dans le CV (liées optionnellement
--  à la table articles de la migration 001).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_publications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title_fr` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255) NOT NULL DEFAULT '',
    `venue_fr` VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'revue, conférence, support',
    `venue_en` VARCHAR(200) NOT NULL DEFAULT '',
    `year` YEAR DEFAULT NULL,
    `url` VARCHAR(400) DEFAULT NULL,
    `article_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'lien facultatif vers articles',
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_cvp_sort` (`sort_order`),
    CONSTRAINT `fk_cvp_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE
    SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Publications citées dans le CV.';
-- -----------------------------------------------------------------------------
--  Table : cv_sections
--  Ordre d'affichage et visibilité des rubriques du CV, PAR LANGUE.
--  sort_order : plus petit = affiché en premier. visible = 0 : masquée.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_sections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_key` VARCHAR(60) NOT NULL COMMENT 'summary|education|experience|skills|languages|certifications|associations|distinctions|publications',
    `lang` ENUM('fr', 'en') NOT NULL,
    `title` VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'titre affiché (facultatif, défaut intégré)',
    `visible` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_section_lang` (`section_key`, `lang`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Ordre et visibilité des rubriques du CV (par langue).';
-- -----------------------------------------------------------------------------
--  Table : cv_state
--  État du CV généré : brouillon vs publié, modèle choisi, horodatages.
--  - draft_* : dernière génération enregistrée comme brouillon.
--  - published_* : version actuellement visible/publiable publiquement.
--  Pour chaque langue, un CV est généré indépendamment (FR / EN).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_state` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `lang` ENUM('fr', 'en') NOT NULL,
    `template` VARCHAR(40) NOT NULL DEFAULT 'diplomatic' COMMENT 'classic|modern|diplomatic',
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME DEFAULT NULL,
    `published_file` VARCHAR(160) DEFAULT NULL COMMENT 'PDF publié (uploads/cv/)',
    `draft_file` VARCHAR(160) DEFAULT NULL COMMENT 'PDF brouillon (uploads/cv/drafts/)',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_state_lang` (`lang`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'État de génération/publication du CV par langue.';
-- -----------------------------------------------------------------------------
--  Table : cv_personal
--  Informations personnelles et profil professionnel, PAR LANGUE
--  (le nom et les coordonnées sont universels mais le titre/résumé diffèrent).
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cv_personal` (
    `lang` ENUM('fr', 'en') NOT NULL,
    `full_name` VARCHAR(160) NOT NULL DEFAULT '',
    `headline` VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'titre professionnel',
    `summary` TEXT NOT NULL DEFAULT '' COMMENT 'profil / résumé',
    `email` VARCHAR(254) NOT NULL DEFAULT '',
    `phone` VARCHAR(40) NOT NULL DEFAULT '',
    `location` VARCHAR(160) NOT NULL DEFAULT '',
    `website` VARCHAR(300) NOT NULL DEFAULT '',
    `linkedin` VARCHAR(300) NOT NULL DEFAULT '',
    `nationality` VARCHAR(80) NOT NULL DEFAULT '',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lang`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Informations personnelles du CV (par langue).';
-- =============================================================================
--  DONNÉES INITIALES
-- =============================================================================
-- Rubriques dans l'ordre classique, pour les deux langues.
INSERT INTO `cv_sections` (`section_key`, `lang`, `visible`, `sort_order`)
VALUES ('summary', 'fr', 1, 1),
    ('summary', 'en', 1, 1),
    ('experience', 'fr', 1, 2),
    ('experience', 'en', 1, 2),
    ('education', 'fr', 1, 3),
    ('education', 'en', 1, 3),
    ('skills', 'fr', 1, 4),
    ('skills', 'en', 1, 4),
    ('languages', 'fr', 1, 5),
    ('languages', 'en', 1, 5),
    ('certifications', 'fr', 1, 6),
    ('certifications', 'en', 1, 6),
    ('publications', 'fr', 1, 7),
    ('publications', 'en', 1, 7),
    ('associations', 'fr', 1, 8),
    ('associations', 'en', 1, 8),
    ('distinctions', 'fr', 1, 9),
    ('distinctions', 'en', 1, 9) ON DUPLICATE KEY
UPDATE `section_key` = `section_key`;
-- Lignes personnelles vides (FR/EN) prêtes à compléter.
INSERT INTO `cv_personal` (`lang`, `full_name`)
VALUES ('fr', ''),
    ('en', '') ON DUPLICATE KEY
UPDATE `lang` = `lang`;
-- État initial : brouillons vides, modèle classique, pour les deux langues.
INSERT INTO `cv_state` (`lang`, `template`, `status`)
VALUES ('fr', 'diplomatic', 'draft'),
    ('en', 'diplomatic', 'draft') ON DUPLICATE KEY
UPDATE `lang` = `lang`;
-- =============================================================================
--  Fin de migration — 6 nouvelles tables, 0 table modifiée.
-- =============================================================================