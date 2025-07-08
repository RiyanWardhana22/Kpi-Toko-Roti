<?php
$is_edit = isset($_GET['id']);
$id_produk = $is_edit ? $_GET['id'] : null;
$nama_produk = '';

if ($is_edit) {
            $stmt = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
            $stmt->execute([$id_produk]);
            $produk = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($produk) {
                        $nama_produk = $produk['nama_produk'];
            }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_produk_post = $_POST['nama_produk'];

            try {
                        if ($is_edit) {
                                    $stmt = $pdo->prepare("UPDATE produk SET nama_produk = ? WHERE id_produk = ?");
                                    $stmt->execute([$nama_produk_post, $id_produk]);
                        } else {
                                    $stmt = $pdo->prepare("INSERT INTO produk (nama_produk) VALUES (?)");
                                    $stmt->execute([$nama_produk_post]);
                        }
                        redirect(base_url('index.php?page=produk&status=sukses'));
            } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
            }
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800"><?php echo $is_edit ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h1>

            <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                        <div class="card-body">
                                    <form method="POST">
                                                <div class="mb-3">
                                                            <label for="nama_produk" class="form-label">Nama Produk</label>
                                                            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($nama_produk); ?>" required>
                                                </div>

                                                <a href="<?php echo base_url('index.php?page=produk'); ?>" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Simpan Perubahan' : 'Tambah Produk'; ?></button>
                                    </form>
                        </div>
            </div>
</div>