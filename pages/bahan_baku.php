<?php
try {
            $stmt = $pdo->query("SELECT * FROM bahan_baku ORDER BY id_bahan_baku DESC");
            $bahan_bakus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $bahan_bakus = [];
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Manajemen Data Bahan Baku</h1>

            <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-<?php echo $_GET['status'] == 'gagal' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                                    <?php
                                    if ($_GET['status'] == 'sukses') echo "Data bahan baku berhasil ditambahkan/diperbarui!";
                                    if ($_GET['status'] == 'dihapus') echo "Data bahan baku berhasil dihapus!";
                                    if ($_GET['status'] == 'gagal') echo "Gagal menghapus data. Mungkin bahan baku ini sedang digunakan dalam resep.";
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php endif; ?>

            <a href="<?php echo base_url('index.php?page=bahan_baku_form'); ?>" class="btn btn-primary mb-3">
                        Tambah Bahan Baku Baru
            </a>

            <div class="card shadow mb-4">
                        <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Bahan Baku</h6>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table table-bordered" width="100%" cellspacing="0">
                                                            <thead>
                                                                        <tr>
                                                                                    <th>ID</th>
                                                                                    <th>Nama Bahan</th>
                                                                                    <th>Satuan</th>
                                                                                    <th>Harga Beli</th>
                                                                                    <th>Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($bahan_bakus)): ?>
                                                                                    <tr>
                                                                                                <td colspan="5" class="text-center">Belum ada data bahan baku.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php foreach ($bahan_bakus as $bahan): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo htmlspecialchars($bahan['id_bahan_baku']); ?></td>
                                                                                                            <td><?php echo htmlspecialchars($bahan['nama_bahan']); ?></td>
                                                                                                            <td><?php echo htmlspecialchars($bahan['satuan']); ?></td>
                                                                                                            <td>Rp <?php echo number_format($bahan['harga_beli'], 2, ',', '.'); ?></td>
                                                                                                            <td>
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_form&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_hapus&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus bahan baku ini?');">Hapus</a>
                                                                                                            </td>
                                                                                                </tr>
                                                                                    <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                            </tbody>
                                                </table>
                                    </div>
                        </div>
            </div>
</div>