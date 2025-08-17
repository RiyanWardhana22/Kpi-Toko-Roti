-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 17, 2025 at 06:02 PM
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
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(1, 'Admin', 'Admin', '$2y$10$LFqcfW0kXKslc/t1IHSl4.XjIqFV9x8Gq1NiBRkUMClIADGb15iNu', 'Admin');

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
  MODIFY `id_perintah_kerja` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perintah_kerja_penggunaan_batch`
--
ALTER TABLE `perintah_kerja_penggunaan_batch`
  MODIFY `id_penggunaan` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `produk_jadi_batch`
--
ALTER TABLE `produk_jadi_batch`
  MODIFY `id_batch_produk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id_resep` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stok_batch`
--
ALTER TABLE `stok_batch`
  MODIFY `id_batch` int NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `_arsip_produksi_penggunaan_bahan_ibfk_2` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`);

--
-- Constraints for table `_arsip_produksi_rencana`
--
ALTER TABLE `_arsip_produksi_rencana`
  ADD CONSTRAINT `_arsip_produksi_rencana_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
