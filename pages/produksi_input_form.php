<?php
// pages/produksi_input_form.php (atau nama file Anda)

// Ambil data rencana produksi yang akan diinput hasilnya
$id_rencana = isset($_GET['id_rencana']) ? (int)$_GET['id_rencana'] : 0;
$stmt_rencana = $pdo->prepare("
    SELECT pr.*, p.nama_produk, p.masa_simpan_hari 
    FROM produksi_rencana pr 
    JOIN produk p ON pr.id_produk = p.id_produk 
    WHERE pr.id_rencana = ?
");
$stmt_rencana->execute([$id_rencana]);
$rencana = $stmt_rencana->fetch(PDO::FETCH_ASSOC);

if (!$rencana) {
    redirect(base_url('index.php?page=produksi_daftar&status=notfound'));
    exit();
}

// --- LOGIKA UTAMA: Proses form saat disubmit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah_sukses = (int)$_POST['jumlah_sukses'];
    $jumlah_gagal = (int)$_POST['jumlah_gagal'];
    $alasan_gagal = $_POST['alasan_gagal'];
    $kode_batch_input = trim($_POST['kode_batch']); // <-- MENGAMBIL KODE BATCH DARI FORM
    $id_produk = $rencana['id_produk'];
    $id_pengguna = $_SESSION['id_pengguna'];
    
    try {
        $pdo->beginTransaction();

        // 1. Simpan log produksi utama ke tabel 'produksi_log'
        $stmt_log = $pdo->prepare("
            INSERT INTO produksi_log (id_rencana, tanggal_aktual, id_produk, jumlah_sukses, jumlah_gagal, alasan_gagal, id_pengguna_input)
            VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt_log->execute([$id_rencana, $id_produk, $jumlah_sukses, $jumlah_gagal, $alasan_gagal, $id_pengguna]);
        $id_log_baru = $pdo->lastInsertId();

        // 2. Jika ada produk yang sukses dibuat, lakukan pengurangan stok bahan baku (Logika FEFO Anda)
        if ($jumlah_sukses > 0) {
            $stmt_resep = $pdo->prepare("SELECT id_bahan_baku, jumlah FROM resep WHERE id_produk = ?");
            $stmt_resep->execute([$id_produk]);
            $resep_produk = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resep_produk as $bahan) {
                $id_bahan_baku = $bahan['id_bahan_baku'];
                $total_dibutuhkan = $bahan['jumlah'] * $jumlah_sukses;

                $stmt_batch = $pdo->prepare("SELECT * FROM stok_batch WHERE id_bahan_baku = ? AND sisa_dasar > 0 ORDER BY tanggal_kadaluarsa ASC");
                $stmt_batch->execute([$id_bahan_baku]);
                $batch_tersedia = $stmt_batch->fetchAll(PDO::FETCH_ASSOC);

                $total_stok_bahan_ini = array_sum(array_column($batch_tersedia, 'sisa_dasar'));
                if ($total_stok_bahan_ini < $total_dibutuhkan) {
                    throw new Exception("Stok untuk bahan baku (ID: $id_bahan_baku) tidak mencukupi.");
                }

                foreach ($batch_tersedia as $batch) {
                    if ($total_dibutuhkan <= 0) break;
                    $ambil_dari_batch = min($batch['sisa_dasar'], $total_dibutuhkan);

                    $stmt_update_batch = $pdo->prepare("UPDATE stok_batch SET sisa_dasar = sisa_dasar - ? WHERE id_batch = ?");
                    $stmt_update_batch->execute([$ambil_dari_batch, $batch['id_batch']]);

                    $stmt_penggunaan = $pdo->prepare("INSERT INTO produksi_penggunaan_bahan (id_log, id_bahan_baku, id_batch, jumlah_aktual) VALUES (?, ?, ?, ?)");
                    $stmt_penggunaan->execute([$id_log_baru, $id_bahan_baku, $batch['id_batch'], $ambil_dari_batch]);

                    $total_dibutuhkan -= $ambil_dari_batch;
                }
            }
        }
        
        // 3. (INTI FITUR) Membuat batch produk jadi dengan kode custom
        if ($jumlah_sukses > 0) {
            $masa_simpan = (int)$rencana['masa_simpan_hari'];
            $tanggal_produksi = date('Y-m-d H:i:s');
            $tanggal_kadaluarsa = date('Y-m-d', strtotime($tanggal_produksi . " +{$masa_simpan} days"));

            $stmt_batch_jadi = $pdo->prepare(
                "INSERT INTO produk_jadi_batch 
                (id_produk, id_perintah_kerja, kode_batch, jumlah_produksi, sisa_stok, tanggal_produksi, tanggal_kadaluarsa)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt_batch_jadi->execute([
                $id_produk,
                $id_rencana, // Menggunakan id_rencana sebagai referensi
                $kode_batch_input, // <-- MENGGUNAKAN KODE DARI INPUT ADMIN
                $jumlah_sukses,
                $jumlah_sukses, // Saat dibuat, sisa stok = jumlah produksi
                $tanggal_produksi,
                $tanggal_kadaluarsa
            ]);
        }
        
        $pdo->commit();
        redirect(base_url('index.php?page=produksi_daftar&status=sukses_input'));

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal memproses: " . $e->getMessage();
    }
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header"><h6 class="m-0">Input Hasil Produksi</h6></div>
        <form method="POST">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Produk:</strong>
                        <p><?php echo htmlspecialchars($rencana['nama_produk']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Target Produksi:</strong>
                        <p><?php echo htmlspecialchars($rencana['target_produksi']); ?> Pcs</p>
                    </div>
                </div>
                <hr>
                
                <div class="mb-3">
                    <label for="kode_batch" class="form-label fw-bold">Kode Batch Produksi (Custom)</label>
                    <input type="text" class="form-control" id="kode_batch" name="kode_batch" placeholder="Contoh: RC-160825-PAGI" required>
                    <div class="form-text">Masukkan kode unik buatan Anda untuk melacak batch produksi ini.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jumlah_sukses" class="form-label">Jumlah Sukses</label>
                        <input type="number" class="form-control" id="jumlah_sukses" name="jumlah_sukses" value="0" min="0" required>
                        <div class="form-text">Jumlah produk yang berhasil dibuat sesuai standar.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jumlah_gagal" class="form-label">Jumlah Gagal</label>
                        <input type="number" class="form-control" id="jumlah_gagal" name="jumlah_gagal" value="0" min="0" required>
                        <div class="form-text">Jumlah produk yang gagal/rusak.</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="alasan_gagal" class="form-label">Alasan Gagal (Jika Ada)</label>
                    <textarea class="form-control" id="alasan_gagal" name="alasan_gagal" rows="3"></textarea>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="<?php echo base_url('index.php?page=produksi_daftar'); ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Hasil Produksi</button>
            </div>
        </form>
    </div>
</div>