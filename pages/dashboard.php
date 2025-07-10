<?php
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days'));
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
if ($tanggal_awal == $tanggal_akhir) {
    $periode_label = "Tanggal " . date('d M Y', strtotime($tanggal_awal));
} else {
    $periode_label = "Periode " . date('d M Y', strtotime($tanggal_awal)) . " - " . date('d M Y', strtotime($tanggal_akhir));
}

$stmt_target = $pdo->prepare("SELECT SUM(target_produksi) as total_target FROM produksi_rencana WHERE tanggal_produksi BETWEEN ? AND ?");
$stmt_target->execute([$tanggal_awal, $tanggal_akhir]);
$total_target = $stmt_target->fetchColumn();
$stmt_sukses = $pdo->prepare("SELECT SUM(jumlah_sukses) as total_sukses FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ?");
$stmt_sukses->execute([$tanggal_awal, $tanggal_akhir]);
$total_sukses = $stmt_sukses->fetchColumn();
$persentase_pencapaian = ($total_target > 0) ? ($total_sukses / $total_target) * 100 : 0;

$stmt_gagal = $pdo->prepare("SELECT SUM(jumlah_gagal) as total_gagal FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ?");
$stmt_gagal->execute([$tanggal_awal, $tanggal_akhir]);
$total_gagal = $stmt_gagal->fetchColumn() ?: 0;

$stmt_efisiensi = $pdo->prepare("SELECT (SUM(r.jumlah_standar * pl.jumlah_sukses) / SUM(ppb.jumlah_aktual)) * 100 AS efisiensi FROM produksi_log pl JOIN produksi_penggunaan_bahan ppb ON pl.id_log = ppb.id_log JOIN resep r ON pl.id_produk = r.id_produk AND ppb.id_bahan_baku = r.id_bahan_baku WHERE DATE(pl.tanggal_aktual) BETWEEN ? AND ?");
$stmt_efisiensi->execute([$tanggal_awal, $tanggal_akhir]);
$efisiensi_bahan = $stmt_efisiensi->fetchColumn() ?: 0;

$stmt_rincian = $pdo->prepare("SELECT p.nama_produk, pr.target_produksi, COALESCE(SUM(pl.jumlah_sukses), 0) as total_sukses FROM produksi_rencana pr JOIN produk p ON pr.id_produk = p.id_produk LEFT JOIN produksi_log pl ON pr.id_rencana = pl.id_rencana WHERE pr.tanggal_produksi BETWEEN ? AND ? GROUP BY p.nama_produk, pr.target_produksi ORDER BY p.nama_produk");
$stmt_rincian->execute([$tanggal_awal, $tanggal_akhir]);
$rincian_pencapaian = $stmt_rincian->fetchAll(PDO::FETCH_ASSOC);

$stmt_aktivitas = $pdo->prepare("SELECT p.nama_produk, pl.jumlah_sukses, COALESCE(u.nama_lengkap, 'Sistem') as nama_pengguna, pl.tanggal_aktual FROM produksi_log pl JOIN produk p ON pl.id_produk = p.id_produk LEFT JOIN pengguna u ON pl.id_pengguna_input = u.id_pengguna WHERE DATE(pl.tanggal_aktual) BETWEEN ? AND ? ORDER BY pl.id_log DESC LIMIT 5");
$stmt_aktivitas->execute([$tanggal_awal, $tanggal_akhir]);
$aktivitas_terkini = $stmt_aktivitas->fetchAll(PDO::FETCH_ASSOC);

