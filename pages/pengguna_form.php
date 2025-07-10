<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url('index.php?page=dashboard&status=terlarang'));
}

$is_edit = isset($_GET['id']);
$id_pengguna = $is_edit ? $_GET['id'] : null;
$user_data = ['nama_lengkap' => '', 'username' => '', 'role' => 'Pegawai'];

if ($is_edit) {
            $stmt = $pdo->prepare("SELECT nama_lengkap, username, role FROM pengguna WHERE id_pengguna = ?");
            $stmt->execute([$id_pengguna]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama_lengkap'];
            $user = $_POST['username'];
            $pass = $_POST['password'];
            $role = $_POST['role'];
            try {
                        if ($is_edit) {
                                    if (!empty($pass)) {
                                                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                                                $stmt = $pdo->prepare("UPDATE pengguna SET nama_lengkap=?, username=?, password=?, role=? WHERE id_pengguna=?");
                                                $stmt->execute([$nama, $user, $hashed_pass, $role, $id_pengguna]);
                                    } else {
                                                $stmt = $pdo->prepare("UPDATE pengguna SET nama_lengkap=?, username=?, role=? WHERE id_pengguna=?");
                                                $stmt->execute([$nama, $user, $role, $id_pengguna]);
                                    }
                        } else {
                                    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
                                    $stmt = $pdo->prepare("INSERT INTO pengguna (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
                                    $stmt->execute([$nama, $user, $hashed_pass, $role]);
                        }
                        redirect(base_url('index.php?page=pengguna&status=sukses'));
            } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
            }
}
?>
<div class="container-fluid py-3">
            <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <form method="POST">
                        <div class="card">
                                    <div class="card-header">
                                                <h6 class="m-0"><?php echo $is_edit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h6>
                                    </div>
                                    <div class="card-body">
                                                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required></div>
                                                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" required></div>
                                                <div class="mb-3">
                                                            <label class="form-label">Password</label>
                                                            <input type="password" name="password" class="form-control" <?php if (!$is_edit) echo 'required'; ?>>
                                                            <?php if ($is_edit): ?><small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small><?php endif; ?>
                                                </div>
                                                <div class="mb-3">
                                                            <label class="form-label">Role</label>
                                                            <select name="role" class="form-control">
                                                                        <option value="Pegawai" <?php echo ($user_data['role'] == 'Pegawai') ? 'selected' : ''; ?>>Pegawai</option>
                                                                        <option value="Admin" <?php echo ($user_data['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                                            </select>
                                                </div>
                                    </div>
                                    <div class="card-footer text-end">
                                                <a href="<?php echo base_url('index.php?page=pengguna'); ?>" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Simpan Perubahan' : 'Tambah Pengguna'; ?></button>
                                    </div>
                        </div>
            </form>
</div>