<?php
$is_edit = isset($_GET['id']);
$id_bahan_baku = $is_edit ? $_GET['id'] : null;

$nama_bahan = '';
$satuan = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM bahan_baku WHERE id_bahan_baku = ?");
    $stmt->execute([$id_bahan_baku]);
    $bahan = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($bahan) {
        $nama_bahan = $bahan['nama_bahan'];
        $satuan = $bahan['satuan'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_bahan_post = $_POST['nama_bahan'];
    $satuan_post = $_POST['satuan'];

    try {
        if ($is_edit) {
            $stmt = $pdo->prepare("UPDATE bahan_baku SET nama_bahan = ?, satuan = ? WHERE id_bahan_baku = ?");
            $stmt->execute([$nama_bahan_post, $satuan_post, $id_bahan_baku]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO bahan_baku (nama_bahan, satuan) VALUES (?, ?)");
            $stmt->execute([$nama_bahan_post, $satuan_post]);
        }
        redirect(base_url('index.php?page=bahan_baku&status=sukses'));
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container-fluid py-3">
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0"><?php echo $is_edit ? 'Edit Data Master Bahan Baku' : 'Tambah Bahan Baku Baru'; ?></h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_bahan" class="form-label">Nama Bahan Baku</label>
                    <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="<?php echo htmlspecialchars($nama_bahan); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="satuan" class="form-label">Satuan Dasar</label>
                    <input type="text" class="form-control" id="satuan" name="satuan" value="<?php echo htmlspecialchars($satuan); ?>" required>
                    <div class="form-text">Masukkan satuan terkecil untuk perhitungan resep (Contoh: Gram, ML, Pcs).</div>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="<?php echo base_url('index.php?page=bahan_baku'); ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Simpan Perubahan' : 'Tambah Bahan'; ?></button>
            </div>
        </div>
    </form>
</div>