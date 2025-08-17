<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die('Akses ditolak.');
}
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?page=stok_harian');
    exit();
}

$id_batch = (int)$_GET['id'];

try {
    $stmt_get_kode = $pdo->prepare("SELECT kode_batch FROM stok_batch WHERE id_batch = ?");
    $stmt_get_kode->execute([$id_batch]);
    $kode_batch = $stmt_get_kode->fetchColumn();

    if ($kode_batch) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM perintah_kerja_penggunaan_batch WHERE kode_batch_bahan = ?");
        $stmt_check->execute([$kode_batch]);
        $usage_count = $stmt_check->fetchColumn();

        if ($usage_count > 0) {
            $_SESSION['pesan_error'] = "Gagal menghapus! Batch '$kode_batch' sudah dialokasikan untuk sebuah Perintah Kerja. Batalkan Perintah Kerja tersebut terlebih dahulu.";
        } else {
            $stmt_delete = $pdo->prepare("DELETE FROM stok_batch WHERE id_batch = ?");
            $stmt_delete->execute([$id_batch]);
            $_SESSION['pesan_sukses'] = "Batch stok berhasil dihapus.";
        }
    } else {
        $_SESSION['pesan_error'] = "Batch stok tidak ditemukan.";
    }
} catch (PDOException $e) {
    $_SESSION['pesan_error'] = "Terjadi kesalahan database: " . $e->getMessage();
}

session_write_close();
header('Location: ' . BASE_URL . 'index.php?page=stok_harian');
exit();
