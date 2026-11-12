-- =============================================================================
--  Portfolio SOADAN Koffi Sylvain (SKS) — Schéma de base de données
--  Moteur : MySQL 5.7+ / MariaDB 10.2+
--  Import  : phpMyAdmin, 'mysql -u user -p base < schema.sql', ou CLI MariaDB
-- =============================================================================
--
--  Ce fichier crée l'ensemble des tables nécessaires au fonctionnement du
--  portfolio côté serveur (formulaire de contact, limitation anti-spam,
--  messages reçus consultables depuis l'espace administrateur).
--
--  IMPORTANT : créez la base et l'utilisateur MySQL au préalable, puis
--  importez ce fichier DANS cette base :
--
--      CREATE DATABASE portfolio_sks
--        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--      CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'mot_de_passe';
--      GRANT ALL PRIVILEGES ON portfolio_sks.* TO 'portfolio_user'@'localhost';
--      FLUSH PRIVILEGES;
--
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------------------------------
--  Table : messages
--  Rôle  : message envoyé depuis le formulaire de contact du portfolio.
--          Chaque ligne conserve la langue d'origine, le statut de traitement
--          et un hachage d'IP (jamais l'IP en clair) pour l'anti-spam.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(160)    NOT NULL,
    `email`       VARCHAR(254)    NOT NULL,
    `phone`       VARCHAR(40)     DEFAULT NULL,
    `subject`     VARCHAR(200)    DEFAULT NULL,
    `message`     TEXT            NOT NULL,
    `language`    ENUM('fr','en') NOT NULL DEFAULT 'fr',
    `collab_type` VARCHAR(80)     DEFAULT NULL,
    `ip_hash`     CHAR(64)        DEFAULT NULL,
    `user_agent`  VARCHAR(255)    DEFAULT NULL,
    `status`      ENUM('new','read','processed') NOT NULL DEFAULT 'new',
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_messages_status`  (`status`),
    KEY `idx_messages_created` (`created_at`),
    KEY `idx_messages_email`   (`email`),
    KEY `idx_messages_ip_hash` (`ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Messages reçus via le formulaire de contact du portfolio.';

-- -----------------------------------------------------------------------------
--  Table : contact_rate_limit
--  Rôle  : journal des tentatives de soumission, utilisé pour limiter le nombre
--          de messages par adresse IP et par fenêtre de temps (anti-spam).
--          Le nettoyage des lignes anciennes est effectué par l'application.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_rate_limit` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_hash`     CHAR(64)        NOT NULL,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rate_ip_created` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historique de soumissions pour la limitation anti-spam.';

-- -----------------------------------------------------------------------------
--  Table : login_attempts
--  Rôle  : limitation des tentatives de connexion à l'espace administrateur
--          (protection contre le bruteforce). Aucune IP en clair n'est stockée.
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_hash`     CHAR(64)        NOT NULL,
    `successful`  TINYINT(1)      NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_ip_created` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tentatives de connexion admin pour la protection bruteforce.';
