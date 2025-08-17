<?php
// (Kode Awal Sama)
// ...
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // --- Menghapus data dari sistem PERINTAH KERJA (Baru) ---
    $pdo->exec("TRUNCATE TABLE `perintah_kerja_penggunaan_batch`;");
    $pdo->exec("TRUNCATE TABLE `produk_jadi_batch`;");
    $pdo->exec("TRUNCATE TABLE `perintah_kerja`;");

    // --- Menghapus data dari sistem PRODUKSI LOG (Lama) ---
    $pdo->exec("TRUNCATE TABLE `produksi_penggunaan_bahan`;");
    $pdo->exec("TRUNCATE TABLE `produksi_log`;");
    $pdo->exec("TRUNCATE TABLE `produksi_rencana`;");

    // Baris untuk menghapus stok bahan baku telah dihilangkan/dikomentari
    // $pdo->exec("TRUNCATE TABLE `stok_batch`;"); 
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    $_SESSION['success_message'] = "Semua data riwayat produksi berhasil direset. Stok bahan baku tidak diubah.";
    redirect(base_url('index.php?page=settings&tab=reset'));

} catch (PDOException $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    $_SESSION['error_message'] = "Gagal mereset data: " . $e->getMessage();
    redirect(base_url('index.php?page=settings&tab=reset'));
}
?>