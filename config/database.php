<?php

/**
 * Mendefinisikan Alamat Dasar (Base URL) untuk seluruh aplikasi.
 * Sesuaikan '/Kpi-Toko-Roti/' dengan nama folder proyek Anda jika berbeda.
 * Tanda '/' di awal dan akhir SANGAT PENTING.
 */
define('BASE_URL', '/Kpi-Toko-Roti/');

// --- Sisa kode koneksi Anda (tidak ada yang diubah) ---
$host = 'localhost';
$dbname = 'kpi_toko_roti';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}