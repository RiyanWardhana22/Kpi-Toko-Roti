<?php
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-29 days', strtotime($tanggal_akhir)));

$stmt = $pdo->prepare("SELECT COALESCE(NULLIF(TRIM(alasan_gagal), ''), 'Tidak Ada Alasan') as penyebab, SUM(jumlah_gagal) as total_gagal FROM produksi_log WHERE DATE(tanggal_aktual) BETWEEN ? AND ? AND jumlah_gagal > 0 GROUP BY penyebab ORDER BY total_gagal DESC");
$stmt->execute([$tanggal_awal, $tanggal_akhir]);
$data_waste = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_data = [];
foreach ($data_waste as $data) {
    $chart_labels[] = $data['penyebab'];
    $chart_data[] = $data['total_gagal'];
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">Filter Periode Laporan</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="laporan_waste">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-12 mb-2 mb-lg-0"><label for="awal">Tanggal Awal</label><input type="date" name="awal" id="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-lg-5 col-md-12 mb-2 mb-lg-0"><label for="akhir">Tanggal Akhir</label><input type="date" name="akhir" id="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-lg-2 col-md-12"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Grafik Komposisi Produk Gagal</div>
                <div class="card-body">
                    <?php if (empty($data_waste)): ?>
                        <div class="text-center p-5">Tidak ada data produk gagal pada periode ini.</div>
                    <?php else: ?>
                        <div class="chart-pie pt-4" style="height: 350px;"><canvas id="wastePieChart"></canvas></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Tabel Rincian Produk Gagal</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Penyebab Kegagalan</th>
                                    <th>Total Gagal</th>
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
                                            <td><?php echo htmlspecialchars($data['total_gagal']); ?> Pcs</td>
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
                        backgroundColor: ['#F44336', '#4A90E2', '#FFC107', '#4CAF50', '#9C27B0', '#FF9800'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        <?php endif; ?>
    });
</script>