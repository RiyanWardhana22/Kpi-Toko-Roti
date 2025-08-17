<?php
// Pastikan request adalah POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=produksi_daftar');
    exit;
}

$id_produk = $_POST['id_produk'] ?? null;
$jumlah_direncanakan = (int)($_POST['jumlah_direncanakan'] ?? 0);
$catatan = $_POST['catatan'] ?? null;

if (empty($id_produk) || $jumlah_direncanakan <= 0) {
    $_SESSION['error_message'] = "Data tidak valid. Silakan coba lagi.";
    header('Location: index.php?page=produksi_buat');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt_resep = $pdo->prepare("SELECT id_bahan_baku, jumlah FROM resep WHERE id_produk = ?");
    $stmt_resep->execute([$id_produk]);
    $resep = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resep)) {
        throw new Exception("Resep untuk produk ini tidak ditemukan.");
    }

    $batch_yang_digunakan = [];

    foreach ($resep as $bahan) {
        $id_bahan_baku = $bahan['id_bahan_baku'];
        $jumlah_dibutuhkan = $bahan['jumlah'] * $jumlah_direncanakan;
        
        // Menggunakan nama kolom `sisa_dasar` yang BENAR
        $stmt_stok = $pdo->prepare(
            "SELECT kode_batch, sisa_dasar FROM stok_batch 
             WHERE id_bahan_baku = ? AND sisa_dasar > 0 AND tanggal_kadaluarsa >= CURDATE()
             ORDER BY tanggal_masuk ASC"
        );
        $stmt_stok->execute([$id_bahan_baku]);
        $stok_tersedia = $stmt_stok->fetchAll(PDO::FETCH_ASSOC);

        $jumlah_terpenuhi = 0;
        foreach ($stok_tersedia as $batch) {
            // Menggunakan nama kolom `sisa_dasar` yang BENAR
            $stok_di_batch = $batch['sisa_dasar'];
            $ambil_dari_batch_ini = 0;
            
            if ($jumlah_terpenuhi < $jumlah_dibutuhkan) {
                $sisa_kebutuhan = $jumlah_dibutuhkan - $jumlah_terpenuhi;
                
                if ($stok_di_batch >= $sisa_kebutuhan) {
                    $ambil_dari_batch_ini = $sisa_kebutuhan;
                    $jumlah_terpenuhi += $ambil_dari_batch_ini;
                } else {
                    $ambil_dari_batch_ini = $stok_di_batch;
                    $jumlah_terpenuhi += $ambil_dari_batch_ini;
                }
                
                if ($ambil_dari_batch_ini > 0) {
                    $batch_yang_digunakan[] = [
                        'kode_batch' => $batch['kode_batch'],
                        'jumlah' => $ambil_dari_batch_ini
                    ];
                }
            }
        }
        
        if ($jumlah_terpenuhi < $jumlah_dibutuhkan) {
            $stmt_nama_bahan = $pdo->prepare("SELECT nama_bahan FROM bahan_baku WHERE id_bahan_baku = ?");
            $stmt_nama_bahan->execute([$id_bahan_baku]);
            $nama_bahan = $stmt_nama_bahan->fetchColumn();
            throw new Exception("Stok untuk '{$nama_bahan}' tidak cukup. Dibutuhkan: {$jumlah_dibutuhkan}, Tersedia: {$jumlah_terpenuhi}.");
        }
    }

    $stmt_pk = $pdo->prepare(
        "INSERT INTO perintah_kerja (id_produk, jumlah_direncanakan, status, catatan)
         VALUES (?, ?, 'Direncanakan', ?)"
    );
    $stmt_pk->execute([$id_produk, $jumlah_direncanakan, $catatan]);
    $id_perintah_kerja = $pdo->lastInsertId();

    $stmt_penggunaan = $pdo->prepare(
        "INSERT INTO perintah_kerja_penggunaan_batch (id_perintah_kerja, kode_batch_bahan, jumlah_digunakan)
         VALUES (?, ?, ?)"
    );
    foreach ($batch_yang_digunakan as $penggunaan) {
        $stmt_penggunaan->execute([
            $id_perintah_kerja,
            $penggunaan['kode_batch'],
            $penggunaan['jumlah']
        ]);
    }

    $pdo->commit();
    
    $_SESSION['success_message'] = "Perintah kerja berhasil dibuat dengan ID #{$id_perintah_kerja}.";
    header('Location: index.php?page=produksi_daftar');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Gagal membuat perintah kerja: " . $e->getMessage();
    header('Location: index.php?page=produksi_buat');
    exit;
}