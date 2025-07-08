<?php
$is_edit = isset($_GET['id']);
$id_bahan_baku = $is_edit ? $_GET['id'] : null;

$nama_bahan = '';
$satuan = '';
$harga_beli = '';

if ($is_edit) {
            $stmt = $pdo->prepare("SELECT * FROM bahan_baku WHERE id_bahan_baku = ?");
            $stmt->execute([$id_bahan_baku]);
            $bahan = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($bahan) {
                        $nama_bahan = $bahan['nama_bahan'];
                        $satuan = $bahan['satuan'];
                        $harga_beli = $bahan['harga_beli'];
            }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_bahan_post = $_POST['nama_bahan'];
            $satuan_post = $_POST['satuan'];
            $harga_beli_post = $_POST['harga_beli'];

            try {
                        if ($is_edit) {
                                    $stmt = $pdo->prepare("UPDATE bahan_baku SET nama_bahan = ?, satuan = ?, harga_beli = ? WHERE id_bahan_baku = ?");
                                    $stmt->execute([$nama_bahan_post, $satuan_post, $harga_beli_post, $id_bahan_baku]);
                        } else {
                                    $stmt = $pdo->prepare("INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli) VALUES (?, ?, ?)");
                                    $stmt->execute([$nama_bahan_post, $satuan_post, $harga_beli_post]);
                        }
                        redirect(base_url('index.php?page=bahan_baku&status=sukses'));
            } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
            }
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800"><?php echo $is_edit ? 'Edit Bahan Baku' : 'Tambah Bahan Baku Baru'; ?></h1>

            <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                        <div class="card-body">
                                    <form method="POST">
                                                <div class="mb-3">
                                                            <label for="nama_bahan" class="form-label">Nama Bahan Baku</label>
                                                            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="<?php echo htmlspecialchars($nama_bahan); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="satuan" class="form-label">Satuan (Contoh: Kg, Gr, Liter, Pcs)</label>
                                                            <input type="text" class="form-control" id="satuan" name="satuan" value="<?php echo htmlspecialchars($satuan); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="harga_beli" class="form-label">Harga Beli (Opsional)</label>
                                                            <input type="number" step="0.01" class="form-control" id="harga_beli" name="harga_beli" value="<?php echo htmlspecialchars($harga_beli); ?>">
                                                </div>

                                                <a href="<?php echo base_url('index.php?page=bahan_baku'); ?>" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Simpan Perubahan' : 'Tambah Bahan Baku'; ?></button>
                                    </form>
                        </div>
            </div>
</div>