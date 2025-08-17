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
if (!empty($search_term)) {
            $stmt->bindValue(1, "%$search_term%");
            $stmt->bindValue(2, "%$search_term%");
}
$stmt->bindValue(count($params) - 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomor = ($page - 1) * $limit + 1;
?>

<div class="container-fluid py-3">
            <?php if (isset($_GET['status_hapus'])): ?>
                        <div class="alert alert-<?php echo $_GET['status_hapus'] == 'sukses' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $_GET['status_hapus'] == 'sukses' ? 'Pengguna berhasil dihapus!' : 'Gagal menghapus pengguna (Anda tidak bisa menghapus akun sendiri).'; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
            <?php endif; ?>

            <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                    <h6 class="m-0">Daftar Pengguna (Total: <?php echo $total_records; ?>)</h6>
                                    <div class="d-flex flex-wrap align-items-center">
                                                <form method="GET" class="d-flex me-2">
                                                            <input type="hidden" name="page" value="pengguna">
                                                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari pengguna..." value="<?php echo htmlspecialchars($search_term); ?>">
                                                            <button type="submit" class="btn btn-sm btn-info ms-2">Cari</button>
                                                </form>
                                                <a href="<?php echo base_url('index.php?page=pengguna_form'); ?>" class="btn btn-outline-primary btn-sm">+ Tambah</a>
                                    </div>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table-modern">
                                                            <thead>
                                                                        <tr>
                                                                                    <th style="width: 5%;">No</th>
                                                                                    <th>Nama Lengkap</th>
                                                                                    <th>Username</th>
                                                                                    <th class="text-center">Role</th>
                                                                                    <th class="text-center" style="width: 15%;">Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($users)): ?>
                                                                                    <tr>
                                                                                                <td colspan="5" class="text-center bg-light p-5">Tidak ada pengguna yang cocok dengan kriteria.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php foreach ($users as $user): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo $nomor++; ?></td>
                                                                                                            <td>
                                                                                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
                                                                                                            </td>
                                                                                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                                                                            <td class="text-center">
                                                                                                                        <?php
                                                                                                                        if ($user['role'] == 'Admin') {
                                                                                                                                    $badge_class = 'bg-primary';
                                                                                                                        } else {
                                                                                                                                    $badge_class = 'bg-secondary';
                                                                                                                        }
                                                                                                                        ?>
                                                                                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $user['role']; ?></span>
                                                                                                            </td>
                                                                                                            <td class="d-flex justify-content-center gap-2">
                                                                                                                        <a href="<?php echo base_url('index.php?page=pengguna_form&id=' . $user['id_pengguna']); ?>" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pencil"></i></a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=pengguna_hapus&id=' . $user['id_pengguna']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Anda yakin?');"><i class="fa-solid fa-trash"></i></a>
                                                                                                            </td>
                                                                                                </tr>
                                                                                    <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                            </tbody>
                                                </table>
                                    </div>

                                    <?php if ($total_pages > 1): ?>
                                                <nav class="mt-4">
                                                            <ul class="pagination justify-content-end">
                                                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=pengguna&p=<?php echo $page - 1; ?>&q=<?php echo urlencode($search_term); ?>">Previous</a></li>
                                                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=pengguna&p=<?php echo $i; ?>&q=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a></li>
                                                                        <?php endfor; ?>
                                                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=pengguna&p=<?php echo $page + 1; ?>&q=<?php echo urlencode($search_term); ?>">Next</a></li>
                                                            </ul>
                                                </nav>
                                    <?php endif; ?>
                        </div>
            </div>
</div>