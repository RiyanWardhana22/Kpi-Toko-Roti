<?php
$stmt_produk = $pdo->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
$produks = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tanggal_produksi = $_POST['tanggal_produksi'];
            $id_produk = $_POST['id_produk'];
            $target_produksi = $_POST['target_produksi'];

            if (!empty($tanggal_produksi) && !empty($id_produk) && !empty($target_produksi)) {
                        try {
                                    $stmt_cek = $pdo->prepare("SELECT id_rencana FROM produksi_rencana WHERE tanggal_produksi = ? AND id_produk = ?");
                                    $stmt_cek->execute([$tanggal_produksi, $id_produk]);
                                    if ($stmt_cek->fetch()) {
                                                $error = "Rencana produksi untuk produk ini pada tanggal tersebut sudah ada.";
                                    } else {
                                                $stmt = $pdo->prepare("INSERT INTO produksi_rencana (tanggal_produksi, id_produk, target_produksi) VALUES (?, ?, ?)");
                                                $stmt->execute([$tanggal_produksi, $id_produk, $target_produksi]);
                                                redirect(base_url('index.php?page=produksi&status=sukses'));
                                    }
                        } catch (PDOException $e) {
                                    $error = "Error: " . $e->getMessage();
                        }
            } else {
                        $error = "Semua field wajib diisi.";
            }
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Input Rencana Produksi Harian</h1>

            <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                        <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Form Rencana Produksi</h6>
                        </div>
                        <div class="card-body">
                                    <form method="POST">
                                                <div class="mb-3">
                                                            <label for="tanggal_produksi" class="form-label">Tanggal Produksi</label>
                                                            <input type="date" class="form-control" id="tanggal_produksi" name="tanggal_produksi" value="<?php echo date('Y-m-d'); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="id_produk" class="form-label">Pilih Produk</label>
                                                            <select class="form-control" id="id_produk" name="id_produk" required>
                                                                        <option value="">-- Pilih Produk --</option>
                                                                        <?php foreach ($produks as $produk): ?>
                                                                                    <option value="<?php echo $produk['id_produk']; ?>"><?php echo htmlspecialchars($produk['nama_produk']); ?></option>
                                                                        <?php endforeach; ?>
                                                            </select>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="target_produksi" class="form-label">Target Produksi (Jumlah)</label>
                                                            <input type="number" class="form-control" id="target_produksi" name="target_produksi" placeholder="Contoh: 100" required>
                                                </div>

                                                <a href="<?php echo base_url('index.php?page=produksi'); ?>" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Rencana</button>
                                    </form>
                        </div>
            </div>
</div>