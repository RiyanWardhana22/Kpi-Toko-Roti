<?php
// Menentukan tanggal laporan, default ke hari ini
$tanggal_laporan = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query baru yang mengambil data dari sistem Perintah Kerja
// dan menyertakan harga untuk kalkulasi biaya.
$sql = "
    SELECT 
        pk.id_perintah_kerja,
        pk.tanggal_dimulai,
        p.nama_produk,
        bb.nama_bahan,
        pp.kode_batch_bahan,
        pp.jumlah_digunakan,
        bb.satuan,
        sb.harga_per_satuan_dasar,
        (pp.jumlah_digunakan * sb.harga_per_satuan_dasar) AS total_biaya
    FROM 
        perintah_kerja_penggunaan_batch pp
    JOIN 
        perintah_kerja pk ON pp.id_perintah_kerja = pk.id_perintah_kerja
    JOIN 
        produk p ON pk.id_produk = p.id_produk
    JOIN 
        stok_batch sb ON pp.kode_batch_bahan = sb.kode_batch
    JOIN 
        bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE 
        pk.status IN ('Berlangsung', 'Selesai', 'Dibatalkan') 
        AND DATE(pk.tanggal_dimulai) = ?
    ORDER BY 
        pk.tanggal_dimulai ASC, pk.id_perintah_kerja ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tanggal_laporan]);
$laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kalkulasi total biaya harian
$total_biaya_harian = 0;
foreach ($laporan_data as $data) {
    $total_biaya_harian += $data['total_biaya'];
}
?>

<div class="container-fluid py-3 px-4">
    <div class="card mb-4">
        <div class="card-header">Filter Laporan</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="laporan_penggunaan_harian">
                <div class="row align-items-end">
                    <div class="col-lg-10 col-md-12 mb-2 mb-lg-0">
                        <label for="tanggal">Pilih Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal_laporan); ?>">
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">Rincian Penggunaan Bahan - <?php echo date('d F Y', strtotime($tanggal_laporan)); ?></h6>
            <a href="export_penggunaan_harian.php?tanggal=<?php echo $tanggal_laporan; ?>" class="btn btn-success btn-sm" target="_blank">+ Export ke Excel</a>
        </div>
        <div class="card-body">
            <div class="card bg-light border-left-info shadow-sm mb-4">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Biaya Bahan Terpakai</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($total_biaya_harian, 0, ',', '.'); ?></div>
                </div>
            </div>

            <?php if (empty($laporan_data)): ?>
                <div class="text-center p-5">Tidak ada penggunaan bahan pada tanggal ini.</div>
            <?php else: 
                $current_pk_id = null; // Variabel untuk melacak ID Perintah Kerja saat ini
                foreach ($laporan_data as $data):
                    // Jika ID Perintah Kerja berubah, buat header grup baru
                    if ($data['id_perintah_kerja'] !== $current_pk_id):
                        if ($current_pk_id !== null):
                            // Tutup tabel sebelumnya jika bukan yang pertama
                            echo '</tbody></table></div></div>';
                        endif;
                        $current_pk_id = $data['id_perintah_kerja'];
            ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-primary-subtle">
                                <h6 class="m-0">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    <strong>Perintah Kerja #<?php echo htmlspecialchars($data['id_perintah_kerja']); ?>:</strong>
                                    <?php echo htmlspecialchars($data['nama_produk']); ?>
                                    <small class="text-muted float-end">Dimulai Pukul: <?php echo date('H:i', strtotime($data['tanggal_dimulai'])); ?></small>
                                </h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bahan Baku</th>
                                            <th>Dari Batch</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Harga Satuan</th>
                                            <th class="text-end">Total Biaya</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            <?php 
                    endif; 
            ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data['nama_bahan']); ?></td>
                                            <td><?php echo htmlspecialchars($data['kode_batch_bahan']); ?></td>
                                            <td class="text-end"><?php echo number_format($data['jumlah_digunakan'], 2, ',', '.') . ' ' . htmlspecialchars($data['satuan']); ?></td>
                                            <td class="text-end">Rp <?php echo number_format($data['harga_per_satuan_dasar'], 0, ',', '.'); ?></td>
                                            <td class="text-end fw-bold">Rp <?php echo number_format($data['total_biaya'], 0, ',', '.'); ?></td>
                                        </tr>
            <?php 
                endforeach;
                // Tutup tabel terakhir setelah loop selesai
                echo '</tbody></table></div></div>';
            ?>
            <?php endif; ?>
        </div>
    </div>
</div>