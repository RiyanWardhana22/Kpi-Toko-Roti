<?php
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$halaman_sekarang = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
if ($halaman_sekarang < 1) $halaman_sekarang = 1;

$base_sql = "FROM produk_jadi_batch AS pjb JOIN produk AS p ON pjb.id_produk = p.id_produk";
$where_sql = "";
$params = [];

if (!empty($search_query)) {
    $where_sql = " WHERE (pjb.kode_batch LIKE :search OR p.nama_produk LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$sql_total = "SELECT COUNT(*) " . $base_sql . $where_sql;
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$total_data = $stmt_total->fetchColumn();

$limit = 20;
$total_halaman = ceil($total_data / $limit);
$offset = ($halaman_sekarang - 1) * $limit;

$sql_data = "
    SELECT 
        pjb.kode_batch, pjb.jumlah_produksi, pjb.sisa_stok,
        pjb.tanggal_produksi, pjb.tanggal_kadaluarsa, p.nama_produk,
        DATEDIFF(pjb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    " . $base_sql . $where_sql . "
    ORDER BY pjb.tanggal_produksi DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql_data);

if (!empty($search_query)) {
    $stmt->bindValue(':search', $params[':search']);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$semua_produk_jadi = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Semua Batch Produk Jadi</h6>
        </div>
        <div class="card-body">

            <form method="GET" class="mb-4">
                <input type="hidden" name="page" value="produk_jadi">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari Kode Batch atau Nama Produk..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i> Cari</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="index.php?page=produk_jadi" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Batch</th>
                            <th>Nama Produk</th>
                            <th class="text-end">Jumlah Awal</th>
                            <th class="text-end">Sisa Stok</th>
                            <th class="text-center">Tgl Produksi</th>
                            <th class="text-center">Tgl Kadaluarsa</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($semua_produk_jadi)): ?>
                            <tr>
                                <td colspan="7" class="text-center p-5 bg-light">
                                    <?php if (!empty($search_query)): ?>
                                        Tidak ada produk jadi yang cocok dengan kata kunci "<?php echo htmlspecialchars($search_query); ?>".
                                    <?php else: ?>
                                        Belum ada data produksi yang tercatat.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($semua_produk_jadi as $batch): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($batch['kode_batch']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($batch['nama_produk']); ?></td>
                                    <td class="text-end"><?php echo number_format($batch['jumlah_produksi']); ?> Pcs</td>
                                    <td class="text-end fw-bold"><?php echo number_format($batch['sisa_stok']); ?> Pcs</td>
                                    <td class="text-center"><?php echo date('d M Y, H:i', strtotime($batch['tanggal_produksi'])); ?></td>
                                    <td class="text-center"><?php echo date('d M Y', strtotime($batch['tanggal_kadaluarsa'])); ?></td>
                                    <td class="text-center">
                                        <?php
                                        if ($batch['sisa_stok'] <= 0) {
                                            echo '<span class="badge bg-secondary">Habis</span>';
                                        } elseif ($batch['sisa_hari'] < 0) {
                                            echo '<span class="badge bg-dark">Kadaluarsa</span>';
                                        } elseif ($batch['sisa_hari'] <= 7) {
                                            echo '<span class="badge bg-danger">' . $batch['sisa_hari'] . ' hari lagi</span>';
                                        } else {
                                            echo '<span class="badge bg-success">Tersedia</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_halaman > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($halaman_sekarang <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=produk_jadi&p=<?php echo $halaman_sekarang - 1; ?>&q=<?php echo urlencode($search_query); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <li class="page-item <?php echo ($i == $halaman_sekarang) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=produk_jadi&p=<?php echo $i; ?>&q=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($halaman_sekarang >= $total_halaman) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=produk_jadi&p=<?php echo $halaman_sekarang + 1; ?>&q=<?php echo urlencode($search_query); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>