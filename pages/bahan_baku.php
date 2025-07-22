<?php

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

$nomor = ($page - 1) * $limit + 1;
?>

<div class="container-fluid py-3">
            <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                    <h6 class="m-0">Daftar Bahan Baku (Total: <?php echo $total_records ?>)</h6>
                                    <div class="d-flex flex-wrap align-items-center">
                                                <form method="GET" class="d-flex me-2">
                                                            <input type="hidden" name="page" value="bahan_baku">
                                                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari bahan baku..." value="<?php echo htmlspecialchars($search_term); ?>">
                                                            <button type="submit" class="btn btn-sm btn-info ms-2">Cari</button>
                                                </form>
                                                <a href="<?php echo base_url('index.php?page=bahan_baku_form'); ?>" class="btn btn-outline-primary btn-sm">+ Tambah</a>
                                    </div>
                        </div>
                        <div class="card-body">
                                    <div class="table-responsive">
                                                <table class="table-modern">
                                                            <thead>
                                                                        <tr>
                                                                                    <th style="width: 5%;">No</th>
                                                                                    <th>Nama Bahan</th>
                                                                                    <th>Satuan</th>
                                                                                    <th class="text-center">Harga Beli</th>
                                                                                    <th class="text-center" style="width: 15%;">Aksi</th>
                                                                        </tr>
                                                            </thead>
                                                            <tbody>
                                                                        <?php if (empty($bahan_bakus)): ?>
                                                                                    <tr>
                                                                                                <td colspan="5" class="text-center bg-light p-5">Data bahan baku tidak ditemukan.</td>
                                                                                    </tr>
                                                                        <?php else: ?>
                                                                                    <?php foreach ($bahan_bakus as $bahan): ?>
                                                                                                <tr>
                                                                                                            <td><?php echo $nomor++; ?></td>
                                                                                                            <td>
                                                                                                                        <div class="fw-bold"><?php echo htmlspecialchars($bahan['nama_bahan']); ?></div>
                                                                                                            </td>
                                                                                                            <td><?php echo htmlspecialchars($bahan['satuan']); ?></td>
                                                                                                            <td class="text-start">Rp <?php echo number_format($bahan['harga_beli'], 0, ',', '.'); ?></td>
                                                                                                            <td class="d-flex justify-content-center gap-2">
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_form&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pencil"></i></a>
                                                                                                                        <a href="<?php echo base_url('index.php?page=bahan_baku_hapus&id=' . $bahan['id_bahan_baku']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Anda yakin?');"><i class="fa-solid fa-trash"></i></a>
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