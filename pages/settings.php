<?php
if ($_SESSION['role'] !== 'Admin') {
    redirect(base_url('index.php?page=dashboard&status=terlarang'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pengaturan'])) {
    try {
        $pdo->beginTransaction();
        $stmt_nama = $pdo->prepare("UPDATE pengaturan SET nilai_pengaturan = ? WHERE nama_pengaturan = 'nama_website'");
        $stmt_nama->execute([$_POST['nama_website']]);

        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['favicon'];
            $target_dir = "assets/images/";
            $allowed_types = ['image/png', 'image/x-icon', 'image/jpeg', 'image/gif', 'image/vnd.microsoft.icon'];

            if (in_array($file['type'], $allowed_types)) {
                $filename = 'favicon_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $target_file = $target_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $current_favicon = $settings['favicon'];
                    if ($current_favicon && file_exists($target_dir . $current_favicon) && !in_array($current_favicon, ['favicon.png', 'favicon.ico'])) {
                        unlink($target_dir . $current_favicon);
                    }
                    $stmt_favicon = $pdo->prepare("UPDATE pengaturan SET nilai_pengaturan = ? WHERE nama_pengaturan = 'favicon'");
                    $stmt_favicon->execute([$filename]);
                } else {
                    throw new Exception("Gagal memindahkan file yang di-upload.");
                }
            } else {
                throw new Exception("Tipe file favicon tidak diizinkan.");
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Pengaturan berhasil diperbarui! Perubahan mungkin memerlukan refresh halaman (Ctrl+F5) untuk terlihat.";
        $settings = load_settings($pdo);
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    redirect(base_url('index.php?page=settings&tab=pengaturan'));
    exit;
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pengaturan';
?>

<div class="container-fluid py-3 px-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-3">
            <?php echo $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger mb-3">
            <?php echo $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab === 'pengaturan' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=settings&tab=pengaturan'); ?>">Pengaturan Website</a>
                </li>
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_tab === 'reset' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=settings&tab=reset'); ?>">Reset Data Transaksi</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade <?php echo $active_tab === 'pengaturan' ? 'show active' : ''; ?>" id="pengaturan">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="simpan_pengaturan" value="1">
                        <div class="mb-3">
                            <label for="nama_website" class="form-label">Nama Website</label>
                            <input type="text" class="form-control" id="nama_website" name="nama_website" value="<?php echo htmlspecialchars($settings['nama_website']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="favicon" class="form-label">Upload Favicon Baru</label>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo base_url('assets/images/' . htmlspecialchars($settings['favicon'])); ?>" alt="Favicon" class="me-3" style="width: 32px; height: 32px; border-radius: 4px; border: 1px solid #ddd;">
                                <input type="file" class="form-control" id="favicon" name="favicon">
                            </div>
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah favicon. Tipe file yang diizinkan: .ico, .png, .jpg.</small>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>

                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <div class="tab-pane fade <?php echo $active_tab === 'reset' ? 'show active' : ''; ?>" id="reset">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="m-0"><i class="fa-solid fa-triangle-exclamation"></i> Zona Berbahaya</h6>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Reset Seluruh Data Transaksi</h5>
                                <p class="card-text">Aksi ini akan menghapus <strong>SEMUA</strong> data transaksi (Perintah Kerja, Stok Produk Jadi, Log Produksi Lama, dll). Data master dan <strong>STOK BAHAN BAKU</strong> tidak akan terhapus. Aksi ini tidak bisa dibatalkan.</p>
                                <button type="button" class="btn btn-danger" onclick="confirmReset()">Reset Data Sekarang</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmReset() {
        const confirmationText = 'RESET';
        const userInput = prompt(`Ini adalah aksi yang sangat berbahaya dan tidak bisa dibatalkan.\nUntuk melanjutkan, ketik "${confirmationText}" di bawah ini:`);

        if (userInput === confirmationText) {
            window.location.href = '<?php echo base_url("index.php?page=reset_data"); ?>';
        } else if (userInput !== null) {
            alert('Teks konfirmasi salah. Proses reset dibatalkan.');
        }
    }
</script>