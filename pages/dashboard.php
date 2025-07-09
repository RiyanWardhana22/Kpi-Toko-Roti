<?php
// --- [BAGIAN BARU] PENGATURAN FILTER TANGGAL GLOBAL ---
// Jika tidak ada filter, default ke hari ini.
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d');
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');

// Membuat label periode untuk judul
if ($tanggal_awal == $tanggal_akhir) {
    $periode_label = "Tanggal " . date('d M Y', strtotime($tanggal_awal));
} else {
    $periode_label = "Periode " . date('d M Y', strtotime($tanggal_awal)) . " - " . date('d M Y', strtotime($tanggal_akhir));
}


// --- SEMUA QUERY DI BAWAH INI TELAH DIMODIFIKASI UNTUK MENGGUNAKAN FILTER ---

// 1. KPI: Pencapaian Target
$stmt_target = $pdo->prepare("SELECT SUM(target_produksi) as total_target FROM produksi_rencana WHERE tanggal_produksi BETWEEN ? AND ?");
$stmt_target->execute([$tanggal_awal, $tanggal_akhir]);
$total_target = $stmt_target->fetchColumn();
$stmt_sukses = $pdo->prepare("SELECT SUM(jumlah_sukses) as total_sukses FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ?");
$stmt_sukses->execute([$tanggal_awal, $tanggal_akhir]);
$total_sukses = $stmt_sukses->fetchColumn();
$persentase_pencapaian = ($total_target > 0) ? ($total_sukses / $total_target) * 100 : 0;

// 2. KPI: Produk Gagal
$stmt_gagal = $pdo->prepare("SELECT SUM(jumlah_gagal) as total_gagal FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ?");
$stmt_gagal->execute([$tanggal_awal, $tanggal_akhir]);
$total_gagal = $stmt_gagal->fetchColumn() ?: 0;

// 3. KPI: Efisiensi Bahan Baku
$stmt_efisiensi = $pdo->prepare("SELECT (SUM(r.jumlah_standar * pl.jumlah_sukses) / SUM(ppb.jumlah_aktual)) * 100 AS efisiensi FROM produksi_log pl JOIN produksi_penggunaan_bahan ppb ON pl.id_log = ppb.id_log JOIN resep r ON pl.id_produk = r.id_produk AND ppb.id_bahan_baku = r.id_bahan_baku WHERE DATE(pl.tanggal_aktual) BETWEEN ? AND ?");
$stmt_efisiensi->execute([$tanggal_awal, $tanggal_akhir]);
$efisiensi_bahan = $stmt_efisiensi->fetchColumn() ?: 0;

// 4. Data Rincian Pencapaian
$stmt_rincian = $pdo->prepare("SELECT p.nama_produk, pr.target_produksi, COALESCE(SUM(pl.jumlah_sukses), 0) as total_sukses FROM produksi_rencana pr JOIN produk p ON pr.id_produk = p.id_produk LEFT JOIN produksi_log pl ON pr.id_rencana = pl.id_rencana WHERE pr.tanggal_produksi BETWEEN ? AND ? GROUP BY p.nama_produk, pr.target_produksi ORDER BY p.nama_produk");
$stmt_rincian->execute([$tanggal_awal, $tanggal_akhir]);
$rincian_pencapaian = $stmt_rincian->fetchAll(PDO::FETCH_ASSOC);

// 5. Data Aktivitas Terkini
$stmt_aktivitas = $pdo->prepare("SELECT p.nama_produk, pl.jumlah_sukses, COALESCE(u.nama_lengkap, 'Sistem') as nama_pengguna, pl.tanggal_aktual FROM produksi_log pl JOIN produk p ON pl.id_produk = p.id_produk LEFT JOIN pengguna u ON pl.id_pengguna_input = u.id_pengguna WHERE DATE(pl.tanggal_aktual) BETWEEN ? AND ? ORDER BY pl.id_log DESC LIMIT 5");
$stmt_aktivitas->execute([$tanggal_awal, $tanggal_akhir]);
$aktivitas_terkini = $stmt_aktivitas->fetchAll(PDO::FETCH_ASSOC);

