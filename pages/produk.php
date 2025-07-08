<?php
try {
            $stmt = $pdo->query("SELECT * FROM produk ORDER BY id_produk DESC");
            $produks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            $produks = [];
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Manajemen Data Produk</h1>

            <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php
                                    if ($_GET['status'] == 'sukses') echo "Data produk berhasil ditambahkan/diperbarui!";
                                    if ($_GET['status'] == 'dihapus') echo "Data produk berhasil dihapus!";
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php endif; ?>

            <a href="<?php echo base_url('index.php?page=produk_form'); ?>" class="btn btn-primary mb-3">
                        Tambah Produk Baru
            </a>

            <div class="card shadow mb-4">
                        <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Produk Roti</h6>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                                            <thead>
                                                                        <tr>
                                                                                    <th>ID</th>
                                                                                    <th>Nama Produk</th>
                                                                                    <th>Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($produks)): ?>
                                                                                    <tr>
                                                                                                <td colspan="3" class="text-center">Belum ada data produk.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php foreach ($produks as $produk): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo htmlspecialchars($produk['id_produk']); ?></td>
                                                                                                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                                                                                                            <td>
                                                                                                                        <a href="<?php echo base_url('index.php?page=resep&id_produk=' . $produk['id_produk']); ?>" class="btn btn-info btn-sm">Detail (Resep)</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=produk_form&id=' . $produk['id_produk']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=produk_hapus&id=' . $produk['id_produk']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus produk ini? Ini juga akan menghapus semua resep yang terkait.');">Hapus</a>
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