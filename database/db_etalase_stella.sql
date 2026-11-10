-- --------------------------------------------------------
-- SQL DATABASE: db_etalase_stella
-- Stella Etalase Digital - SMK Stella Maris / Telkom Lampung
-- --------------------------------------------------------

-- Catatan untuk Hosting (InfinityFree/cPanel):
-- Jika mengimpor lewat phpMyAdmin di hosting yang nama databasenya sudah ditentukan,
-- baris CREATE DATABASE dan USE di bawah tidak perlu dijalankan (sudah dikomentari).

-- CREATE DATABASE IF NOT EXISTS `db_etalase_stella` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `db_etalase_stella`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'siswa') NOT NULL DEFAULT 'siswa',
  `foto` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data Awal Pengguna (Password default: 'password' atau 'admin123')
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `foto`) VALUES
(1, 'Super Admin Stella', 'superadmin@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'super_admin', NULL),
(2, 'Admin Stella', 'admin@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'admin', NULL),
(3, 'Siswa Stella', 'siswa@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'siswa', NULL),
(4, 'syahnurfajri', 'syahnurfajri0@gmail.com', '$2y$10$7jWapHn9RtyHYFL9mfFawOwzcfPfEBfY6PBFHd3b0RPEX2GJJL.ue', 'siswa', NULL)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- --------------------------------------------------------
-- 2. Struktur Tabel `projects`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `nama_project` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  `link_project` VARCHAR(255) DEFAULT NULL,
  `kategori` VARCHAR(50) NOT NULL DEFAULT 'Web Design',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_projects_user` (`user_id`),
  KEY `idx_projects_created` (`created_at`),
  CONSTRAINT `fk_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Struktur Tabel `project_likes`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `project_likes` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_project_user_like` (`project_id`, `user_id`),
  KEY `fk_likes_project` (`project_id`),
  KEY `fk_likes_user` (`user_id`),
  CONSTRAINT `fk_likes_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
