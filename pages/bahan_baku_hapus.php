<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die('Akses ditolak.');
}

if (isset($_GET['id'])) {
    $id_bahan_baku = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM bahan_baku WHERE id_bahan_baku = ?");
        $stmt->execute([$id_bahan_baku]);

        $_SESSION['pesan_sukses'] = "Bahan baku berhasil dihapus.";
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['pesan_error'] = "Gagal menghapus! Bahan baku ini sedang digunakan dalam satu atau lebih resep.";
        } else {
            $_SESSION['pesan_error'] = "Terjadi kesalahan pada database: " . $e->getMessage();
        }
    }
}

require_once 'config/database.php';
header('Location: ' . BASE_URL . 'index.php?page=bahan_baku');
exit();
