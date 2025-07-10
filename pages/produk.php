<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url());
}

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(id_produk) FROM produk";
$params = [];
if (!empty($search_term)) {
            $count_sql .= " WHERE nama_produk LIKE ?";
            $params[] = "%$search_term%";
}
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$data_sql = "SELECT * FROM produk";
if (!empty($search_term)) {
            $data_sql .= " WHERE nama_produk LIKE ?";
}
$data_sql .= " ORDER BY id_produk DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($data_sql);
if (!empty($search_term)) {
            $stmt->bindValue(1, "%$search_term%");
}
$stmt->bindValue(count($params) - 1, $limit, PDO::PARAM_INT);
$stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
$stmt->execute();
$produks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomor = ($page - 1) * $limit + 1;
?>

<div class="container-fluid py-3">
            <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                    <h6 class="m-0">Daftar Produk</h6>
                                    <div class="d-flex flex-wrap align-items-center">
                                                <form method="GET" class="d-flex me-2">
                                                            <input type="hidden" name="page" value="produk">
                                                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search_term); ?>">
                                                            <button type="submit" class="btn btn-sm btn-info ms-2">Cari</button>
                                                </form>
                                                <a href="<?php echo base_url('index.php?page=produk_form'); ?>" class="btn btn-primary btn-sm">+ Tambah Produk</a>
                                    </div>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table-modern">
                                                            <thead>
                                                                        <tr>
                                                                                    <th style="width: 5%;">No</th>
                                                                                    <th>Nama Produk</th>
                                                                                    <th style="width: 25%;">Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($produks)): ?>
                                                                                    <tr>
                                                                                                <td colspan="3" class="text-center bg-light p-5">Data produk tidak ditemukan.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php foreach ($produks as $produk): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo $nomor++; ?></td>
                                                                                                            <td>
                                                                                                                        <div class="fw-bold"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                                                                                                            </td>
                                                                                                            <td>
                                                                                                                        <a href="<?php echo base_url('index.php?page=resep&id_produk=' . $produk['id_produk']); ?>" class="btn btn-info btn-sm">Resep</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=produk_form&id=' . $produk['id_produk']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=produk_hapus&id=' . $produk['id_produk']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin?');">Hapus</a>
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
                                                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=produk&p=<?php echo $page - 1; ?>&q=<?php echo urlencode($search_term); ?>">Previous</a></li>
                                                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=produk&p=<?php echo $i; ?>&q=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a></li>
                                                                        <?php endfor; ?>
                                                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=produk&p=<?php echo $page + 1; ?>&q=<?php echo urlencode($search_term); ?>">Next</a></li>
                                                            </ul>
                                                </nav>
                                    <?php endif; ?>
                        </div>
            </div>
</div>