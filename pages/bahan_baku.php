<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url());
}

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(id_bahan_baku) FROM bahan_baku";
$params = [];
if (!empty($search_term)) {
            $count_sql .= " WHERE nama_bahan LIKE ?";
            $params[] = "%$search_term%";
}
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT * FROM bahan_baku";
if (!empty($search_term)) {
            $data_sql .= " WHERE nama_bahan LIKE ?";
}
$data_sql .= " ORDER BY id_bahan_baku DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($data_sql);
if (!empty($search_term)) {
            $stmt->bindValue(1, "%$search_term%");
}
$stmt->bindValue(count($params) - 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
$stmt->execute();
$bahan_bakus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Manajemen Data Bahan Baku</h1>

            <div class="d-flex justify-content-between mb-3">
                        <a href="<?php echo base_url('index.php?page=bahan_baku_form'); ?>" class="btn btn-primary">Tambah Bahan Baku</a>
                        <form action="" method="GET" class="d-flex">
                                    <input type="hidden" name="page" value="bahan_baku">
                                    <input type="text" name="q" class="form-control" placeholder="Cari nama bahan..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button type="submit" class="btn btn-info ms-2">Cari</button>
                        </form>
            </div>

            <div class="card shadow mb-4">
                        <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Bahan Baku</h6>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table table-bordered">
                                                            <thead>
                                                                        <tr>
                                                                                    <th>ID</th>
                                                                                    <th>Nama Bahan</th>
                                                                                    <th>Satuan</th>
                                                                                    <th>Harga Beli</th>
                                                                                    <th>Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($bahan_bakus)): ?>
                                                                                    <tr>
                                                                                                <td colspan="5" class="text-center">Belum ada data bahan baku.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php
                                                                                    $no = 1;
                                                                                    foreach ($bahan_bakus as $bahan): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo $no++ ?></td>
                                                                                                            <td><?php echo htmlspecialchars($bahan['nama_bahan']); ?></td>
                                                                                                            <td><?php echo htmlspecialchars($bahan['satuan']); ?></td>
                                                                                                            <td>Rp <?php echo number_format($bahan['harga_beli'], 2, ',', '.'); ?></td>
                                                                                                            <td>
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_form&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_hapus&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin?');">Hapus</a>
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
                                                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=bahan_baku&p=<?php echo $page - 1; ?>&q=<?php echo urlencode($search_term); ?>">Previous</a></li>
                                                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=bahan_baku&p=<?php echo $i; ?>&q=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a></li>
                                                                        <?php endfor; ?>
                                                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=bahan_baku&p=<?php echo $page + 1; ?>&q=<?php echo urlencode($search_term); ?>">Next</a></li>
                                                            </ul>
                                                </nav>
                                    <?php endif; ?>
                        </div>
            </div>
</div>