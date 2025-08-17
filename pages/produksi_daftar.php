<?php
// Mengambil semua data perintah kerja, diurutkan dari yang terbaru
// Kita JOIN dengan tabel produk untuk mendapatkan nama produknya
$stmt = $pdo->query("
    SELECT pk.*, p.nama_produk 
    FROM perintah_kerja pk
    JOIN produk p ON pk.id_produk = p.id_produk
    ORDER BY pk.tanggal_dibuat DESC
");
$daftar_kerja = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">Daftar Perintah Kerja Produksi</h6>
            <a href="index.php?page=produksi_buat" class="btn btn-outline-primary btn-sm">+ Buat Perintah Kerja Baru</a>
        </div>
        <div class="card-body">
            <div class="card-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
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
                                            <td>#<?php echo htmlspecialchars($kerja['id_perintah_kerja']); ?></td>
                                            <td><?php echo htmlspecialchars($kerja['nama_produk']); ?></td>
                                            <td class="text-end"><?php echo number_format($kerja['jumlah_direncanakan']); ?></td>
                                            <td><?php echo date('d M Y, H:i', strtotime($kerja['tanggal_dibuat'])); ?></td>
                                            <td class="text-center">
                                                <?php
                                                $status = $kerja['status'];
                                                $badge_class = 'bg-secondary'; // Default
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
                </div>
            </div>
        </div>