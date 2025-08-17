<?php
// pages/produk_jadi.php

// --- LOGIKA PHP UNTUK MENGAMBIL DATA ---
$stmt = $pdo->query("
    SELECT 
        pjb.kode_batch,
        pjb.jumlah_produksi,
        pjb.sisa_stok,
        pjb.tanggal_produksi,
        pjb.tanggal_kadaluarsa,
        p.nama_produk,
        DATEDIFF(pjb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    FROM 
        produk_jadi_batch AS pjb
    JOIN 
        produk AS p ON pjb.id_produk = p.id_produk
    ORDER BY 
        pjb.tanggal_produksi DESC
");
$semua_produk_jadi = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Semua Batch Produk Jadi</h6>
            </div>
        <div class="card-body">
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
                                Belum ada data produksi yang tercatat.
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
                                    // Logika untuk menampilkan status batch
                                    if ($batch['sisa_stok'] <= 0) {
                                        echo '<span class="badge bg-secondary">Habis</span>';
                                    } elseif ($batch['sisa_hari'] < 0) {
                                        echo '<span class="badge bg-dark">Kadaluarsa</span>';
                                    } elseif ($batch['sisa_hari'] <= 1) {
                                        echo '<span class="badge bg-danger">1 hari lagi</span>';
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
        </div>
    </div>
</div>