// 6. Data Grafik Tren Produksi
$stmt_grafik = $pdo->prepare("SELECT DATE(tanggal_aktual) as tanggal, SUM(jumlah_sukses) as total FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ? GROUP BY DATE(tanggal_aktual) ORDER BY tanggal ASC");
$stmt_grafik->execute([$tanggal_awal, $tanggal_akhir]);
$data_grafik_db = $stmt_grafik->fetchAll(PDO::FETCH_ASSOC);
$grafik_labels = [];
$grafik_data = [];
$begin = new DateTime($tanggal_awal);
$end = new DateTime($tanggal_akhir);
$end->modify('+1 day'); // Include the end date in the loop
$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($begin, $interval, $end);
$data_grafik_assoc = array_column($data_grafik_db, 'total', 'tanggal');
foreach ($dateRange as $date) {
    $formatted_date_key = $date->format('Y-m-d');
    $grafik_labels[] = $date->format('d M');
    $grafik_data[] = isset($data_grafik_assoc[$formatted_date_key]) ? $data_grafik_assoc[$formatted_date_key] : 0;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Utama</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Filter Dashboard</h6>
        </div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="dashboard">
                <div class="row align-items-end">
                    <div class="col-md-5"><label for="awal">Tanggal Awal</label><input type="date" name="awal" id="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-md-5"><label for="akhir">Tanggal Akhir</label><input type="date" name="akhir" id="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pencapaian Target</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($persentase_pencapaian, 2); ?>%</div>
                        </div>
                        <div class="col-auto"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-bullseye text-gray-300" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                <path d="M8 13A5 5 0 1 1 8 3a5 5 0 0 1 0 10zm0 1A6 6 0 1 0 8 2a6 6 0 0 0 0 12z" />
                                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
                                <path d="M9.5 8a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                            </svg></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Efisiensi Bahan Baku</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($efisiensi_bahan, 2); ?>%</div>
                        </div>
                        <div class="col-auto"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-recycle text-gray-300" viewBox="0 0 16 16">
                                <path d="M9.302 1.256a1.5 1.5 0 0 0-2.604 0l-1.704 2.98a.5.5 0 0 0 .869.5l1.704-2.981A.5.5 0 0 1 8 1.5a.5.5 0 0 1 .434.256l1.704 2.981a.5.5 0 0 0 .869-.5l-1.704-2.98zM2.973 7.773l-1.222 2.14a.5.5 0 0 0 .87.497l1.222-2.14a.5.5 0 0 0-.87-.497zM10.24 9.917l1.222 2.14a.5.5 0 0 0 .87-.497l-1.222-2.14a.5.5 0 0 0-.87.497zM8.5 5.03a.5.5 0 0 0-1 0v3.217l-1.85-.925a.5.5 0 0 0-.447.894l2.5 1.25a.5.5 0 0 0 .447 0l2.5-1.25a.5.5 0 0 0-.447-.894L8.5 8.247V5.03z" />
                                <path d="M14.002 11.168a.5.5 0 0 0-.497.87l1.222 2.14a.5.5 0 0 0 .87-.497l-1.222-2.14a.5.5 0 0 0-.373-.373zM3.703 14.137l1.222-2.14a.5.5 0 0 0-.87-.497l-1.222 2.14a.5.5 0 0 0 .497.87zM1.222 9.354l-1.222 2.14a.5.5 0 0 0 .87.497l1.222-2.14a.5.5 0 0 0-.87-.497z" />
                            </svg></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Produk Gagal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_gagal); ?></div>
                        </div>
                        <div class="col-auto"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-x-circle text-gray-300" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                            </svg></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="mb-4 text-center text-gray-700">Menampilkan Data untuk: <?php echo $periode_label; ?></h5>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rincian Pencapaian Target</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($rincian_pencapaian)): ?><div class="text-center">Belum ada rencana produksi pada periode ini.</div><?php else: foreach ($rincian_pencapaian as $rincian): ?>
                            <?php $persen = ($rincian['target_produksi'] > 0) ? ($rincian['total_sukses'] / $rincian['target_produksi']) * 100 : 0;
                                                                                                                                            $color_class = $persen < 50 ? 'bg-danger' : ($persen < 90 ? 'bg-warning' : 'bg-success'); ?>
                            <h4 class="small font-weight-bold"><?php echo htmlspecialchars($rincian['nama_produk']); ?> <span class="float-end"><?php echo "{$rincian['total_sukses']} / {$rincian['target_produksi']}"; ?></span></h4>
                            <div class="progress mb-4">
                                <div class="progress-bar <?php echo $color_class; ?>" role="progressbar" style="width: <?php echo $persen; ?>%" aria-valuenow="<?php echo $persen; ?>"></div>
                            </div>
                    <?php endforeach;
                                                                                                                                    endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Produksi Terkini</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                <?php if (empty($aktivitas_terkini)): ?><tr>
                                        <td class="text-center">Belum ada aktivitas.</td>
                                    </tr><?php else: foreach ($aktivitas_terkini as $aktivitas): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($aktivitas['nama_produk']); ?></strong><small class="d-block text-muted"><?php echo htmlspecialchars($aktivitas['jumlah_sukses']); ?> Pcs oleh <?php echo htmlspecialchars($aktivitas['nama_pengguna']); ?></small></td>
                                            <td class="text-end text-muted small align-middle"><?php echo date('d M, H:i', strtotime($aktivitas['tanggal_aktual'])); ?></td>
                                        </tr>
                                <?php endforeach;
                                        endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Tren Produksi</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;"><canvas id="produksiChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = <?php echo json_encode($grafik_labels); ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: 'Total Produksi Harian',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                data: <?php echo json_encode($grafik_data); ?>,
                fill: true,
                tension: 0.3
            }]
        };
        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.formattedValue;
                            }
                        }
                    }
                }
            }
        };
        if (document.getElementById('produksiChart')) {
            const myChart = new Chart(document.getElementById('produksiChart'), config);
        }
    });
</script>