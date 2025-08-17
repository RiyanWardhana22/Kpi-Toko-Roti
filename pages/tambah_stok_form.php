<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data mentah dari form
    $id_bahan_baku = $_POST['id_bahan_baku'];
    $jumlah_input = (float)$_POST['jumlah_input'];
    $satuan_input = $_POST['satuan_input'];
    $total_harga = (float)$_POST['total_harga'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $masa_simpan_hari = (int)$_POST['masa_simpan_hari'];
    $id_pengguna_input = $_SESSION['id_pengguna'];

    try {
        // Ambil satuan dasar dari bahan baku
        $stmt_bahan = $pdo->prepare("SELECT nama_bahan, satuan FROM bahan_baku WHERE id_bahan_baku = ?");
        $stmt_bahan->execute([$id_bahan_baku]);
        $bahan_info = $stmt_bahan->fetch(PDO::FETCH_ASSOC);
        $satuan_dasar = $bahan_info['satuan'];

        // 2. Lakukan Konversi ke Satuan Dasar (untuk internal)
        $jumlah_dikonversi = $jumlah_input;
        if ($satuan_input === 'KG' && $satuan_dasar === 'Gram') {
            $jumlah_dikonversi = $jumlah_input * 1000;
        } else if ($satuan_input === 'Liter' && $satuan_dasar === 'ML') {
            $jumlah_dikonversi = $jumlah_input * 1000;
        }
        // Tambahkan aturan konversi lain jika perlu

        // 3. Hitung Harga per Satuan Dasar
        $harga_per_satuan_dasar = ($jumlah_dikonversi > 0) ? $total_harga / $jumlah_dikonversi : 0;

        $pdo->beginTransaction();

        $nama_singkat = strtoupper(substr($bahan_info['nama_bahan'], 0, 3));
        $kode_batch = $nama_singkat . '-' . date('YmdHis');
        $tanggal_kadaluarsa = date('Y-m-d', strtotime($tanggal_masuk . ' + ' . $masa_simpan_hari . ' days'));

        $sql = "INSERT INTO stok_batch (
                    kode_batch, id_bahan_baku, 
                    jumlah_display, satuan_display, 
                    jumlah_dasar, sisa_dasar, 
                    harga_per_satuan_dasar, 
                    tanggal_masuk, tanggal_kadaluarsa, id_pengguna_input
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([
            $kode_batch,
            $id_bahan_baku,
            $jumlah_input,
            $satuan_input, // Simpan data display apa adanya
            $jumlah_dikonversi,
            $jumlah_dikonversi, // Simpan data terkonversi
            $harga_per_satuan_dasar,
            $tanggal_masuk,
            $tanggal_kadaluarsa,
            $id_pengguna_input
        ]);

        $pdo->commit();
        redirect(base_url('index.php?page=stok_harian&status=sukses'));
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Error Database: " . $e->getMessage();
    }
}

// Ambil daftar bahan baku untuk ditampilkan di dropdown
$stmt_bahan_all = $pdo->query("SELECT id_bahan_baku, nama_bahan, satuan FROM bahan_baku ORDER BY nama_bahan ASC");
$semua_bahan = $stmt_bahan_all->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0">Form Tambah Stok Bahan Baku</h6>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="id_bahan_baku" class="form-label">Nama Bahan Baku</label>
                    <select class="form-select" id="id_bahan_baku" name="id_bahan_baku" required>
                        <option value="">-- Pilih Bahan Baku --</option>
                        <?php foreach ($semua_bahan as $bahan): ?>
                            <option value="<?php echo $bahan['id_bahan_baku']; ?>">
                                <?php echo htmlspecialchars($bahan['nama_bahan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jumlah_input" class="form-label">Jumlah</label>
                        <input type="number" step="any" class="form-control" id="jumlah_input" name="jumlah_input" required placeholder="Contoh: 500">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="satuan_input" class="form-label">Satuan</label>
                        <select class="form-select" name="satuan_input" id="satuan_input">
                            <option>Gram</option>
                            <option>KG</option>
                            <option>Pcs</option>
                            <option>Liter</option>
                            <option>ML</option>
                            <option>Buah</option>
                            <option>Butir</option>
                            <option>Ikat</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="total_harga" class="form-label">Total Harga Pembelian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="any" class="form-control" id="total_harga" name="total_harga" required placeholder="Contoh: 5000">
                    </div>
                    <div class="form-text">Masukkan total harga sesuai nota untuk jumlah di atas.</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                        <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="masa_simpan_hari" class="form-label">Masa Simpan (Hari)</label>
                        <input type="number" class="form-control" id="masa_simpan_hari" name="masa_simpan_hari" required placeholder="Contoh: 90">
                    </div>
                </div>

                <div class="card-footer text-end bg-transparent border-0 px-0">
                    <a href="<?php echo base_url('index.php?page=stok_harian'); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Stok Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>