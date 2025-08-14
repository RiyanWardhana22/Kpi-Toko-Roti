<?php
// pages/dashboard.php

// 1. Logika Filter Tanggal
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days'));
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$periode_params = [$tanggal_awal, $tanggal_akhir];


// ==================== BAGIAN LOGIKA PHP YANG DIPERBARUI ====================

// --- 2. Query KPI Produksi (Menggunakan tabel perintah_kerja) ---
$stmt_produksi = $pdo->prepare("
    SELECT
        COALESCE(SUM(jumlah_direncanakan), 0) AS total_target,
        COALESCE(SUM(jumlah_sukses), 0) AS total_sukses,
        COALESCE(SUM(jumlah_gagal), 0) AS total_gagal
    FROM perintah_kerja
    WHERE status = 'Selesai' AND DATE(tanggal_selesai) BETWEEN ? AND ?
");
$stmt_produksi->execute($periode_params);
$data_produksi = $stmt_produksi->fetch(PDO::FETCH_ASSOC);

// Kalkulasi metrik
$persentase_pencapaian = ($data_produksi['total_target'] > 0) ? ($data_produksi['total_sukses'] / $data_produksi['total_target']) * 100 : 0;
$total_produksi_aktual = $data_produksi['total_sukses'] + $data_produksi['total_gagal'];
$tingkat_kegagalan = ($total_produksi_aktual > 0) ? ($data_produksi['total_gagal'] / $total_produksi_aktual) * 100 : 0;


// --- 3. Query Panel Peringatan Bahan Baku (Tidak ada perubahan) ---
$stmt_kritis = $pdo->query("
    SELECT 
        bb.nama_bahan, bb.satuan, bb.stok_minimum,
        COALESCE(SUM(sb.sisa_dasar), 0) as total_sisa
    FROM bahan_baku bb
    LEFT JOIN stok_batch sb ON bb.id_bahan_baku = sb.id_bahan_baku AND sb.sisa_dasar > 0
    GROUP BY bb.id_bahan_baku, bb.nama_bahan, bb.satuan, bb.stok_minimum
    HAVING total_sisa < bb.stok_minimum AND bb.stok_minimum > 0
");
$stok_kritis = $stmt_kritis->fetchAll(PDO::FETCH_ASSOC);

$stmt_kadaluarsa = $pdo->query("
    SELECT sb.*, bb.nama_bahan, bb.satuan, DATEDIFF(sb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    FROM stok_batch sb
    JOIN bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE sb.sisa_dasar > 0 AND DATEDIFF(sb.tanggal_kadaluarsa, CURDATE()) <= 3
    ORDER BY sb.tanggal_kadaluarsa ASC
");
$stok_kadaluarsa = $stmt_kadaluarsa->fetchAll(PDO::FETCH_ASSOC);


// --- 4. Query Panel Peringatan Produk Jadi (Tidak ada perubahan) ---
$stmt_pj_kadaluarsa = $pdo->query("
    SELECT 
        pjb.kode_batch, pjb.sisa_stok, p.nama_produk, 
        DATEDIFF(pjb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    FROM produk_jadi_batch AS pjb
    JOIN produk AS p ON pjb.id_produk = p.id_produk
    WHERE pjb.sisa_stok > 0 AND DATEDIFF(pjb.tanggal_kadaluarsa, CURDATE()) <= 1
    ORDER BY pjb.tanggal_kadaluarsa ASC
");
$produk_jadi_kadaluarsa = $stmt_pj_kadaluarsa->fetchAll(PDO::FETCH_ASSOC);


// --- 5. Query & Persiapan Data untuk Grafik (Menggunakan tabel perintah_kerja) ---
$stmt_grafik = $pdo->prepare("
    SELECT DATE(tanggal_selesai) as tanggal, SUM(jumlah_sukses) as total 
    FROM perintah_kerja 
    WHERE status = 'Selesai' AND DATE(tanggal_selesai) BETWEEN ? AND ?
    GROUP BY DATE(tanggal_selesai) ORDER BY tanggal ASC
");
$stmt_grafik->execute($periode_params);
$data_grafik_db = $stmt_grafik->fetchAll(PDO::FETCH_ASSOC);

// Memformat data agar siap dipakai oleh Chart.js (Tidak ada perubahan)
$grafik_labels = [];
$grafik_data = [];
$data_grafik_assoc = array_column($data_grafik_db, 'total', 'tanggal');
$begin = new DateTime($tanggal_awal);
$end = new DateTime($tanggal_akhir);
$end->modify('+1 day');
$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($begin, $interval, $end);

foreach ($dateRange as $date) {
    $formatted_date_key = $date->format('Y-m-d');
    $grafik_labels[] = $date->format('d M');
    $grafik_data[] = isset($data_grafik_assoc[$formatted_date_key]) ? $data_grafik_assoc[$formatted_date_key] : 0;
}
?>

<div class="container-fluid py-3 px-4">

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap align-items-center" action="">
                <input type="hidden" name="page" value="dashboard">
                <div class="me-3 mb-2">
                    <label for="awal" class="form-label-sm fw-bold">Tanggal Awal:</label>
                    <input type="date" id="awal" name="awal" class="form-control form-control-sm" value="<?php echo $tanggal_awal; ?>">
                </div>
                <div class="me-3 mb-2">
                    <label for="akhir" class="form-label-sm fw-bold">Tanggal Akhir:</label>
                    <input type="date" id="akhir" name="akhir" class="form-control form-control-sm" value="<?php echo $tanggal_akhir; ?>">
                </div>
                <div class="align-self-end mb-2">
                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 mb-4">
            <a href="index.php?page=laporan_pencapaian&awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pencapaian Target</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($persentase_pencapaian, 1); ?>%</div>
                                <small class="text-muted"><?php echo number_format($data_produksi['total_sukses']) . ' dari ' . number_format($data_produksi['total_target']) . ' Pcs'; ?></small>
                            </div>
                            <div class="col-auto"><i class="fas fa-bullseye fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 mb-4">
            <a href="index.php?page=laporan_kegagalan&awal=<?php echo $tanggal_awal; ?>&akhir=<?php echo $tanggal_akhir; ?>" class="text-decoration-none">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Tingkat Produk Gagal</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($tingkat_kegagalan, 1); ?>%</div>
                                <small class="text-muted"><?php echo number_format($data_produksi['total_gagal']) . ' dari ' . number_format($total_produksi_aktual) . ' Pcs'; ?></small>
                            </div>
                            <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Tren Produksi Sukses Harian</h6></div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="trenProduksiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card card-warning shadow h-100">
                <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-box-open"></i> Stok Kritis Bahan Baku</h6></div>
                <div class="card-body">
                    <?php if (empty($stok_kritis)): ?>
                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Aman, semua stok di atas minimum.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($stok_kritis as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <?php echo htmlspecialchars($item['nama_bahan']); ?>
                                    <span class="badge bg-warning rounded-pill text-dark"><?php echo number_format($item['total_sisa']) . ' / ' . number_format($item['stok_minimum']) . ' ' . htmlspecialchars($item['satuan']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card card-danger shadow h-100">
                <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-calendar-times"></i> Kadaluarsa Bahan Baku (<= 3 Hari)</h6></div>
                <div class="card-body">
                    <?php if (empty($stok_kadaluarsa)): ?>
                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Aman, tidak ada bahan baku kadaluarsa.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($stok_kadaluarsa as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <?php echo htmlspecialchars($item['nama_bahan']); ?>
                                        <small class="d-block text-muted">Batch: <?php echo htmlspecialchars($item['kode_batch']); ?></small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill"><?php echo $item['sisa_hari'] <= 0 ? 'Sudah Lewat' : $item['sisa_hari'] . ' hari lagi'; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card card-info shadow h-100">
                <div class="card-header"><h6 class="m-0 font-weight-bold"><i class="fas fa-birthday-cake"></i> Kadaluarsa Produk Jadi (<= 1 Hari)</h6></div>
                <div class="card-body">
                    <?php if (empty($produk_jadi_kadaluarsa)): ?>
                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Aman, tidak ada produk jadi kadaluarsa.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                        <?php foreach($produk_jadi_kadaluarsa as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong>
                                    <small class="d-block text-muted">Batch: <?php echo htmlspecialchars($item['kode_batch']); ?> (Sisa: <?php echo $item['sisa_stok']; ?>)</small>
                                </div>
                                <?php
                                    $sisa_hari_text = '';
                                    if ($item['sisa_hari'] == 1) $sisa_hari_text = 'Besok';
                                    elseif ($item['sisa_hari'] == 0) $sisa_hari_text = 'Hari Ini';
                                    else $sisa_hari_text = 'Sudah Lewat';
                                ?>
                                <span class="badge bg-info rounded-pill"><?php echo $sisa_hari_text; ?></span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const canvasElement = document.getElementById('trenProduksiChart');
    if (canvasElement) {
        const ctx = canvasElement.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($grafik_labels); ?>,
                datasets: [{
                    label: 'Produksi Sukses (Pcs)',
                    data: <?php echo json_encode($grafik_data); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    fill: true,
                    tension: 0.2, // Membuat garis sedikit melengkung
                    pointBackgroundColor: '#4e73df',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Memastikan angka di sumbu Y adalah integer
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Sembunyikan legenda jika hanya ada 1 dataset
                    }
                }
            }
        });
    }
});
</script>