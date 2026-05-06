-- ──────────────────────────────────────────────────────────────────
--  Friedrich Schäfer — CMS Datenbank
--  MySQL / MariaDB
--  Host: 127.0.0.1   Port: 3306
--  DB:   friedrichdb User: friedrichdbuser
-- ──────────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS `friedrichdb`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `friedrichdb`;

-- ─── Admin (single account, password is changeable from the CMS) ───
CREATE TABLE IF NOT EXISTS `admin` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(64)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,                    -- PHP password_hash() (bcrypt/argon2)
  `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default login wird einmalig durch /admin/init.php gesetzt:
--   Username: Friedrich-Schaefer
--   Passwort: start123  (sofort über „Passwort ändern" wechseln!)
-- init.php nutzt PHP-eigenes password_hash() — kompatibel mit password_verify().
-- (Kein hartgecodeter Hash hier, weil PHP-Versionen verschiedene bcrypt-Varianten erzeugen.)

-- ─── Sessions (login persistence) ───
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `token`      CHAR(64)  NOT NULL,
  `admin_id`   INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`token`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `fk_session_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Allgemeine Seiteninhalte (Hero, Bio, Kontakt-Texte etc.) ───
-- key → JSON mit DE/EN-Varianten
CREATE TABLE IF NOT EXISTS `content_blocks` (
  `key_name`   VARCHAR(120) NOT NULL,
  `value_de`   MEDIUMTEXT   NULL,
  `value_en`   MEDIUMTEXT   NULL,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initiale Werte
INSERT INTO `content_blocks` (`key_name`, `value_de`, `value_en`) VALUES
  ('hero_quote',          'Musik, die erzählt — klar, lebendig, klassisch.', 'Music that speaks — clear, vivid, classical.'),
  ('bio_paragraph_1',     'Friedrich Schäfer ist ein klassischer Pianist aus Mittelsachsen mit einer außergewöhnlichen musikalischen Reife und einem breiten Repertoire, das von der Wiener Klassik bis zur romantischen Klavierliteratur reicht.', 'Friedrich Schäfer is a classical pianist from central Saxony with exceptional musical maturity and a broad repertoire spanning from the Viennese classical era to romantic piano literature.'),
  ('bio_paragraph_2',     'Sein Spiel verbindet pianistische Präzision mit lyrischer Tiefe und erzählerischer Energie.', 'His playing combines pianistic precision with lyrical depth and narrative energy.'),
  ('bio_paragraph_3',     'Sein Repertoire umfasst charaktervolle Miniaturen ebenso wie große Konzertwerke.', 'His repertoire encompasses expressive miniatures as well as major concert works.'),
  ('contact_email',       'kontakt@fjs-pianist.de', 'kontakt@fjs-pianist.de'),
  ('contact_management',  'management@fjs-pianist.de', 'management@fjs-pianist.de'),
  ('contact_phone',       '',  ''),
  ('contact_address',     '', '')
ON DUPLICATE KEY UPDATE `key_name` = `key_name`;

-- ─── Konzerte ───
CREATE TABLE IF NOT EXISTS `concerts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `concert_date` DATE         NOT NULL,
  `title_de`     VARCHAR(255) NOT NULL,
  `title_en`     VARCHAR(255) NULL,
  `program_de`   VARCHAR(500) NULL,
  `program_en`   VARCHAR(500) NULL,
  `city`         VARCHAR(120) NULL,
  `venue`        VARCHAR(255) NULL,
  `published`    TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`   INT          NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`concert_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Videos (Konzertmitschnitte) ───
CREATE TABLE IF NOT EXISTS `videos` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title_de`    VARCHAR(255) NOT NULL,
  `title_en`    VARCHAR(255) NULL,
  `caption_de`  VARCHAR(500) NULL,
  `caption_en`  VARCHAR(500) NULL,
  `file_path`   VARCHAR(500) NOT NULL,                       -- relativ zu /uploads/videos/
  `poster_path` VARCHAR(500) NULL,                           -- optionales Vorschaubild
  `duration`    VARCHAR(12)  NULL,                           -- z.B. "4:12"
  `featured`    TINYINT(1)   NOT NULL DEFAULT 0,             -- großes Hauptvideo?
  `published`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Hörproben (Audio) ───
CREATE TABLE IF NOT EXISTS `audio_tracks` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `track_num`   VARCHAR(8)   NULL,
  `title_de`    VARCHAR(255) NOT NULL,
  `title_en`    VARCHAR(255) NULL,
  `composer_de` VARCHAR(255) NULL,
  `composer_en` VARCHAR(255) NULL,
  `file_path`   VARCHAR(500) NOT NULL,                       -- relativ zu /uploads/audio/
  `duration`    VARCHAR(12)  NULL,                           -- z.B. "3:42"
  `published`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Pressezitate ───
CREATE TABLE IF NOT EXISTS `press_quotes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_de`   TEXT         NOT NULL,
  `quote_en`   TEXT         NULL,
  `source_de`  VARCHAR(255) NULL,
  `source_en`  VARCHAR(255) NULL,
  `published`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Galerie ───
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_path`  VARCHAR(500) NOT NULL,                        -- relativ zu /uploads/gallery/
  `caption_de` VARCHAR(255) NULL,
  `caption_en` VARCHAR(255) NULL,
  `published`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Hero-Slides (Hintergrund-Medien für die Startseite) ───
CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `media_type` ENUM('image','video') NOT NULL,
  `file_path`  VARCHAR(500) NOT NULL,
  `published`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── User für die Anwendung anlegen ───
-- (auf dem Server einmalig ausführen, falls noch nicht vorhanden)
-- CREATE USER 'friedrichdbuser'@'127.0.0.1' IDENTIFIED BY 'fjWVT9Iy7j5ecItEClfi';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON friedrichdb.* TO 'friedrichdbuser'@'127.0.0.1';
-- FLUSH PRIVILEGES;
