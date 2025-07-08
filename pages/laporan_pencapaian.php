<?php
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days', strtotime($tanggal_akhir)));

$stmt = $pdo->prepare("
    SELECT
        p.nama_produk,
        SUM(pr.target_produksi) as total_target,
        (SELECT SUM(COALESCE(pl.jumlah_sukses, 0)) FROM produksi_log pl WHERE pl.id_produk = p.id_produk AND DATE(pl.tanggal_aktual) BETWEEN ? AND ?) as total_aktual
    FROM produksi_rencana pr
    JOIN produk p ON pr.id_produk = p.id_produk
    WHERE pr.tanggal_produksi BETWEEN ? AND ?
    GROUP BY p.id_produk, p.nama_produk
    ORDER BY p.nama_produk
");
$stmt->execute([$tanggal_awal, $tanggal_akhir, $tanggal_awal, $tanggal_akhir]);
$laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_target = [];
$chart_aktual = [];
foreach ($laporan_data as $data) {
            $chart_labels[] = $data['nama_produk'];
            $chart_target[] = $data['total_target'];
            $chart_aktual[] = $data['total_aktual'];
}

?>
<div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Laporan Pencapaian Target Produksi</h1>
            <p class="mb-4">Visualisasi perbandingan antara target produksi dengan hasil produksi aktual untuk setiap produk.</p>

            <div class="card shadow mb-4">
                        <div class="card-header">Filter Periode</div>
                        <div class="card-body">
                                    <form method="GET">
                                                <input type="hidden" name="page" value="laporan_pencapaian">
                                                <div class="row align-items-end">
                                                            <div class="col-md-5"><label>Tanggal Awal</label><input type="date" name="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                                                            <div class="col-md-5"><label>Tanggal Akhir</label><input type="date" name="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                                                            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                                                </div>
                                    </form>
                        </div>
            </div>

            <div class="row">
                        <div class="col-lg-12">
                                    <div class="card shadow mb-4">
                                                <div class="card-header">
                                                            <h6 class="m-0 font-weight-bold text-primary">Grafik Perbandingan Target vs Aktual</h6>
                                                </div>
                                                <div class="card-body">
                                                            <?php if (empty($laporan_data)): ?>
                                                                        <div class="text-center">Tidak ada rencana produksi pada periode ini.</div>
                                                            <?php else: ?>
                                                                        <div class="chart-bar" style="height: 400px;"><canvas id="pencapaianChart"></canvas></div>
                                                            <?php endif; ?>
                                                </div>
                                    </div>

                                    <div class="card shadow mb-4">
                                                <div class="card-header">
                                                            <h6 class="m-0 font-weight-bold text-primary">Tabel Rincian Pencapaian</h6>
                                                </div>
                                                <div class="card-body">
                                                            <table class="table table-bordered">
                                                                        <thead>
                                                                                    <tr>
                                                                                                <th>Nama Produk</th>
                                                                                                <th class="text-end">Total Target</th>
                                                                                                <th class="text-end">Total Aktual</th>
                                                                                                <th class="text-end">Pencapaian</th>
                                                                                    </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                    <?php if (empty($laporan_data)): ?>
                                                                                                <tr>
                                                                                                            <td colspan="4" class="text-center">Tidak ada data.</td>
                                                                                                </tr>
                                                                                    <?php else: ?>
                                                                                                <?php foreach ($laporan_data as $data): ?>
                                                                                                            <?php $pencapaian = ($data['total_target'] > 0) ? ($data['total_aktual'] / $data['total_target']) * 100 : 0; ?>
                                                                                                            <tr>
                                                                                                                        <td><?php echo htmlspecialchars($data['nama_produk']); ?></td>
                                                                                                                        <td class="text-end"><?php echo number_format($data['total_target']); ?></td>
                                                                                                                        <td class="text-end"><?php echo number_format($data['total_aktual']); ?></td>
                                                                                                                        <td class="text-end">
                                                                                                                                    <div class="progress" style="height: 20px;">
                                                                                                                                                <div class="progress-bar <?php echo ($pencapaian < 80) ? 'bg-danger' : (($pencapaian < 100) ? 'bg-warning' : 'bg-success'); ?>" role="progressbar" style="width: <?php echo $pencapaian; ?>%;" aria-valuenow="<?php echo $pencapaian; ?>" aria-valuemin="0" aria-valuemax="100">
                                                                                                                                                            <?php echo number_format($pencapaian, 1); ?>%
                                                                                                                                                </div>
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
</div>

<script>
            document.addEventListener('DOMContentLoaded', function() {
                        <?php if (!empty($laporan_data)): ?>
                                    const ctx = document.getElementById('pencapaianChart');
                                    new Chart(ctx, {
                                                type: 'bar',
                                                data: {
                                                            labels: <?php echo json_encode($chart_labels); ?>,
                                                            datasets: [{
                                                                        label: 'Target Produksi',
                                                                        data: <?php echo json_encode($chart_target); ?>,
                                                                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                                                                        borderColor: 'rgba(255, 159, 64, 1)',
                                                                        borderWidth: 1
                                                            }, {
                                                                        label: 'Produksi Aktual',
                                                                        data: <?php echo json_encode($chart_aktual); ?>,
                                                                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                                                        borderColor: 'rgba(75, 192, 192, 1)',
                                                                        borderWidth: 1
                                                            }]
                                                },
                                                options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                        y: {
                                                                                    beginAtZero: true
                                                                        }
                                                            }
                                                }
                                    });
                        <?php endif; ?>
            });
</script>