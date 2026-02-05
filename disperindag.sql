-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 05, 2026 at 02:45 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `disperindag`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `aktivitas` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `harga_harian`
--

CREATE TABLE `harga_harian` (
  `id` int NOT NULL,
  `laporan_id` int NOT NULL,
  `komoditas_id` int NOT NULL,
  `het_hap` decimal(12,2) DEFAULT NULL,
  `harga_kemarin` decimal(12,2) NOT NULL,
  `harga_hari_ini` decimal(12,2) NOT NULL,
  `perubahan_rp` decimal(12,2) DEFAULT NULL,
  `perubahan_persen` decimal(6,2) DEFAULT NULL,
  `persen_terhadap_het` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komoditas`
--

CREATE TABLE `komoditas` (
  `id` int NOT NULL,
  `kategori_id` int NOT NULL,
  `nama_komoditas` varchar(150) NOT NULL,
  `satuan` enum('kg','ltr') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_harian`
--

CREATE TABLE `laporan_harian` (
  `id` int NOT NULL,
  `pasar_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `dibuat_oleh` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasar`
--

CREATE TABLE `pasar` (
  `id` int NOT NULL,
  `nama_pasar` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','member') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_harga`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_harga` (
`tanggal` date
,`nama_pasar` varchar(150)
,`nama_kategori` varchar(100)
,`nama_komoditas` varchar(150)
,`satuan` enum('kg','ltr')
,`het_hap` decimal(12,2)
,`harga_kemarin` decimal(12,2)
,`harga_hari_ini` decimal(12,2)
,`perubahan_rp` decimal(12,2)
,`perubahan_persen` decimal(6,2)
,`persen_terhadap_het` decimal(6,2)
);

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_harga`
--
DROP TABLE IF EXISTS `view_laporan_harga`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_harga`  AS SELECT `lh`.`tanggal` AS `tanggal`, `p`.`nama_pasar` AS `nama_pasar`, `k`.`nama_kategori` AS `nama_kategori`, `km`.`nama_komoditas` AS `nama_komoditas`, `km`.`satuan` AS `satuan`, `hh`.`het_hap` AS `het_hap`, `hh`.`harga_kemarin` AS `harga_kemarin`, `hh`.`harga_hari_ini` AS `harga_hari_ini`, `hh`.`perubahan_rp` AS `perubahan_rp`, `hh`.`perubahan_persen` AS `perubahan_persen`, `hh`.`persen_terhadap_het` AS `persen_terhadap_het` FROM ((((`harga_harian` `hh` join `laporan_harian` `lh` on((`hh`.`laporan_id` = `lh`.`id`))) join `pasar` `p` on((`lh`.`pasar_id` = `p`.`id`))) join `komoditas` `km` on((`hh`.`komoditas_id` = `km`.`id`))) join `kategori` `k` on((`km`.`kategori_id` = `k`.`id`)))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `harga_harian`
--
ALTER TABLE `harga_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_id` (`laporan_id`),
  ADD KEY `komoditas_id` (`komoditas_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `komoditas`
--
ALTER TABLE `komoditas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `laporan_harian`
--
ALTER TABLE `laporan_harian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasar_id` (`pasar_id`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indexes for table `pasar`
--
ALTER TABLE `pasar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `harga_harian`
--
ALTER TABLE `harga_harian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komoditas`
--
ALTER TABLE `komoditas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_harian`
--
ALTER TABLE `laporan_harian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pasar`
--
ALTER TABLE `pasar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `harga_harian`
--
ALTER TABLE `harga_harian`
  ADD CONSTRAINT `harga_harian_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan_harian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `harga_harian_ibfk_2` FOREIGN KEY (`komoditas_id`) REFERENCES `komoditas` (`id`);

--
-- Constraints for table `komoditas`
--
ALTER TABLE `komoditas`
  ADD CONSTRAINT `komoditas_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Constraints for table `laporan_harian`
--
ALTER TABLE `laporan_harian`
  ADD CONSTRAINT `laporan_harian_ibfk_1` FOREIGN KEY (`pasar_id`) REFERENCES `pasar` (`id`),
  ADD CONSTRAINT `laporan_harian_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
