<?php
// --- BAGIAN LOGIKA PHP ---

// Logika untuk menampilkan notifikasi dari proses lain (hapus/tambah/edit)
$status = isset($_GET['status']) ? $_GET['status'] : '';
$notif_sukses = '';
$notif_gagal = '';
if ($status === 'sukses') {
    $notif_sukses = 'Data produk berhasil disimpan.';
} elseif ($status === 'sukses_hapus') {
    $notif_sukses = 'Produk berhasil dihapus.';
} elseif ($status === 'gagal_hapus') {
    $notif_gagal = 'Gagal menghapus! Produk ini sudah tercatat dalam riwayat produksi atau rencana.';
}

// Logika untuk pencarian dan pagination
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page - 1) * $limit;

// Query untuk menghitung total produk
$count_sql = "SELECT COUNT(id_produk) FROM produk";
$count_params = [];
if (!empty($search_term)) {
    $count_sql .= " WHERE nama_produk LIKE ?";
    $count_params[] = "%$search_term%";
}
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($count_params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Query untuk mengambil data produk
$data_sql = "SELECT * FROM produk";
if (!empty($search_term)) {
    $data_sql .= " WHERE nama_produk LIKE ?";
}
$data_sql .= " ORDER BY id_produk DESC LIMIT ? OFFSET ?";

// PERBAIKAN: Binding parameter dengan tipe data yang benar untuk mencegah error
$stmt = $pdo->prepare($data_sql);
$param_index = 1;
if (!empty($search_term)) {
    $stmt->bindValue($param_index++, "%$search_term%", PDO::PARAM_STR);
}
$stmt->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt->execute();
$produks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nomor = ($page - 1) * $limit + 1;
?>

<div class="container-fluid py-3">
    <?php if ($notif_sukses): ?>
    <div class="alert alert-success"><?php echo $notif_sukses; ?></div>
    <?php endif; ?>
    <?php if ($notif_gagal): ?>
    <div class="alert alert-danger"><?php echo $notif_gagal; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h6 class="m-0">Data Master Produk (Total: <?php echo $total_records ?>)</h6>
            <div class="d-flex flex-wrap align-items-center">
                <form method="GET" class="d-flex me-2">
                    <input type="hidden" name="page" value="produk">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-sm btn-info ms-2">Cari</button>
                </form>
                <a href="<?php echo base_url('index.php?page=produk_form'); ?>" class="btn btn-outline-primary btn-sm">+ Tambah Produk Baru</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th>Nama Produk</th>
                            <th class="text-center" style="width: 25%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produks)): ?>
                        <tr>
                            <td colspan="3" class="text-center bg-light p-5">Belum ada data produk. Silakan tambah produk baru.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($produks as $produk): ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                            </td>
                            <td class="d-flex justify-content-center gap-2">
                                <a href="<?php echo base_url('index.php?page=produk_form&id=' . $produk['id_produk']); ?>" class="btn btn-outline-warning btn-sm" title="Edit Produk & Resep"><i class="fa-solid fa-pencil"></i> Edit</a>
                                <a href="<?php echo base_url('index.php?page=produk_hapus&id=' . $produk['id_produk']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus produk ini beserta resepnya?');" title="Hapus Produk"><i class="fa-solid fa-trash"></i> Hapus</a>
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