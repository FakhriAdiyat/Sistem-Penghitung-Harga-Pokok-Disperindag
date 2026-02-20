-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 19, 2026 at 07:17 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_harga_bapok`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan_pokok`
--

CREATE TABLE `bahan_pokok` (
  `id` int NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `het_hap` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan_pokok`
--

INSERT INTO `bahan_pokok` (`id`, `nama_bahan`, `kategori`, `satuan`, `het_hap`, `created_at`) VALUES
(1, 'Beras', 'Premium', 'Kg', '0.00', '2026-02-18 04:44:33'),
(2, 'Gula', 'Pasir Kristal (Curah)', 'Kg', '0.00', '2026-02-18 04:44:33'),
(3, 'Minyak Goreng', 'Minyakita', 'Lt', '0.00', '2026-02-18 04:44:33'),
(4, 'Daging Sapi', 'Paha Depan', 'Kg', '0.00', '2026-02-18 04:44:33'),
(5, 'Daging Ayam', 'Ayam Broiler', 'Kg', '0.00', '2026-02-18 04:44:33'),
(6, 'Telur Ayam', 'Ras', 'Kg', '0.00', '2026-02-18 04:44:33'),
(7, 'Cabe Rawit', 'Merah', 'Kg', '0.00', '2026-02-18 04:44:33'),
(8, 'Bawang Merah', '-', 'Kg', '0.00', '2026-02-18 04:44:33'),
(9, 'Bawang Putih', 'Honan', 'Kg', '0.00', '2026-02-18 04:44:33'),
(10, 'Timun Sedang', '-', 'Kg', '0.00', '2026-02-18 04:44:33'),
(11, 'Kacang Kedelai', 'Eks Import', 'Kg', '0.00', '2026-02-18 04:44:33'),
(12, 'Cabai', 'Keriting', 'Kg', '0.00', '2026-02-19 04:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `harga`
--

CREATE TABLE `harga` (
  `id` int NOT NULL,
  `bahan_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `rata_rata` decimal(12,2) DEFAULT NULL,
  `rata_penyimpangan` decimal(12,2) DEFAULT NULL,
  `fluktuasi_persen` decimal(8,2) DEFAULT NULL,
  `stabilitas_persen` decimal(8,2) DEFAULT NULL,
  `persen_kenaikan` decimal(8,2) DEFAULT NULL,
  `persen_penurunan` decimal(8,2) DEFAULT NULL,
  `kenaikan_rp` decimal(12,2) DEFAULT NULL,
  `penurunan_rp` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `harga`
--

INSERT INTO `harga` (`id`, `bahan_id`, `tanggal`, `harga`, `created_at`, `rata_rata`, `rata_penyimpangan`, `fluktuasi_persen`, `stabilitas_persen`, `persen_kenaikan`, `persen_penurunan`, `kenaikan_rp`, `penurunan_rp`) VALUES
(1, 1, '2026-02-18', '14000.00', '2026-02-18 04:44:33', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(2, 1, '2026-02-18', '13500.00', '2026-02-18 04:44:33', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(3, 2, '2026-02-18', '18000.00', '2026-02-18 04:44:33', '18000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(4, 3, '2026-02-18', '15700.00', '2026-02-18 04:44:33', '15700.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(5, 4, '2026-02-18', '135000.00', '2026-02-18 04:44:33', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(6, 4, '2026-02-18', '135000.00', '2026-02-18 04:44:33', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(7, 5, '2026-02-18', '35000.00', '2026-02-18 04:44:33', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(8, 6, '2026-02-18', '29000.00', '2026-02-18 04:44:33', '29000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(9, 7, '2026-02-18', '80000.00', '2026-02-18 04:44:33', '80000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(10, 8, '2026-02-18', '35000.00', '2026-02-18 04:44:33', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(11, 9, '2026-02-18', '38000.00', '2026-02-18 04:44:33', '38000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(12, 10, '2026-02-18', '15000.00', '2026-02-18 04:44:33', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(13, 11, '2026-02-18', '15000.00', '2026-02-18 04:44:33', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(14, 1, '2026-02-19', '14000.00', '2026-02-19 04:02:02', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(15, 1, '2026-02-19', '13500.00', '2026-02-19 04:02:02', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(16, 2, '2026-02-19', '18000.00', '2026-02-19 04:02:02', '18000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(17, 3, '2026-02-19', '15700.00', '2026-02-19 04:02:02', '15700.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(18, 4, '2026-02-19', '135000.00', '2026-02-19 04:02:02', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(19, 4, '2026-02-19', '135000.00', '2026-02-19 04:02:02', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(20, 5, '2026-02-19', '35000.00', '2026-02-19 04:02:02', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(21, 6, '2026-02-19', '29000.00', '2026-02-19 04:02:02', '29000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(22, 7, '2026-02-19', '80000.00', '2026-02-19 04:02:02', '80000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(23, 8, '2026-02-19', '35000.00', '2026-02-19 04:02:02', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(24, 9, '2026-02-19', '38000.00', '2026-02-19 04:02:02', '38000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(25, 10, '2026-02-19', '15000.00', '2026-02-19 04:02:02', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(26, 11, '2026-02-19', '15000.00', '2026-02-19 04:02:02', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(27, 1, '2026-02-19', '14000.00', '2026-02-19 04:02:47', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(28, 1, '2026-02-19', '13500.00', '2026-02-19 04:02:47', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(29, 2, '2026-02-19', '18000.00', '2026-02-19 04:02:47', '18000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(30, 3, '2026-02-19', '15700.00', '2026-02-19 04:02:47', '15700.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(31, 4, '2026-02-19', '135000.00', '2026-02-19 04:02:47', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(32, 4, '2026-02-19', '135000.00', '2026-02-19 04:02:47', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(33, 5, '2026-02-19', '35000.00', '2026-02-19 04:02:47', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(34, 6, '2026-02-19', '29000.00', '2026-02-19 04:02:47', '29000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(35, 12, '2026-02-19', '40000.00', '2026-02-19 04:02:47', '40000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(36, 7, '2026-02-19', '80000.00', '2026-02-19 04:02:47', '80000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(37, 8, '2026-02-19', '35000.00', '2026-02-19 04:02:47', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(38, 9, '2026-02-19', '38000.00', '2026-02-19 04:02:47', '38000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(39, 10, '2026-02-19', '15000.00', '2026-02-19 04:02:47', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(40, 11, '2026-02-19', '15000.00', '2026-02-19 04:02:47', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(41, 1, '2026-02-19', '14000.00', '2026-02-19 06:49:48', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(42, 1, '2026-02-19', '13500.00', '2026-02-19 06:49:48', '13750.00', '250.00', '1.82', '98.18', '0.00', '3.57', NULL, NULL),
(43, 2, '2026-02-19', '18000.00', '2026-02-19 06:49:48', '18000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(44, 3, '2026-02-19', '15700.00', '2026-02-19 06:49:48', '15700.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(45, 4, '2026-02-19', '135000.00', '2026-02-19 06:49:48', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(46, 4, '2026-02-19', '135000.00', '2026-02-19 06:49:48', '135000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(47, 5, '2026-02-19', '35000.00', '2026-02-19 06:49:48', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(48, 6, '2026-02-19', '29000.00', '2026-02-19 06:49:48', '29000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(49, 12, '2026-02-19', '40000.00', '2026-02-19 06:49:48', '40000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(50, 7, '2026-02-19', '80000.00', '2026-02-19 06:49:48', '80000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(51, 8, '2026-02-19', '35000.00', '2026-02-19 06:49:48', '35000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(52, 9, '2026-02-19', '38000.00', '2026-02-19 06:49:48', '38000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(53, 10, '2026-02-19', '15000.00', '2026-02-19 06:49:48', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL),
(54, 11, '2026-02-19', '15000.00', '2026-02-19 06:49:48', '15000.00', '0.00', '0.00', '100.00', '0.00', '0.00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(8, 'admin_fix', '$2y$12$zRDmp34qPHQgmotTnZWPZeLsC4F5f/JtI95HFwPixfaPYx6OFX9xa', 'admin', '2026-02-11 04:43:41'),
(9, 'onak', '$2y$12$ZSSDtewqRLx9TeKsSYGwQO8NzDeveKhjkU.H819G0EpwRd5aQBEgC', 'member', '2026-02-11 06:15:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_pokok`
--
ALTER TABLE `bahan_pokok`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `harga`
--
ALTER TABLE `harga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_harga_tanggal` (`tanggal`),
  ADD KEY `idx_harga_bahan` (`bahan_id`);

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
-- AUTO_INCREMENT for table `bahan_pokok`
--
ALTER TABLE `bahan_pokok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `harga`
--
ALTER TABLE `harga`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `harga`
--
ALTER TABLE `harga`
  ADD CONSTRAINT `harga_ibfk_1` FOREIGN KEY (`bahan_id`) REFERENCES `bahan_pokok` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
