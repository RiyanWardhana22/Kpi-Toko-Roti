<?php
$hari_ini = date('Y-m-d');
$stmt_target = $pdo->prepare("SELECT SUM(target_produksi) as total_target FROM produksi_rencana WHERE tanggal_produksi = ?");
$stmt_target->execute([$hari_ini]);
$total_target = $stmt_target->fetchColumn();

$stmt_sukses = $pdo->prepare("SELECT SUM(jumlah_sukses) as total_sukses FROM produksi_log WHERE DATE(tanggal_aktual) = ?");
$stmt_sukses->execute([$hari_ini]);
$total_sukses = $stmt_sukses->fetchColumn();

if ($total_target > 0) {
            $persentase_pencapaian = ($total_sukses / $total_target) * 100;
} else {
            $persentase_pencapaian = 0;
}


$stmt_gagal = $pdo->prepare("SELECT SUM(jumlah_gagal) as total_gagal FROM produksi_log WHERE DATE(tanggal_aktual) = ?");
$stmt_gagal->execute([$hari_ini]);
$total_gagal = $stmt_gagal->fetchColumn() ?: 0;

$stmt_efisiensi = $pdo->prepare("
    SELECT 
        (SUM(r.jumlah_standar * pl.jumlah_sukses) / SUM(ppb.jumlah_aktual)) * 100 AS efisiensi
    FROM produksi_log pl
    JOIN produksi_penggunaan_bahan ppb ON pl.id_log = ppb.id_log
    JOIN resep r ON pl.id_produk = r.id_produk AND ppb.id_bahan_baku = r.id_bahan_baku
    WHERE pl.tanggal_aktual >= CURDATE() - INTERVAL 7 DAY
");
$stmt_efisiensi->execute();
$efisiensi_bahan = $stmt_efisiensi->fetchColumn() ?: 0;


$stmt_grafik = $pdo->prepare("
    SELECT 
        DATE(tanggal_aktual) as tanggal, 
        SUM(jumlah_sukses) as total 
    FROM produksi_log 
    WHERE tanggal_aktual >= CURDATE() - INTERVAL 7 DAY 
    GROUP BY DATE(tanggal_aktual) 
    ORDER BY tanggal ASC
");
$stmt_grafik->execute();
$data_grafik = $stmt_grafik->fetchAll(PDO::FETCH_ASSOC);

$grafik_labels = [];
$grafik_data = [];
for ($i = 6; $i >= 0; $i--) {
            $tanggal = date('Y-m-d', strtotime("-$i days"));
            $grafik_labels[] = date('d M', strtotime($tanggal));
            $grafik_data[date('d M', strtotime($tanggal))] = 0;
}
foreach ($data_grafik as $row) {
            $label_tanggal = date("d M", strtotime($row['tanggal']));
            if (isset($grafik_data[$label_tanggal])) {
                        $grafik_data[$label_tanggal] = $row['total'];
            }
}

?>

<div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard Utama</h1>
            </div>

            <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                                <div class="card-body">
                                                            <div class="row no-gutters align-items-center">
                                                                        <div class="col mr-2">
                                                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pencapaian Target (Hari Ini)</div>
                                                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($persentase_pencapaian, 2); ?>%</div>
                                                                        </div>
                                                            </div>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-success shadow h-100 py-2">
                                                <div class="card-body">
                                                            <div class="row no-gutters align-items-center">
                                                                        <div class="col mr-2">
                                                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Efisiensi Bahan Baku (7 Hari)</div>
                                                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($efisiensi_bahan, 2); ?>%</div>
                                                                        </div>
                                                            </div>
                                                </div>
                                    </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                                <div class="card-body">
                                                            <div class="row no-gutters align-items-center">
                                                                        <div class="col mr-2">
                                                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Produk Gagal (Hari Ini)</div>
                                                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_gagal); ?></div>
                                                                        </div>
                                                            </div>
                                                </div>
                                    </div>
                        </div>
            </div>

            <div class="row">
                        <div class="col-lg-12">
                                    <div class="card shadow mb-4">
                                                <div class="card-header py-3">
                                                            <h6 class="m-0 font-weight-bold text-primary">Grafik Tren Produksi (7 Hari Terakhir)</h6>
                                                </div>
                                                <div class="card-body">
                                                            <div class="chart-area" style="height: 320px;">
                                                                        <canvas id="produksiChart"></canvas>
                                                            </div>
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
                                                data: <?php echo json_encode(array_values($grafik_data)); ?>,
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

                        const myChart = new Chart(
                                    document.getElementById('produksiChart'),
                                    config
                        );
            });
</script>