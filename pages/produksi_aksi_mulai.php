<?php
// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=produksi_daftar');
    exit;
}

$id_perintah_kerja = filter_input(INPUT_POST, 'id_perintah_kerja', FILTER_VALIDATE_INT);
if (!$id_perintah_kerja) {
    $_SESSION['error_message'] = "ID Perintah Kerja tidak valid.";
    header('Location: index.php?page=produksi_daftar');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_cek = $pdo->prepare("SELECT status FROM perintah_kerja WHERE id_perintah_kerja = ? FOR UPDATE");
    $stmt_cek->execute([$id_perintah_kerja]);
    $status_sekarang = $stmt_cek->fetchColumn();

    if ($status_sekarang !== 'Direncanakan') {
        throw new Exception("Produksi ini sudah dimulai atau selesai.");
    }

    $stmt_bahan = $pdo->prepare("SELECT kode_batch_bahan, jumlah_digunakan FROM perintah_kerja_penggunaan_batch WHERE id_perintah_kerja = ?");
    $stmt_bahan->execute([$id_perintah_kerja]);
    $bahan_untuk_dikurangi = $stmt_bahan->fetchAll(PDO::FETCH_ASSOC);

    // ==================== PERBAIKAN DI SINI ====================
    // Mengganti `sisa_stok` menjadi `sisa_dasar`
    $stmt_update_stok = $pdo->prepare("UPDATE stok_batch SET sisa_dasar = sisa_dasar - ? WHERE kode_batch = ?");
    // =========================================================
    foreach ($bahan_untuk_dikurangi as $bahan) {
        $stmt_update_stok->execute([$bahan['jumlah_digunakan'], $bahan['kode_batch_bahan']]);
    }

    $stmt_update_pk = $pdo->prepare("UPDATE perintah_kerja SET status = 'Berlangsung', tanggal_dimulai = NOW() WHERE id_perintah_kerja = ?");
    $stmt_update_pk->execute([$id_perintah_kerja]);
    
    $pdo->commit();

    $_SESSION['success_message'] = "Produksi #{$id_perintah_kerja} berhasil dimulai dan stok telah dikurangi.";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Gagal memulai produksi: " . $e->getMessage();
}

header("Location: index.php?page=produksi_detail&id={$id_perintah_kerja}");
exit;