<?php
// Ambil semua data produk untuk pilihan dropdown
$stmt_produk = $pdo->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk ASC");
$semua_produk = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = $_POST['id_produk'];
    $target_produksi = $_POST['target_produksi'];
    $tanggal_produksi = $_POST['tanggal_produksi'];

    if (empty($id_produk) || empty($target_produksi) || empty($tanggal_produksi)) {
        $error = "Semua field wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO produksi_rencana (id_produk, target_produksi, tanggal_produksi) VALUES (?, ?, ?)");
            $stmt->execute([$id_produk, $target_produksi, $tanggal_produksi]);
            redirect(base_url('index.php?page=produksi&tanggal=' . $tanggal_produksi));
        } catch (PDOException $e) {
            $error = "Gagal menyimpan rencana: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0">Buat Rencana Produksi Baru</h6>
        </div>
        <form method="POST">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="id_produk" class="form-label">Pilih Produk</label>
                    <select class="form-select" id="id_produk" name="id_produk" required>
                        <option value="">-- Pilih Produk yang Akan Dibuat --</option>
                        <?php foreach ($semua_produk as $produk): ?>
                            <option value="<?php echo $produk['id_produk']; ?>"><?php echo htmlspecialchars($produk['nama_produk']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="target_produksi" class="form-label">Target Produksi (Pcs)</label>
                        <input type="number" class="form-control" id="target_produksi" name="target_produksi" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_produksi" class="form-label">Tanggal Produksi</label>
                        <input type="date" class="form-control" id="tanggal_produksi" name="tanggal_produksi" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="<?php echo base_url('index.php?page=produksi'); ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Rencana</button>
            </div>
        </form>
    </div>
</div>