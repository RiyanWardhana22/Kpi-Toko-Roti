<?php
// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['role'] !== 'Admin') {
    exit('Akses ditolak.');
}

if (!isset($_GET['id'])) {
    redirect(base_url('index.php?page=stok_harian'));
    exit();
}

$id_batch = (int)$_GET['id'];

try {
    // Langkah 1: Periksa apakah batch ini pernah digunakan di produksi
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM produksi_penggunaan_bahan WHERE id_batch = ?");
    $stmt_check->execute([$id_batch]);
    $usage_count = $stmt_check->fetchColumn();

    if ($usage_count > 0) {
        // JIKA SUDAH PERNAH DIPAKAI: Jangan hapus, kirim notifikasi gagal
        redirect(base_url('index.php?page=stok_harian&status=gagal_hapus_digunakan'));
        exit();
    }

    // Langkah 2: Jika belum pernah dipakai, lanjutkan proses hapus
    $stmt_delete = $pdo->prepare("DELETE FROM stok_batch WHERE id_batch = ?");
    $stmt_delete->execute([$id_batch]);

    // Kirim notifikasi sukses
    redirect(base_url('index.php?page=stok_harian&status=sukses_hapus'));
    exit();

} catch (PDOException $e) {
    // Tangani jika ada error database lain
    redirect(base_url('index.php?page=stok_harian&status=gagal'));
    exit();
}
?>