-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 16, 2025 at 12:39 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.14

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
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bahan_baku`
--

INSERT INTO `bahan_baku` (`id_bahan_baku`, `nama_bahan`, `satuan`, `stok`, `stok_minimum`) VALUES
(9, 'Tepung Roti', 'Kg', 0, 0),
(10, 'Selai Coklat', 'Gram', 0, 0),
(11, 'Telur', 'Butir', 0, 0),
(12, 'Stroberi', 'Buah', 0, 0);

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
(6, 'oka', 'oka123', '$2y$10$x.RJadQ.Le4lW8KLuuMgNOWP/42Zb5YqoyHNVEL8S7dh.RqnTW/eC', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `perintah_kerja`
--

CREATE TABLE `perintah_kerja` (
  `id_perintah_kerja` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah_direncanakan` int NOT NULL,
  `jumlah_sukses` int DEFAULT NULL,
  `jumlah_gagal` int DEFAULT NULL,
  `tanggal_dibuat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_dimulai` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `status` enum('Direncanakan','Berlangsung','Selesai','Dibatalkan') NOT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `perintah_kerja`
--

INSERT INTO `perintah_kerja` (`id_perintah_kerja`, `id_produk`, `jumlah_direncanakan`, `jumlah_sukses`, `jumlah_gagal`, `tanggal_dibuat`, `tanggal_dimulai`, `tanggal_selesai`, `status`, `catatan`) VALUES
(1, 15, 5, 5, 0, '2025-08-14 16:00:03', '2025-08-14 16:00:35', '2025-08-14 16:14:43', 'Selesai', 'Dijual'),
(2, 15, 7, 5, 2, '2025-08-14 16:23:36', '2025-08-14 16:23:39', '2025-08-14 16:23:45', 'Selesai', 'stok hari ini'),
(3, 14, 5, 4, 1, '2025-08-14 17:00:33', '2025-08-14 17:00:41', '2025-08-14 17:00:45', 'Selesai', 'Siap hari ini'),
(4, 15, 2, 1, 1, '2025-08-16 17:08:53', '2025-08-16 19:12:03', '2025-08-16 19:12:13', 'Selesai', '1'),
(5, 15, 1, 1, 0, '2025-08-16 18:57:53', '2025-08-16 19:02:54', '2025-08-16 19:03:02', 'Selesai', '1'),
(6, 15, 1, 1, 0, '2025-08-16 19:03:10', '2025-08-16 19:03:45', '2025-08-16 19:03:57', 'Selesai', '1'),
(7, 14, 1, 1, 0, '2025-08-16 19:06:31', '2025-08-16 19:06:36', '2025-08-16 19:10:20', 'Selesai', '1'),
(8, 13, 1, 1, 0, '2025-08-16 19:12:37', '2025-08-16 19:12:40', '2025-08-16 19:12:48', 'Selesai', '1'),
(9, 13, 2, 1, 1, '2025-08-16 19:30:57', '2025-08-16 19:31:01', '2025-08-16 19:31:09', 'Selesai', 'siapkan sekarang');

-- --------------------------------------------------------

--
-- Table structure for table `perintah_kerja_penggunaan_batch`
--

