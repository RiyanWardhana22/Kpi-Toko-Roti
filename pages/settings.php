<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url('index.php?page=dashboard&status=terlarang'));
}

$sukses = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                        $pdo->beginTransaction();
                        $stmt_nama = $pdo->prepare("UPDATE pengaturan SET nilai_pengaturan = ? WHERE nama_pengaturan = 'nama_website'");
                        $stmt_nama->execute([$_POST['nama_website']]);

                        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
                                    $file = $_FILES['favicon'];
                                    $target_dir = "assets/images/";
                                    $allowed_types = ['image/png', 'image/x-icon', 'image/jpeg', 'image/gif'];

                                    if (in_array($file['type'], $allowed_types)) {
                                                $filename = 'favicon_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                                                $target_file = $target_dir . $filename;

                                                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                                                            $current_favicon = $settings['favicon'];
                                                            if ($current_favicon && file_exists($target_dir . $current_favicon)) {
                                                                        if ($current_favicon != 'favicon.ico') {
                                                                                    unlink($target_dir . $current_favicon);
                                                                        }
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
                        $sukses = "Pengaturan berhasil diperbarui! Perubahan mungkin memerlukan refresh halaman (Ctrl+F5) untuk terlihat.";
                        $settings = load_settings($pdo);
            } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = "Terjadi kesalahan: " . $e->getMessage();
            }
}
?>

<div class="container-fluid py-3">
            <?php if ($sukses): ?>
                        <div class="alert alert-success"><?php echo $sukses; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                        <div class="card">
                                    <div class="card-header">
                                                <h6 class="m-0">Form Pengaturan Website</h6>
                                    </div>
                                    <div class="card-body">
                                                <div class="mb-3">
                                                            <label for="nama_website" class="form-label">Nama Website</label>
                                                            <input type="text" class="form-control" id="nama_website" name="nama_website" value="<?php echo htmlspecialchars($settings['nama_website']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                            <label for="favicon" class="form-label">Upload Favicon Baru</label>
                                                            <div class="d-flex align-items-center">
                                                                        <img src="<?php echo base_url('assets/images/' . htmlspecialchars($settings['favicon'])); ?>" alt="Favicon" class="me-3" style="width: 32px; height: 32px; border-radius: 4px;">
                                                                        <input type="file" class="form-control" id="favicon" name="favicon">
                                                            </div>
                                                            <small class="text-muted">Kosongkan jika tidak ingin mengubah favicon. Tipe file: .ico, .png, .jpg.</small>
                                                </div>
                                    </div>
                                    <div class="card-footer text-end">
                                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                                    </div>
                        </div>
            </form>
</div>