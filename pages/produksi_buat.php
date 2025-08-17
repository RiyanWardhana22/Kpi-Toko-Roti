<?php
// Menggunakan query asli yang sudah terbukti bisa menampilkan halaman di sistem Anda
$stmt = $pdo->query("
    SELECT p.id_produk, p.nama_produk 
    FROM produk p
    WHERE p.id_produk IN (SELECT DISTINCT id_produk FROM resep)
    ORDER BY p.nama_produk ASC
");
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0">Buat Perintah Kerja Produksi Baru</h6>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php?page=produksi_aksi_buat" method="POST">
                
                <div class="mb-3">
                    <label for="id_produk" class="form-label">Pilih Produk yang Akan Dibuat</label>
                    <select class="form-control" id="id_produk" name="id_produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produk_list as $produk): ?>
                            <option value="<?php echo $produk['id_produk']; ?>">
                                <?php echo htmlspecialchars($produk['nama_produk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="jumlah_direncanakan" class="form-label">Jumlah Direncanakan</label>
                    <input type="number" class="form-control" id="jumlah_direncanakan" name="jumlah_direncanakan" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="catatan" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="index.php?page=produksi_daftar" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan & Rencanakan Produksi</button>
                </div>

            </form>
        </div>
    </div>
</div>