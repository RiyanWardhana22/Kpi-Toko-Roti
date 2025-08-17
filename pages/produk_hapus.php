<?php
// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'Admin') {
    exit('Akses ditolak.');
}

if (!isset($_GET['id'])) {
    redirect(base_url('index.php?page=produk'));
    exit();
}

$id_produk = (int)$_GET['id'];

try {
    // Cek apakah produk pernah ada di RENCANA produksi
    $stmt_check1 = $pdo->prepare("SELECT COUNT(*) FROM produksi_rencana WHERE id_produk = ?");
    $stmt_check1->execute([$id_produk]);
    if ($stmt_check1->fetchColumn() > 0) {
        // JIKA ADA, GAGALKAN HAPUS
        redirect(base_url('index.php?page=produk&status=gagal_hapus'));
        exit();
    }

    // Cek apakah produk pernah ada di LOG produksi
    $stmt_check2 = $pdo->prepare("SELECT COUNT(*) FROM produksi_log WHERE id_produk = ?");
    $stmt_check2->execute([$id_produk]);
    if ($stmt_check2->fetchColumn() > 0) {
        // JIKA ADA, GAGALKAN HAPUS
        redirect(base_url('index.php?page=produk&status=gagal_hapus'));
        exit();
    }

    // Jika aman (tidak ada di rencana atau log), baru lakukan penghapusan
    // ON DELETE CASCADE akan otomatis menghapus resep yang terhubung
    $stmt_delete = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
    $stmt_delete->execute([$id_produk]);

    redirect(base_url('index.php?page=produk&status=sukses_hapus'));
    exit();

} catch (PDOException $e) {
    // Tangani jika ada error database lain
    redirect(base_url('index.php?page=produk&status=gagal_hapus'));
    exit();
}
?>