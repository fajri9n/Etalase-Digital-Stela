-- --------------------------------------------------------
-- Membuat Database
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_etalase_stella` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_etalase_stella`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
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
-- Insert Data Dummy untuk `users`
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Super Admin Stella', 'superadmin@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'super_admin'),
(2, 'Admin Stella', 'admin@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'admin'),
(3, 'Siswa Stella', 'siswa@stella.sch.id', '$2y$10$kYJqWNUXVeIjHYXmoprZdOi5NHbcaLUnL0bmHmLKNvAsf29pf3oQG', 'siswa'),
(4, 'syahnurfajri', 'syahnurfajri0@gmail.com', '$2y$10$7jWapHn9RtyHYFL9mfFawOwzcfPfEBfY6PBFHd3b0RPEX2GJJL.ue', 'siswa');

-- --------------------------------------------------------
-- Struktur Tabel `projects`
-- --------------------------------------------------------
CREATE TABLE `projects` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `nama_project` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  `link_project` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_projects_user` (`user_id`),
  KEY `idx_projects_created` (`created_at`),
  CONSTRAINT `fk_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;