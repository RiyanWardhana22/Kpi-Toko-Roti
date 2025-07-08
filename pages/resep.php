<?php
if (!isset($_GET['id_produk'])) {
            redirect(base_url('index.php?page=produk'));
}

$id_produk = $_GET['id_produk'];

$stmt_produk = $pdo->prepare("SELECT nama_produk FROM produk WHERE id_produk = ?");
$stmt_produk->execute([$id_produk]);
$produk = $stmt_produk->fetch(PDO::FETCH_ASSOC);

if (!$produk) {
            redirect(base_url('index.php?page=produk'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_bahan'])) {
            $id_bahan_baku = $_POST['id_bahan_baku'];
            $jumlah_standar = $_POST['jumlah_standar'];

            if (!empty($id_bahan_baku) && !empty($jumlah_standar)) {
                        try {
                                    $stmt = $pdo->prepare("INSERT INTO resep (id_produk, id_bahan_baku, jumlah_standar) VALUES (?, ?, ?)");
                                    $stmt->execute([$id_produk, $id_bahan_baku, $jumlah_standar]);
                                    redirect(base_url('index.php?page=resep&id_produk=' . $id_produk . '&status=sukses_tambah'));
                        } catch (PDOException $e) {
                                    $error = "Gagal menambahkan bahan: " . $e->getMessage();
                        }
            }
}

$stmt_bahan = $pdo->query("SELECT id_bahan_baku, nama_bahan FROM bahan_baku ORDER BY nama_bahan ASC");
$semua_bahan_baku = $stmt_bahan->fetchAll(PDO::FETCH_ASSOC);

$stmt_resep = $pdo->prepare("
    SELECT r.id_resep, bb.nama_bahan, r.jumlah_standar, bb.satuan 
    FROM resep r
    JOIN bahan_baku bb ON r.id_bahan_baku = bb.id_bahan_baku
    WHERE r.id_produk = ?
");
$stmt_resep->execute([$id_produk]);
$resep_produk = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Manajemen Resep untuk:</h1>
            <h2 class="h4 mb-4 text-primary"><?php echo htmlspecialchars($produk['nama_produk']); ?></h2>

            <?php if (isset($_GET['status'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php
                                    if ($_GET['status'] == 'sukses_tambah') echo "Bahan baku berhasil ditambahkan ke resep!";
                                    if ($_GET['status'] == 'sukses_hapus') echo "Bahan baku berhasil dihapus dari resep!";
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="row">
                        <div class="col-lg-4">
                                    <div class="card shadow mb-4">
                                                <div class="card-header py-3">
                                                            <h6 class="m-0 font-weight-bold text-primary">Tambah Bahan ke Resep</h6>
                                                </div>
                                                <div class="card-body">
                                                            <form method="POST">
                                                                        <div class="mb-3">
                                                                                    <label for="id_bahan_baku" class="form-label">Pilih Bahan Baku</label>
                                                                                    <select class="form-control" id="id_bahan_baku" name="id_bahan_baku" required>
                                                                                                <option value="">-- Pilih Bahan --</option>
                                                                                                <?php foreach ($semua_bahan_baku as $bahan): ?>
                                                                                                            <option value="<?php echo $bahan['id_bahan_baku']; ?>"><?php echo htmlspecialchars($bahan['nama_bahan']); ?></option>
                                                                                                <?php endforeach; ?>
                                                                                    </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                                    <label for="jumlah_standar" class="form-label">Jumlah Standar</label>
                                                                                    <input type="number" step="0.001" class="form-control" id="jumlah_standar" name="jumlah_standar" placeholder="Contoh: 0.5" required>
                                                                                    <small class="form-text text-muted">Gunakan satuan yang sesuai (misal: jika satuan Kg, 0.5 berarti 500gr).</small>
                                                                        </div>
                                                                        <button type="submit" name="tambah_bahan" class="btn btn-primary">Tambah</button>
                                                            </form>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-lg-8">
                                    <div class="card shadow mb-4">
                                                <div class="card-header py-3">
                                                            <h6 class="m-0 font-weight-bold text-primary">Resep Saat Ini (Bill of Materials)</h6>
                                                </div>
                                                <div class="card-body">
                                                            <div class="table-responsive">
                                                                        <table class="table table-bordered">
                                                                                    <thead>
                                                                                                <tr>
                                                                                                            <th>Nama Bahan Baku</th>
                                                                                                            <th>Jumlah Standar</th>
                                                                                                            <th>Satuan</th>
                                                                                                            <th>Aksi</th>
                                                                                                </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                                <?php if (empty($resep_produk)): ?>
                                                                                                            <tr>
                                                                                                                        <td colspan="4" class="text-center">Resep masih kosong.</td>
                                                                                                            </tr>
                                                                                                <?php else: ?>
                                                                                                            <?php foreach ($resep_produk as $item): ?>
                                                                                                                        <tr>
                                                                                                                                    <td><?php echo htmlspecialchars($item['nama_bahan']); ?></td>
                                                                                                                                    <td><?php echo htmlspecialchars($item['jumlah_standar']); ?></td>
                                                                                                                                    <td><?php echo htmlspecialchars($item['satuan']); ?></td>
                                                                                                                                    <td>
                                                                                                                                                <a href="<?php echo base_url('index.php?page=resep_hapus&id_resep=' . $item['id_resep'] . '&id_produk=' . $id_produk); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus bahan ini dari resep?');">Hapus</a>
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
            </div>
            <a href="<?php echo base_url('index.php?page=produk'); ?>" class="btn btn-secondary">Kembali ke Daftar Produk</a>
</div>