$stmt_grafik = $pdo->prepare("SELECT DATE(tanggal_aktual) as tanggal, SUM(jumlah_sukses) as total FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ? GROUP BY DATE(tanggal_aktual) ORDER BY tanggal ASC");
$stmt_grafik->execute([$tanggal_awal, $tanggal_akhir]);
$data_grafik_db = $stmt_grafik->fetchAll(PDO::FETCH_ASSOC);
$grafik_labels = [];
$grafik_data = [];
$begin = new DateTime($tanggal_awal);
$end = new DateTime($tanggal_akhir);
$end->modify('+1 day');
$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($begin, $interval, $end);
$data_grafik_assoc = array_column($data_grafik_db, 'total', 'tanggal');
foreach ($dateRange as $date) {
    $formatted_date_key = $date->format('Y-m-d');
    $grafik_labels[] = $date->format('d M');
    $grafik_data[] = isset($data_grafik_assoc[$formatted_date_key]) ? $data_grafik_assoc[$formatted_date_key] : 0;
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body">
            <form method="GET" class="d-flex flex-wrap align-items-center">
                <input type="hidden" name="page" value="dashboard">
                <div class="flex-grow-1 d-flex flex-wrap align-items-center">
                    <div class="col-lg-auto col-12 fw-bold me-3 mb-2 mb-lg-0">Filter Periode:</div>
                    <div class="col-lg-4 col-12 me-2 mb-2 mb-lg-0"><input type="date" name="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-lg-4 col-12 me-2 mb-2 mb-lg-0"><input type="date" name="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-lg-1 col-12"><button type="submit" class="btn btn-primary w-100">Go</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">Grafik Tren Produksi</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 350px;"><canvas id="produksiChart"></canvas></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">Rincian Pencapaian Target</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($rincian_pencapaian)): ?>
                        <div class="text-center p-4 text-muted">Belum ada rencana produksi pada periode ini.</div>
                    <?php else: ?>
                        <?php foreach ($rincian_pencapaian as $rincian): ?>
                            <?php $persen = ($rincian['target_produksi'] > 0) ? ($rincian['total_sukses'] / $rincian['target_produksi']) * 100 : 0; ?>
                            <div class="mb-3">
                                <h4 class="small fw-bold"><?php echo htmlspecialchars($rincian['nama_produk']); ?><span class="float-end text-muted"><?php echo "{$rincian['total_sukses']} / {$rincian['target_produksi']}"; ?></span></h4>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $persen; ?>%; background-color: var(--primary-color);" aria-valuenow="<?php echo $persen; ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="kpi-card">
                <div class="kpi-icon bg-icon-primary"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="#ffffff" d="M160 80c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 352c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-352zM0 272c0-26.5 21.5-48 48-48l32 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48L0 272zM368 96l32 0c26.5 0 48 21.5 48 48l0 288c0 26.5-21.5 48-48 48l-32 0c-26.5 0-48-21.5-48-48l0-288c0-26.5 21.5-48 48-48z" />
                    </svg></div>
                <div class="kpi-content">
                    <div class="kpi-label">Pencapaian Target</div>
                    <div class="kpi-value"><?php echo number_format($persentase_pencapaian, 1); ?>%</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon bg-icon-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg></div>
                <div class="kpi-content">
                    <div class="kpi-label">Efisiensi Bahan</div>
                    <div class="kpi-value"><?php echo number_format($efisiensi_bahan, 1); ?>%</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon bg-icon-danger"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg></div>
                <div class="kpi-content">
                    <div class="kpi-label">Produk Gagal</div>
                    <div class="kpi-value"><?php echo number_format($total_gagal); ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0">Aktivitas Terkini</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <?php if (empty($aktivitas_terkini)): ?>
                                    <tr>
                                        <td class="text-center text-muted p-4">Belum ada aktivitas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($aktivitas_terkini as $aktivitas): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($aktivitas['nama_produk']); ?></strong><small class="d-block text-muted"><?php echo htmlspecialchars($aktivitas['jumlah_sukses']); ?> Pcs oleh <?php echo htmlspecialchars($aktivitas['nama_pengguna']); ?></small></td>
                                            <td class="text-end text-muted small align-middle"><?php echo date('H:i', strtotime($aktivitas['tanggal_aktual'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('produksiChart')) {
            const chartData = {
                labels: <?php echo json_encode($grafik_labels); ?>,
                datasets: [{
                    label: 'Total Produksi',
                    data: <?php echo json_encode($grafik_data); ?>,
                    backgroundColor: 'rgba(74, 144, 226, 0.1)',
                    borderColor: 'rgba(74, 144, 226, 1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(74, 144, 226, 1)'
                }]
            };
            const chartConfig = {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };
            new Chart(document.getElementById('produksiChart'), chartConfig);
        }
    });
</script>