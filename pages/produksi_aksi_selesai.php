<?php
// pages/produksi_aksi_selesai.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=produksi_daftar');
    exit;
}

// Ambil semua data dari form, termasuk kode batch custom
$id_perintah_kerja = filter_input(INPUT_POST, 'id_perintah_kerja', FILTER_VALIDATE_INT);
$jumlah_sukses = filter_input(INPUT_POST, 'jumlah_sukses', FILTER_VALIDATE_INT);
$jumlah_gagal = filter_input(INPUT_POST, 'jumlah_gagal', FILTER_VALIDATE_INT);
// ===================================================================
// --- MODIFIKASI 1: Ambil data 'kode_batch' dari form ---
// ===================================================================
$kode_batch_input = trim(filter_input(INPUT_POST, 'kode_batch', FILTER_SANITIZE_STRING));


if ($id_perintah_kerja === false || $jumlah_sukses === null || $jumlah_gagal === null || $jumlah_sukses < 0 || $jumlah_gagal < 0 || empty($kode_batch_input)) {
    $_SESSION['error_message'] = "Data input tidak valid. Pastikan semua field terisi dengan benar.";
    // Arahkan kembali ke halaman detail jika ada error
    $redirect_url = $id_perintah_kerja ? "index.php?page=produksi_detail&id={$id_perintah_kerja}" : "index.php?page=produksi_daftar";
    header("Location: " . $redirect_url);
    exit;
}

try {
    $pdo->beginTransaction();

    // Cek status perintah kerja
    $stmt_cek = $pdo->prepare("SELECT id_produk, jumlah_direncanakan, status FROM perintah_kerja WHERE id_perintah_kerja = ? FOR UPDATE");
    $stmt_cek->execute([$id_perintah_kerja]);
    $pk = $stmt_cek->fetch(PDO::FETCH_ASSOC);

    if (!$pk) {
        throw new Exception("Perintah kerja tidak ditemukan.");
    }
    if ($pk['status'] !== 'Berlangsung') {
        throw new Exception("Produksi ini belum dimulai atau sudah selesai.");
    }
    // Validasi ini mungkin perlu disesuaikan jika jumlah produksi boleh kurang dari rencana
    // if (($jumlah_sukses + $jumlah_gagal) > $pk['jumlah_direncanakan']) {
    //     throw new Exception("Total jumlah Sukses dan Gagal tidak boleh melebihi jumlah yang direncanakan.");
    // }

    // Update status perintah kerja
    $stmt_update = $pdo->prepare(
        "UPDATE perintah_kerja 
         SET jumlah_sukses = ?, jumlah_gagal = ?, status = 'Selesai', tanggal_selesai = NOW() 
         WHERE id_perintah_kerja = ?"
    );
    $stmt_update->execute([$jumlah_sukses, $jumlah_gagal, $id_perintah_kerja]);

    // Jika ada produk sukses, buat batch produk jadi
    if ($jumlah_sukses > 0) {
        $id_produk = $pk['id_produk'];

        // Ambil data masa simpan dari tabel produk
        $stmt_produk = $pdo->prepare("SELECT masa_simpan_hari FROM produk WHERE id_produk = ?");
        $stmt_produk->execute([$id_produk]);
        $masa_simpan = (int)$stmt_produk->fetchColumn();
        
        // Fallback jika masa_simpan_hari tidak diatur
        if ($masa_simpan <= 0) {
            $masa_simpan = 1; // Default 1 hari
        }

        // Hitung tanggal kadaluarsa
        $tanggal_produksi = date('Y-m-d H:i:s');
        $tanggal_kadaluarsa_pj = date('Y-m-d', strtotime("+$masa_simpan days"));
        
        // Siapkan query untuk insert ke produk jadi batch
        $stmt_stok_jadi = $pdo->prepare(
            "INSERT INTO produk_jadi_batch (id_produk, id_perintah_kerja, kode_batch, jumlah_produksi, sisa_stok, tanggal_produksi, tanggal_kadaluarsa)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        // ===================================================================
        // --- MODIFIKASI 2: Gunakan '$kode_batch_input' dari form ---
        // ===================================================================
        $stmt_stok_jadi->execute([
            $id_produk,
            $id_perintah_kerja,
            $kode_batch_input, // Menggunakan kode batch dari input admin, bukan yang dibuat otomatis
            $jumlah_sukses,
            $jumlah_sukses,
            $tanggal_produksi,
            $tanggal_kadaluarsa_pj
        ]);
    }

    $pdo->commit();
    $_SESSION['success_message'] = "Produksi #{$id_perintah_kerja} berhasil diselesaikan.";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Gagal menyelesaikan produksi: " . $e->getMessage();
}

header("Location: index.php?page=produksi_detail&id={$id_perintah_kerja}");
exit;