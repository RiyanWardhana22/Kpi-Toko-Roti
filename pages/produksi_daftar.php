<?php
$limit = 20;

$halaman_sekarang = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
if ($halaman_sekarang < 1) {
    $halaman_sekarang = 1;
}

$stmt_total = $pdo->query("SELECT COUNT(*) FROM perintah_kerja");
$total_data = $stmt_total->fetchColumn();
$total_halaman = ceil($total_data / $limit);
$offset = ($halaman_sekarang - 1) * $limit;

$stmt = $pdo->prepare("
    SELECT pk.*, p.nama_produk 
    FROM perintah_kerja pk
    JOIN produk p ON pk.id_produk = p.id_produk
    ORDER BY pk.tanggal_dibuat DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$daftar_kerja = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">Daftar Perintah Kerja Produksi</h6>
            <a href="index.php?page=produksi_buat" class="btn btn-outline-primary btn-sm">+ Buat Perintah Kerja Baru</a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID Perintah</th>
                            <th>Nama Produk</th>
                            <th class="text-end">Jumlah Rencana</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_kerja)): ?>
                            <tr>
                                <td colspan="6" class="text-center bg-light p-5">
                                    Belum ada perintah kerja yang dibuat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftar_kerja as $kerja): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kerja['id_perintah_kerja']); ?></td>
                                    <td><?php echo htmlspecialchars($kerja['nama_produk']); ?></td>
                                    <td class="text-end"><?php echo number_format($kerja['jumlah_direncanakan']); ?></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($kerja['tanggal_dibuat'])); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $status = $kerja['status'];
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'Direncanakan') $badge_class = 'bg-info';
                                        if ($status == 'Berlangsung') $badge_class = 'bg-warning text-dark';
                                        if ($status == 'Selesai') $badge_class = 'bg-success';
                                        if ($status == 'Dibatalkan') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="index.php?page=produksi_detail&id=<?php echo $kerja['id_perintah_kerja']; ?>" class="btn btn-info btn-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_halaman > 1): ?>
                <nav aria-label="Navigasi Halaman" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($halaman_sekarang <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=produksi_daftar&p=<?php echo $halaman_sekarang - 1; ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                            <li class="page-item <?php echo ($i == $halaman_sekarang) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=produksi_daftar&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($halaman_sekarang >= $total_halaman) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="index.php?page=produksi_daftar&p=<?php echo $halaman_sekarang + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>