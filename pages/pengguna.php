<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url('index.php?page=dashboard&status=terlarang'));
}

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 20;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(id_pengguna) FROM pengguna";
$params = [];
if (!empty($search_term)) {
            $count_sql .= " WHERE nama_lengkap LIKE ? OR username LIKE ?";
            $params[] = "%$search_term%";
            $params[] = "%$search_term%";
}
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT id_pengguna, nama_lengkap, username, role FROM pengguna";
if (!empty($search_term)) {
            $data_sql .= " WHERE nama_lengkap LIKE ? OR username LIKE ?";
}
$data_sql .= " ORDER BY id_pengguna ASC LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($data_sql);
$stmt->bindValue(count($params) - 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params), $offset, PDO::PARAM_INT);

if (!empty($search_term)) {
            $stmt->bindValue(1, "%$search_term%");
            $stmt->bindValue(2, "%$search_term%");
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Manajemen Pengguna</h1>
            <p class="mb-4">Cari, lihat, dan kelola akun pengguna yang terdaftar di sistem.</p>

            <?php if (isset($_GET['status_hapus'])): ?>
                        <div class="alert alert-<?php echo $_GET['status_hapus'] == 'sukses' ? 'success' : 'danger'; ?>">
                                    <?php echo $_GET['status_hapus'] == 'sukses' ? 'Pengguna berhasil dihapus!' : 'Gagal menghapus pengguna (Anda tidak bisa menghapus akun sendiri).'; ?>
                        </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between mb-3">
                        <a href="<?php echo base_url('index.php?page=pengguna_form'); ?>" class="btn btn-primary">Tambah Pengguna</a>
                        <form action="" method="GET" class="d-flex">
                                    <input type="hidden" name="page" value="pengguna">
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama atau username..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button type="submit" class="btn btn-info ms-2">Cari</button>
                        </form>
            </div>

            <div class="card shadow mb-4">
                        <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna</h6>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table table-bordered">
                                                            <thead>
                                                                        <tr>
                                                                                    <th>ID</th>
                                                                                    <th>Nama Lengkap</th>
                                                                                    <th>Username</th>
                                                                                    <th>Role</th>
                                                                                    <th>Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($users)): ?>
                                                                                    <tr>
                                                                                                <td colspan="5" class="text-center">Tidak ada pengguna yang cocok dengan kriteria.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php
                                                                                    $no = 1;
                                                                                    foreach ($users as $user): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo $no++ ?></td>
                                                                                                            <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                                                                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                                                                            <td><span class="badge bg-info text-dark"><?php echo $user['role']; ?></span></td>
                                                                                                            <td>
                                                                                                                        <a href="<?php echo base_url('index.php?page=pengguna_form&id=' . $user['id_pengguna']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=pengguna_hapus&id=' . $user['id_pengguna']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin?');">Hapus</a>
                                                                                                            </td>
                                                                                                </tr>
                                                                                    <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                            </tbody>
                                                </table>
                                    </div>

                                    <?php if ($total_pages > 1): ?>
                                                <nav>
                                                            <ul class="pagination justify-content-end">
                                                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                                                    <a class="page-link" href="?page=pengguna&p=<?php echo $page - 1; ?>&q=<?php echo urlencode($search_term); ?>">Previous</a>
                                                                        </li>

                                                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                                                                <a class="page-link" href="?page=pengguna&p=<?php echo $i; ?>&q=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a>
                                                                                    </li>
                                                                        <?php endfor; ?>

                                                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                                                                    <a class="page-link" href="?page=pengguna&p=<?php echo $page + 1; ?>&q=<?php echo urlencode($search_term); ?>">Next</a>
                                                                        </li>
                                                            </ul>
                                                </nav>
                                    <?php endif; ?>
                        </div>
            </div>
</div>