CREATE TABLE `perintah_kerja_penggunaan_batch` (
  `id_penggunaan` int NOT NULL,
  `id_perintah_kerja` int NOT NULL,
  `kode_batch_bahan` varchar(50) NOT NULL,
  `jumlah_digunakan` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `perintah_kerja_penggunaan_batch`
--

INSERT INTO `perintah_kerja_penggunaan_batch` (`id_penggunaan`, `id_perintah_kerja`, `kode_batch_bahan`, `jumlah_digunakan`) VALUES
(1, 1, 'SEL-20250814085806', '100.00'),
(2, 1, 'TEP-20250814085935', '5.00'),
(3, 1, 'TEL-20250814085900', '5.00'),
(4, 2, 'SEL-20250814085806', '140.00'),
(5, 2, 'TEP-20250814085935', '7.00'),
(6, 2, 'TEL-20250814085900', '7.00'),
(7, 3, 'TEP-20250814085935', '5.00'),
(8, 3, 'TEL-20250814085900', '8.00'),
(9, 3, 'TEL-20250814100008', '2.00'),
(10, 3, 'STR-20250814085825', '5.00'),
(11, 4, 'SEL-20250814085806', '40.00'),
(12, 4, 'TEP-20250814085935', '2.00'),
(13, 4, 'TEL-20250814100008', '2.00'),
(14, 5, 'SEL-20250814085806', '20.00'),
(15, 5, 'TEP-20250814085935', '1.00'),
(16, 5, 'TEL-20250814100008', '1.00'),
(17, 6, 'SEL-20250814085806', '20.00'),
(18, 6, 'TEP-20250814085935', '1.00'),
(19, 6, 'TEL-20250814100008', '1.00'),
(20, 7, 'TEP-20250814085935', '1.00'),
(21, 7, 'TEL-20250814100008', '2.00'),
(22, 7, 'STR-20250814085825', '1.00'),
(23, 8, 'TEP-20250814085935', '1.00'),
(24, 8, 'SEL-20250814085806', '20.00'),
(25, 8, 'TEL-20250814100008', '2.00'),
(26, 9, 'TEP-20250814085935', '2.00'),
(27, 9, 'SEL-20250814085806', '40.00'),
(28, 9, 'TEL-20250814100008', '2.00'),
(29, 9, 'TEL-20250816123038', '2.00');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `masa_simpan_hari` int NOT NULL DEFAULT '0' COMMENT 'Umur simpan produk dalam hari',
  `catatan` text,
  `foto_produk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `masa_simpan_hari`, `catatan`, `foto_produk`) VALUES
(13, 'Roti Coklat', 1, NULL, NULL),
(14, 'Kue Stroberi', 3, NULL, NULL),
(15, 'Donat', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produk_jadi_batch`
--

CREATE TABLE `produk_jadi_batch` (
  `id_batch_produk` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_perintah_kerja` int DEFAULT NULL,
  `kode_batch` varchar(50) NOT NULL COMMENT 'Kode custom dari input pengguna',
  `jumlah_produksi` int NOT NULL,
  `sisa_stok` int NOT NULL,
  `tanggal_produksi` datetime NOT NULL,
  `tanggal_kadaluarsa` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_jadi_batch`
--

INSERT INTO `produk_jadi_batch` (`id_batch_produk`, `id_produk`, `id_perintah_kerja`, `kode_batch`, `jumlah_produksi`, `sisa_stok`, `tanggal_produksi`, `tanggal_kadaluarsa`) VALUES
(6, 15, 1, 'PJ-20250814091443-15', 5, 5, '2025-08-14 09:14:43', '2025-08-17'),
(7, 15, 2, 'PJ-20250814092345-15', 5, 5, '2025-08-14 09:23:45', '2025-08-15'),
(8, 14, 3, 'PJ-20250814100045-14', 4, 4, '2025-08-14 10:00:45', '2025-08-17'),
(9, 15, 5, 'PJ-20250816120302-15', 1, 1, '2025-08-16 12:03:02', '2025-08-17'),
(10, 15, 6, 'PJ-20250816120357-15', 1, 1, '2025-08-16 12:03:57', '2025-08-17'),
(11, 14, 7, 'PJ-20250816121020-14', 1, 1, '2025-08-16 12:10:20', '2025-08-19'),
(12, 15, 4, '2211', 1, 1, '2025-08-16 12:12:13', '2025-08-17'),
(13, 13, 8, '2007', 1, 1, '2025-08-16 12:12:48', '2025-08-17'),
(14, 13, 9, '2233', 1, 1, '2025-08-16 12:31:09', '2025-08-17');

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `id_resep` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_bahan_baku` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`id_resep`, `id_produk`, `id_bahan_baku`, `jumlah`) VALUES
(5, 13, 9, '1.00'),
(6, 13, 10, '20.00'),
(7, 13, 11, '2.00'),
(8, 14, 9, '1.00'),
(9, 14, 11, '2.00'),
(10, 14, 12, '1.00'),
(14, 15, 10, '20.00'),
(15, 15, 9, '1.00'),
(16, 15, 11, '1.00');

-- --------------------------------------------------------

--
-- Table structure for table `stok_batch`
--

CREATE TABLE `stok_batch` (
  `id_batch` int NOT NULL,
  `kode_batch` varchar(50) NOT NULL,
  `id_bahan_baku` int NOT NULL,
  `jumlah_display` varchar(50) NOT NULL,
  `satuan_display` varchar(50) NOT NULL,
  `jumlah_dasar` decimal(10,2) NOT NULL,
  `sisa_dasar` decimal(10,2) NOT NULL,
  `harga_per_satuan_dasar` decimal(10,2) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_kadaluarsa` date NOT NULL,
  `id_pengguna_input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `stok_batch`
--

INSERT INTO `stok_batch` (`id_batch`, `kode_batch`, `id_bahan_baku`, `jumlah_display`, `satuan_display`, `jumlah_dasar`, `sisa_dasar`, `harga_per_satuan_dasar`, `tanggal_masuk`, `tanggal_kadaluarsa`, `id_pengguna_input`) VALUES
(1, 'SEL-20250814085806', 10, '1000', 'Gram', '1000.00', '620.00', '25.00', '2025-08-14', '2025-08-21', 6),
(2, 'STR-20250814085825', 12, '15', 'Buah', '15.00', '9.00', '2333.33', '2025-08-14', '2025-08-17', 6),
(3, 'TEL-20250814085900', 11, '20', 'Butir', '20.00', '0.00', '2150.00', '2025-08-14', '2025-08-21', 6),
(4, 'TEP-20250814085935', 9, '15000', 'Gram', '15000.00', '14975.00', '3.00', '2025-08-14', '2025-08-21', 6),
(5, 'TEL-20250814100008', 11, '12', 'Butir', '12.00', '0.00', '2000.00', '2025-08-14', '2025-08-21', 6),
(6, 'TEL-20250816123038', 11, '20', 'Butir', '20.00', '18.00', '2000.00', '2025-08-16', '2025-08-23', 6);

-- --------------------------------------------------------

--
-- Table structure for table `_arsip_produksi_log`
--

CREATE TABLE `_arsip_produksi_log` (
  `id_log` int NOT NULL,
  `id_rencana` int DEFAULT NULL,
  `tanggal_aktual` datetime NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah_sukses` int NOT NULL,
  `jumlah_gagal` int NOT NULL,
  `alasan_gagal` text,
  `id_pengguna_input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_arsip_produksi_penggunaan_bahan`
--

CREATE TABLE `_arsip_produksi_penggunaan_bahan` (
  `id_penggunaan` int NOT NULL,
  `id_log` int NOT NULL,
  `id_bahan_baku` int NOT NULL,
  `id_batch` int NOT NULL,
  `jumlah_aktual` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_arsip_produksi_rencana`
--

CREATE TABLE `_arsip_produksi_rencana` (
  `id_rencana` int NOT NULL,
  `tanggal_produksi` date NOT NULL,
  `id_produk` int NOT NULL,
  `target_produksi` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Indexes for table `perintah_kerja`
--
ALTER TABLE `perintah_kerja`
  ADD PRIMARY KEY (`id_perintah_kerja`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `perintah_kerja_penggunaan_batch`
--
ALTER TABLE `perintah_kerja_penggunaan_batch`
  ADD PRIMARY KEY (`id_penggunaan`),
  ADD KEY `id_perintah_kerja` (`id_perintah_kerja`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `produk_jadi_batch`
--
ALTER TABLE `produk_jadi_batch`
  ADD PRIMARY KEY (`id_batch_produk`),
  ADD KEY `idx_produk` (`id_produk`),
  ADD KEY `idx_log_produksi` (`id_perintah_kerja`),
  ADD KEY `idx_kadaluarsa` (`tanggal_kadaluarsa`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`);

--
-- Indexes for table `stok_batch`
--
ALTER TABLE `stok_batch`
  ADD PRIMARY KEY (`id_batch`),
  ADD UNIQUE KEY `kode_batch` (`kode_batch`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`),
  ADD KEY `id_pengguna_input` (`id_pengguna_input`);

--
-- Indexes for table `_arsip_produksi_log`
--
ALTER TABLE `_arsip_produksi_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_rencana` (`id_rencana`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_pengguna_input` (`id_pengguna_input`);

--
-- Indexes for table `_arsip_produksi_penggunaan_bahan`
--
ALTER TABLE `_arsip_produksi_penggunaan_bahan`
  ADD PRIMARY KEY (`id_penggunaan`),
  ADD KEY `id_log` (`id_log`),
  ADD KEY `id_bahan_baku` (`id_bahan_baku`),
  ADD KEY `id_batch` (`id_batch`);

--
-- Indexes for table `_arsip_produksi_rencana`
--
ALTER TABLE `_arsip_produksi_rencana`
  ADD PRIMARY KEY (`id_rencana`),
  ADD KEY `id_produk` (`id_produk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  MODIFY `id_bahan_baku` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- AUTO_INCREMENT for table `perintah_kerja`
--
ALTER TABLE `perintah_kerja`
  MODIFY `id_perintah_kerja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `perintah_kerja_penggunaan_batch`
--
ALTER TABLE `perintah_kerja_penggunaan_batch`
  MODIFY `id_penggunaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `produk_jadi_batch`
--
ALTER TABLE `produk_jadi_batch`
  MODIFY `id_batch_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id_resep` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stok_batch`
--
ALTER TABLE `stok_batch`
  MODIFY `id_batch` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `_arsip_produksi_log`
--
ALTER TABLE `_arsip_produksi_log`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `_arsip_produksi_penggunaan_bahan`
--
ALTER TABLE `_arsip_produksi_penggunaan_bahan`
  MODIFY `id_penggunaan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `_arsip_produksi_rencana`
--
ALTER TABLE `_arsip_produksi_rencana`
  MODIFY `id_rencana` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `perintah_kerja`
--
ALTER TABLE `perintah_kerja`
  ADD CONSTRAINT `perintah_kerja_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `perintah_kerja_penggunaan_batch`
--
ALTER TABLE `perintah_kerja_penggunaan_batch`
  ADD CONSTRAINT `perintah_kerja_penggunaan_batch_ibfk_1` FOREIGN KEY (`id_perintah_kerja`) REFERENCES `perintah_kerja` (`id_perintah_kerja`);

--
-- Constraints for table `produk_jadi_batch`
--
ALTER TABLE `produk_jadi_batch`
  ADD CONSTRAINT `fk_pjb_perintah_kerja` FOREIGN KEY (`id_perintah_kerja`) REFERENCES `perintah_kerja` (`id_perintah_kerja`),
  ADD CONSTRAINT `fk_pjb_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `resep`
--
ALTER TABLE `resep`
  ADD CONSTRAINT `resep_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE,
  ADD CONSTRAINT `resep_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`);

--
-- Constraints for table `stok_batch`
--
ALTER TABLE `stok_batch`
  ADD CONSTRAINT `stok_batch_ibfk_1` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`),
  ADD CONSTRAINT `stok_batch_ibfk_2` FOREIGN KEY (`id_pengguna_input`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `_arsip_produksi_log`
--
ALTER TABLE `_arsip_produksi_log`
  ADD CONSTRAINT `_arsip_produksi_log_ibfk_1` FOREIGN KEY (`id_rencana`) REFERENCES `_arsip_produksi_rencana` (`id_rencana`),
  ADD CONSTRAINT `_arsip_produksi_log_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `_arsip_produksi_log_ibfk_3` FOREIGN KEY (`id_pengguna_input`) REFERENCES `pengguna` (`id_pengguna`);

--
-- Constraints for table `_arsip_produksi_penggunaan_bahan`
--
ALTER TABLE `_arsip_produksi_penggunaan_bahan`
  ADD CONSTRAINT `_arsip_produksi_penggunaan_bahan_ibfk_1` FOREIGN KEY (`id_log`) REFERENCES `_arsip_produksi_log` (`id_log`) ON DELETE CASCADE,
  ADD CONSTRAINT `_arsip_produksi_penggunaan_bahan_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`),
  ADD CONSTRAINT `_arsip_produksi_penggunaan_bahan_ibfk_3` FOREIGN KEY (`id_batch`) REFERENCES `stok_batch` (`id_batch`);

--
-- Constraints for table `_arsip_produksi_rencana`
--
ALTER TABLE `_arsip_produksi_rencana`
  ADD CONSTRAINT `_arsip_produksi_rencana_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
