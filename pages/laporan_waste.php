<?php
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-29 days', strtotime($tanggal_akhir)));

$stmt = $pdo->prepare("
    SELECT 
        COALESCE(NULLIF(TRIM(alasan_gagal), ''), 'Tidak Ada Alasan') as penyebab,
        SUM(jumlah_gagal) as total_gagal
    FROM produksi_log
    WHERE DATE(tanggal_aktual) BETWEEN ? AND ?
    AND jumlah_gagal > 0
    GROUP BY penyebab
    ORDER BY total_gagal DESC
");
$stmt->execute([$tanggal_awal, $tanggal_akhir]);
$data_waste = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_data = [];
foreach ($data_waste as $data) {
            $chart_labels[] = $data['penyebab'];
            $chart_data[] = $data['total_gagal'];
}
?>

<div class="container-fluid">
            <h1 class="h3 mb-2 text-gray-800">Laporan Analisis Produk Gagal (Waste)</h1>
            <p class="mb-4">Laporan ini menampilkan total produk gagal berdasarkan penyebabnya pada periode yang dipilih.</p>

            <div class="card shadow mb-4">
                        <div class="card-header">Filter Periode</div>
                        <div class="card-body">
                                    <form method="GET">
                                                <input type="hidden" name="page" value="laporan_waste">
                                                <div class="row align-items-end">
                                                            <div class="col-md-4"><label for="awal">Tanggal Awal</label><input type="date" name="awal" id="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                                                            <div class="col-md-4"><label for="akhir">Tanggal Akhir</label><input type="date" name="akhir" id="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                                                            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                                                </div>
                                    </form>
                        </div>
            </div>

            <div class="row">
                        <div class="col-lg-5">
                                    <div class="card shadow mb-4">
                                                <div class="card-header">Grafik Komposisi Produk Gagal</div>
                                                <div class="card-body">
                                                            <?php if (empty($data_waste)): ?>
                                                                        <div class="text-center">Tidak ada data produk gagal pada periode ini.</div>
                                                            <?php else: ?>
                                                                        <div class="chart-pie pt-4" style="height: 350px;"><canvas id="wastePieChart"></canvas></div>
                                                            <?php endif; ?>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-lg-7">
                                    <div class="card shadow mb-4">
                                                <div class="card-header">Tabel Rincian Produk Gagal</div>
                                                <div class="card-body">
                                                            <div class="table-responsive">
                                                                        <table class="table table-bordered">
                                                                                    <thead>
                                                                                                <tr>
                                                                                                            <th>Penyebab Kegagalan</th>
                                                                                                            <th>Total Produk Gagal</th>
                                                                                                </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                                <?php if (empty($data_waste)): ?>
                                                                                                            <tr>
                                                                                                                        <td colspan="2" class="text-center">Tidak ada data.</td>
                                                                                                            </tr>
                                                                                                <?php else: ?>
                                                                                                            <?php foreach ($data_waste as $data): ?>
                                                                                                                        <tr>
                                                                                                                                    <td><?php echo htmlspecialchars($data['penyebab']); ?></td>
                                                                                                                                    <td><?php echo htmlspecialchars($data['total_gagal']); ?></td>
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
                        <?php if (!empty($data_waste)): ?>
                                    const ctx = document.getElementById('wastePieChart');
                                    new Chart(ctx, {
                                                type: 'pie',
                                                data: {
                                                            labels: <?php echo json_encode($chart_labels); ?>,
                                                            datasets: [{
                                                                        label: 'Total Gagal',
                                                                        data: <?php echo json_encode($chart_data); ?>,
                                                                        backgroundColor: [
                                                                                    'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)',
                                                                                    'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)',
                                                                                    'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'
                                                                        ],
                                                                        borderColor: [
                                                                                    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                                                                                    'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                                                                                    'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                                                                        ],
                                                                        borderWidth: 1
                                                            }]
                                                },
                                                options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                }
                                    });
                        <?php endif; ?>
            });
</script>