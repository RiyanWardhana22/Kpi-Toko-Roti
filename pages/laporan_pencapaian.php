<?php
// --- 1. Logika Filter Tanggal ---
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-29 days', strtotime($tanggal_akhir)));
$periode_params = [$tanggal_awal, $tanggal_akhir];

// --- 2. Query Baru yang Jauh Lebih Sederhana & Cepat ---
$stmt = $pdo->prepare("
    SELECT
        p.nama_produk,
        SUM(pk.jumlah_direncanakan) as total_target,
        SUM(pk.jumlah_sukses) as total_sukses,
        SUM(pk.jumlah_gagal) as total_gagal
    FROM
        perintah_kerja pk
    JOIN
        produk p ON pk.id_produk = p.id_produk
    WHERE
        pk.status = 'Selesai'
        AND DATE(pk.tanggal_selesai) BETWEEN ? AND ?
    GROUP BY
        p.id_produk, p.nama_produk
    ORDER BY
        p.nama_produk ASC
");
$stmt->execute($periode_params);
$laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Kalkulasi Data untuk KPI & Grafik ---
$overall_target = 0;
$overall_sukses = 0;
$overall_gagal = 0;
$chart_labels = [];
$chart_target = [];
$chart_sukses = [];
$chart_gagal = [];

foreach ($laporan_data as $data) {
    // Untuk KPI
    $overall_target += $data['total_target'];
    $overall_sukses += $data['total_sukses'];
    $overall_gagal += $data['total_gagal'];
    
    // Untuk Grafik
    $chart_labels[] = $data['nama_produk'];
    $chart_target[] = $data['total_target'];
    $chart_sukses[] = $data['total_sukses'];
    $chart_gagal[] = $data['total_gagal'];
}
$overall_pencapaian = ($overall_target > 0) ? ($overall_sukses / $overall_target) * 100 : 0;
?>

<div class="container-fluid py-3 px-4">
    <div class="card mb-4">
        <div class="card-header">Filter Periode</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="laporan_pencapaian">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-12 mb-2 mb-lg-0"><label>Tanggal Awal</label><input type="date" name="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-lg-5 col-md-12 mb-2 mb-lg-0"><label>Tanggal Akhir</label><input type="date" name="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-lg-2 col-md-12"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Rencana</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($overall_target); ?> Pcs</div>
                </div><div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Sukses</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($overall_sukses); ?> Pcs</div>
                </div><div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Gagal</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($overall_gagal); ?> Pcs</div>
                </div><div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
            </div></div></div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pencapaian Keseluruhan</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($overall_pencapaian, 1); ?>%</div>
                </div><div class="col-auto"><i class="fas fa-bullseye fa-2x text-gray-300"></i></div>
            </div></div></div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Grafik Perbandingan Target vs Hasil Produksi</h6></div>
        <div class="card-body">
            <?php if (empty($laporan_data)): ?>
                <div class="text-center p-5 text-muted">Tidak ada data produksi pada periode ini.</div>
            <?php else: ?>
                <div style="height: 400px;"><canvas id="pencapaianChart"></canvas></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Tabel Rincian Pencapaian per Produk</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-end">Target</th>
                            <th class="text-end">Sukses</th>
                            <th class="text-end">Gagal</th>
                            <th class="text-end">Selisih (Target-Sukses)</th>
                            <th style="width: 25%;">Pencapaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_data)): ?>
                            <tr><td colspan="6" class="text-center p-5 bg-light">Tidak ada data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($laporan_data as $data): ?>
                                <?php 
                                $pencapaian = ($data['total_target'] > 0) ? ($data['total_sukses'] / $data['total_target']) * 100 : 0;
                                $selisih = $data['total_target'] - $data['total_sukses'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['nama_produk']); ?></td>
                                    <td class="text-end"><?php echo number_format($data['total_target']); ?></td>
                                    <td class="text-end text-success fw-bold"><?php echo number_format($data['total_sukses']); ?></td>
                                    <td class="text-end text-danger"><?php echo number_format($data['total_gagal']); ?></td>
                                    <td class="text-end"><?php echo number_format($selisih); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar <?php echo ($pencapaian < 80) ? 'bg-danger' : (($pencapaian < 100) ? 'bg-warning' : 'bg-success'); ?>" 
                                                 role="progressbar" style="width: <?php echo $pencapaian; ?>%;" 
                                                 aria-valuenow="<?php echo $pencapaian; ?>"><?php echo number_format($pencapaian, 1); ?>%</div>
                                        </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($laporan_data)): ?>
            const ctx = document.getElementById('pencapaianChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [
                    {
                        label: 'Target',
                        data: <?php echo json_encode($chart_target); ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: 'Sukses',
                        data: <?php echo json_encode($chart_sukses); ?>,
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: 'Gagal',
                        data: <?php echo json_encode($chart_gagal); ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        <?php endif; ?>
    });
</script>