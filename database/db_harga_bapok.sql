-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 20, 2026 at 06:46 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.3

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
(1, 'Beras', 'Premium', 'Kg', 0.00, '2026-02-18 04:44:33'),
(2, 'Gula', 'Pasir Kristal (Curah)', 'Kg', 0.00, '2026-02-18 04:44:33'),
(3, 'Minyak Goreng', 'Minyakita', 'Lt', 0.00, '2026-02-18 04:44:33'),
(4, 'Daging Sapi', 'Paha Depan', 'Kg', 0.00, '2026-02-18 04:44:33'),
(5, 'Daging Ayam', 'Ayam Broiler', 'Kg', 0.00, '2026-02-18 04:44:33'),
(6, 'Telur Ayam', 'Ras', 'Kg', 0.00, '2026-02-18 04:44:33'),
(7, 'Cabe Rawit', 'Merah', 'Kg', 0.00, '2026-02-18 04:44:33'),
(8, 'Bawang Merah', '-', 'Kg', 0.00, '2026-02-18 04:44:33'),
(9, 'Bawang Putih', 'Honan', 'Kg', 0.00, '2026-02-18 04:44:33'),
(10, 'Timun Sedang', '-', 'Kg', 0.00, '2026-02-18 04:44:33'),
(11, 'Kacang Kedelai', 'Eks Import', 'Kg', 0.00, '2026-02-18 04:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `harga`
--

CREATE TABLE `harga` (
  `id` int NOT NULL,
  `bahan_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `harga`
--

INSERT INTO `harga` (`id`, `bahan_id`, `tanggal`, `harga`, `created_at`) VALUES
(1, 1, '2026-02-18', 14000.00, '2026-02-18 04:44:33'),
(2, 1, '2026-02-18', 13500.00, '2026-02-18 04:44:33'),
(3, 2, '2026-02-18', 18000.00, '2026-02-18 04:44:33'),
(4, 3, '2026-02-18', 15700.00, '2026-02-18 04:44:33'),
(5, 4, '2026-02-18', 135000.00, '2026-02-18 04:44:33'),
(6, 4, '2026-02-18', 135000.00, '2026-02-18 04:44:33'),
(7, 5, '2026-02-18', 35000.00, '2026-02-18 04:44:33'),
(8, 6, '2026-02-18', 29000.00, '2026-02-18 04:44:33'),
(9, 7, '2026-02-18', 80000.00, '2026-02-18 04:44:33'),
(10, 8, '2026-02-18', 35000.00, '2026-02-18 04:44:33'),
(11, 9, '2026-02-18', 38000.00, '2026-02-18 04:44:33'),
(12, 10, '2026-02-18', 15000.00, '2026-02-18 04:44:33'),
(13, 11, '2026-02-18', 15000.00, '2026-02-18 04:44:33');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `harga`
--
ALTER TABLE `harga`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
