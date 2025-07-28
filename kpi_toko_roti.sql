-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 28, 2025 at 07:59 AM
-- Server version: 8.0.42
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kpi_toko_roti`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan_baku`
--

CREATE TABLE `bahan_baku` (
  `id_bahan_baku` int NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `harga_beli` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan_baku`
--

INSERT INTO `bahan_baku` (`id_bahan_baku`, `nama_bahan`, `satuan`, `harga_beli`) VALUES
(1, 'Gula Pasir', 'KG', '19000.00'),
(2, 'Tepung', 'KG', '7000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id_pengaturan` int NOT NULL,
  `nama_pengaturan` varchar(100) NOT NULL,
  `nilai_pengaturan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id_pengaturan`, `nama_pengaturan`, `nilai_pengaturan`) VALUES
(1, 'nama_website', 'Silmarils Cookies Dessert'),
(2, 'judul_default', 'Silmarils Cookies Dessert'),
(3, 'favicon', 'favicon.png');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Pegawai') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `password`, `role`) VALUES
(1, 'Riyan Wardhana', 'riyan22', '$2y$10$pKUsDDgRkbxg7nUlAF6.L.ncxbiaz46pa/mRyKzg2QnbpT1yo8V7G', 'Admin'),
(6, 'oka', 'oka123', '$2y$10$zhNt6eI.2DgujBzSaFBAPu/JXmrCZ8ixMrPYy/YfmFrqJBvhj4PdK', 'Pegawai');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `catatan` text,
  `foto_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `catatan`, `foto_produk`) VALUES
(1, 'Kue Ulang Tahun', NULL, NULL),
(2, 'Kue Kacang', '', NULL),
(3, 'Roti Strawbery', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produksi_log`
--

CREATE TABLE `produksi_log` (
  `id_log` int NOT NULL,
  `id_rencana` int DEFAULT NULL,
  `tanggal_aktual` datetime NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah_sukses` int NOT NULL,
  `jumlah_gagal` int NOT NULL,
  `alasan_gagal` text,
  `id_pengguna_input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produksi_log`
--

INSERT INTO `produksi_log` (`id_log`, `id_rencana`, `tanggal_aktual`, `id_produk`, `jumlah_sukses`, `jumlah_gagal`, `alasan_gagal`, `id_pengguna_input`) VALUES
(3, 4, '2025-07-08 21:43:24', 1, 4, 2, 'Gosong', 1),
(4, 5, '2025-07-08 22:26:23', 2, 1, 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `produksi_penggunaan_bahan`
--

CREATE TABLE `produksi_penggunaan_bahan` (
  `id_penggunaan` int NOT NULL,
  `id_log` int NOT NULL,
  `id_bahan_baku` int NOT NULL,
  `jumlah_aktual` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produksi_penggunaan_bahan`
--

INSERT INTO `produksi_penggunaan_bahan` (`id_penggunaan`, `id_log`, `id_bahan_baku`, `jumlah_aktual`) VALUES
(4, 3, 1, '0.5'),
(5, 3, 2, '5'),
(6, 4, 1, '1'),
(7, 4, 2, '1');

-- --------------------------------------------------------

--
-- Table structure for table `produksi_rencana`
--

CREATE TABLE `produksi_rencana` (
  `id_rencana` int NOT NULL,
  `tanggal_produksi` date NOT NULL,
  `id_produk` int NOT NULL,
  `target_produksi` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produksi_rencana`
--

INSERT INTO `produksi_rencana` (`id_rencana`, `tanggal_produksi`, `id_produk`, `target_produksi`) VALUES
(4, '2025-07-08', 1, 6),
(5, '2025-07-08', 2, 1),
(6, '2025-07-10', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `id_resep` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_bahan_baku` int NOT NULL,
  `jumlah_standar` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`id_resep`, `id_produk`, `id_bahan_baku`, `jumlah_standar`) VALUES
(4, 1, 1, '0.5'),
(5, 1, 2, '5'),
(6, 2, 1, '1'),
(7, 2, 2, '1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  ADD PRIMARY KEY (`id_bahan_baku`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id_pengaturan`),
  ADD UNIQUE KEY `nama_pengaturan` (`nama_pengaturan`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `produksi_log`
--
ALTER TABLE `produksi_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_rencana` (`id_rencana`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_pengguna_input` (`id_pengguna_input`);

--
-- Indexes for table `produksi_penggunaan_bahan`
--
ALTER TABLE `produksi_penggunaan_bahan`
  ADD PRIMARY KEY (`id_penggunaan`),
  ADD KEY `id_log` (`id_log`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`);

--
-- Indexes for table `produksi_rencana`
--
ALTER TABLE `produksi_rencana`
  ADD PRIMARY KEY (`id_rencana`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  MODIFY `id_bahan_baku` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id_pengaturan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produksi_log`
--
ALTER TABLE `produksi_log`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produksi_penggunaan_bahan`
--
ALTER TABLE `produksi_penggunaan_bahan`
  MODIFY `id_penggunaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produksi_rencana`
--
ALTER TABLE `produksi_rencana`
  MODIFY `id_rencana` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id_resep` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `produksi_log`
--
ALTER TABLE `produksi_log`
  ADD CONSTRAINT `produksi_log_ibfk_1` FOREIGN KEY (`id_rencana`) REFERENCES `produksi_rencana` (`id_rencana`),
  ADD CONSTRAINT `produksi_log_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `produksi_log_ibfk_3` FOREIGN KEY (`id_pengguna_input`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `produksi_penggunaan_bahan`
--
ALTER TABLE `produksi_penggunaan_bahan`
  ADD CONSTRAINT `produksi_penggunaan_bahan_ibfk_1` FOREIGN KEY (`id_log`) REFERENCES `produksi_log` (`id_log`) ON DELETE CASCADE,
  ADD CONSTRAINT `produksi_penggunaan_bahan_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`);

--
-- Constraints for table `produksi_rencana`
--
ALTER TABLE `produksi_rencana`
  ADD CONSTRAINT `produksi_rencana_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `resep`
--
ALTER TABLE `resep`
  ADD CONSTRAINT `resep_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `resep_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
