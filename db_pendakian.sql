-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 31, 2025 at 03:16 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pendakian`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota_pengdaki`
--

CREATE TABLE `anggota_pendaki` (
  `anggota_id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `nama_anggota` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nik` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `umur` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jalur_pendakian`
--

CREATE TABLE `jalur_pendakian` (
  `jalur_id` int NOT NULL,
  `nama_jalur` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kuota_harian` int NOT NULL,
  `tarif_tiket` decimal(10,2) NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `metode` enum('transfer_bank','gopay','ovo','dana') COLLATE utf8mb4_general_ci DEFAULT 'transfer_bank',
  `jumlah_bayar` decimal(10,2) NOT NULL,
  `tanggal_bayar` datetime DEFAULT CURRENT_TIMESTAMP,
  `bukti_bayar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_pembayaran` enum('pending','terkonfirmasi','ditolak') COLLATE utf8mb4_general_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pendakian`
--

CREATE TABLE `pendakian` (
  `pendakian_id` int NOT NULL,
  `jalur_id` int NOT NULL,
  `tanggal_pendakian` date NOT NULL,
  `kuota_tersedia` int NOT NULL,
  `status` enum('tersedia','penuh','selesai') COLLATE utf8mb4_general_ci DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `pesanan_id` int NOT NULL,
  `user_id` int NOT NULL,
  `pendakian_id` int NOT NULL,
  `tanggal_pesan` datetime DEFAULT CURRENT_TIMESTAMP,
  `jumlah_pendaki` int NOT NULL,
  `total_bayar` decimal(10,2) NOT NULL,
  `status_pesanan` enum('menunggu_pembayaran','lunas','batal') COLLATE utf8mb4_general_ci DEFAULT 'menunggu_pembayaran'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('pendaki','admin') COLLATE utf8mb4_general_ci DEFAULT 'pendaki',
  `tanggal_daftar` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nama`, `email`, `password`, `no_hp`, `role`, `tanggal_daftar`) VALUES
(1, 'agung', 'agung@gmail.com', '$2y$10$A.Efem7Hqvy.vkwxiE0UFe8hTnupewyWcxo1hZ1OwYrOYEKNE0tsS', '089504982271', 'pendaki', '2025-10-21 09:49:47'),
(3, 'bbb', 'bbb@gmail.com', '$2y$10$2tslAdzFaL3VM.ljk9ZX8.rRBQf0jvPc4hiNjoYDbFiURFc5lN0ja', '088', 'pendaki', '2025-10-21 10:11:37'),
(4, 'fahril', 'fahril@gmail.com', '$2y$10$Oj62ACsNPTT0RG2osXRF..D7sMYQ7SkGp1KiNbV8411fe.RdaUI9q', '16', 'admin', '2025-10-21 16:54:02'),
(6, 'alsitya', 'alsi@gmail.com', '$2y$10$GHxheyQpJdL4UhnZtaJxb.fEZU4kifTl3ZSueNGRsYvNwhGZFNsL.', '085607551913', 'pendaki', '2025-10-30 14:38:49'),
(7, 'alipp', 'alippp@gmail.com', '$2y$10$OF6O8nXj5KNlGHfYNtGRnuAEYN7oukgiv2m8hHBePQg2szBMBKoo2', '08968678756', 'pendaki', '2025-10-31 09:58:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota_pendaki`
--
ALTER TABLE `anggota_pendaki`
  ADD PRIMARY KEY (`anggota_id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indexes for table `jalur_pendakian`
--
ALTER TABLE `jalur_pendakian`
  ADD PRIMARY KEY (`jalur_id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indexes for table `pendakian`
--
ALTER TABLE `pendakian`
  ADD PRIMARY KEY (`pendakian_id`),
  ADD KEY `jalur_id` (`jalur_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`pesanan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pendakian_id` (`pendakian_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota_pendaki`
--
ALTER TABLE `anggota_pendaki`
  MODIFY `anggota_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jalur_pendakian`
--
ALTER TABLE `jalur_pendakian`
  MODIFY `jalur_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `pembayaran_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pendakian`
--
ALTER TABLE `pendakian`
  MODIFY `pendakian_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `pesanan_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota_pendaki`
--
ALTER TABLE `anggota_pendaki`
  ADD CONSTRAINT `anggota_pendaki_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`pesanan_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`pesanan_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pendakian`
--
ALTER TABLE `pendakian`
  ADD CONSTRAINT `pendakian_ibfk_1` FOREIGN KEY (`jalur_id`) REFERENCES `jalur_pendakian` (`jalur_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`pendakian_id`) REFERENCES `pendakian` (`pendakian_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
