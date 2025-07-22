<?php
if (!isset($_GET['id_rencana'])) {
    redirect(base_url('index.php?page=produksi'));
}
$id_rencana = $_GET['id_rencana'];

$stmt = $pdo->prepare("SELECT pr.id_produk, p.nama_produk, pr.target_produksi FROM produksi_rencana pr JOIN produk p ON pr.id_produk = p.id_produk WHERE pr.id_rencana = ?");
$stmt->execute([$id_rencana]);
$rencana = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$rencana) {
    redirect(base_url('index.php?page=produksi'));
}

$stmt_resep = $pdo->prepare("SELECT r.id_bahan_baku, bb.nama_bahan, r.jumlah_standar, bb.satuan FROM resep r JOIN bahan_baku bb ON r.id_bahan_baku = bb.id_bahan_baku WHERE r.id_produk = ?");
$stmt_resep->execute([$rencana['id_produk']]);
$resep = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah_sukses = $_POST['jumlah_sukses'];
    $jumlah_gagal = $_POST['jumlah_gagal'];
    $alasan_gagal = $_POST['alasan_gagal'];
    $penggunaan_bahan = $_POST['penggunaan_bahan'];

    $pdo->beginTransaction();
    try {
        $id_pengguna_saat_ini = $_SESSION['id_pengguna'];
        $stmt_log = $pdo->prepare("INSERT INTO produksi_log (id_rencana, tanggal_aktual, id_produk, jumlah_sukses, jumlah_gagal, alasan_gagal, id_pengguna_input) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
        $stmt_log->execute([$id_rencana, $rencana['id_produk'], $jumlah_sukses, $jumlah_gagal, $alasan_gagal, $id_pengguna_saat_ini]);
        $id_log = $pdo->lastInsertId();
        $stmt_bahan = $pdo->prepare("INSERT INTO produksi_penggunaan_bahan (id_log, id_bahan_baku, jumlah_aktual) VALUES (?, ?, ?)");
        foreach ($penggunaan_bahan as $id_bahan_baku => $jumlah_aktual) {
            if (!empty($jumlah_aktual)) {
                $stmt_bahan->execute([$id_log, $id_bahan_baku, $jumlah_aktual]);
            }
        }
        $pdo->commit();
        redirect(base_url('index.php?page=produksi&status=input_sukses'));
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
    }
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <p class="text-muted mb-2">Input Hasil Produksi untuk: <strong><?php echo htmlspecialchars($rencana['nama_produk']); ?></strong> (Target: <?php echo htmlspecialchars($rencana['target_produksi']); ?>)</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <h6 class="mb-3 fw-bold">Hasil Produksi</h6>
                            <div class="mb-3">
                                <label for="jumlah_sukses" class="form-label">Jumlah Produk Berhasil</label>
                                <input type="number" class="form-control" id="jumlah_sukses" name="jumlah_sukses" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_gagal" class="form-label">Jumlah Produk Gagal</label>
                                <input type="number" class="form-control" id="jumlah_gagal" name="jumlah_gagal" value="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="alasan_gagal" class="form-label">Alasan Gagal (Jika ada)</label>
                                <textarea class="form-control" id="alasan_gagal" name="alasan_gagal" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h6 class="mb-3 fw-bold">Penggunaan Bahan Baku Aktual</h6>
                            <?php if (empty($resep)): ?>
                                <div class="alert alert-warning">Resep untuk produk ini belum diatur.</div>
                            <?php else: ?>
                                <?php foreach ($resep as $item): ?>
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <?php echo htmlspecialchars($item['nama_bahan']); ?>
                                            <small class="text-muted">(Standar: <?php echo "{$item['jumlah_standar']} {$item['satuan']}"; ?>)</small>
                                        </label>
                                        <input type="number" step="0.001" class="form-control" name="penggunaan_bahan[<?php echo $item['id_bahan_baku']; ?>]" placeholder="Jumlah aktual dalam <?php echo htmlspecialchars($item['satuan']); ?>" required>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?php echo base_url('index.php?page=produksi'); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm" <?php if (empty($resep)) echo 'disabled'; ?>>Simpan Hasil</button>
                </div>
            </div>
        </form>
    </div>
</div>