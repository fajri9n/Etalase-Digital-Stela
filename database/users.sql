-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 19 Okt 2026 pada 05.29
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_etalase_stella`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','siswa') DEFAULT 'siswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Super Admin Stella', 'superadmin@stella.sch.id', '$2y$10$iTxQAkB9c2Mt8ktVaUv0Pe3ywW30pXIQhz6TFEEb/pszqNdzpn67C', 'super_admin'),
(2, 'Admin Stella', 'admin@stella.sch.id', '$2y$10$iTxQAkB9c2Mt8ktVaUv0Pe3ywW30pXIQhz6TFEEb/pszqNdzpn67C', 'admin'),
(3, 'Siswa Contoh', 'siswa@stella.sch.id', '$2y$10$iTxQAkB9c2Mt8ktVaUv0Pe3ywW30pXIQhz6TFEEb/pszqNdzpn67C', 'siswa'),
(22, 'edwdd', 'fajri@gmail.com', '$2y$10$9XEwp09ON6Lnsd9jkHCuf.5BcUc2LcbsIW9dZB/gC7FWtCNrtBuV.', 'super_admin'),
(23, 'siswabaru', 'siswa1@stella.sch.id', '$2y$10$bgpd8AADGjhiLQ/KvsnKQecECrPVEY8oeM.3yI9.DJo2.wVQstoDC', 'siswa');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
