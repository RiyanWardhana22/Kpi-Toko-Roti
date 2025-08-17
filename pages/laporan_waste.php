<?php
// --- 1. Logika Filter Tanggal ---
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-29 days', strtotime($tanggal_akhir)));
$periode_params = [$tanggal_awal, $tanggal_akhir];

// --- 2. Query Utama yang Dirombak untuk Analisis Mendalam ---
// Query ini menghitung biaya kerugian berdasarkan bahan baku yang benar-benar digunakan pada setiap perintah kerja.
$sql = "
    WITH BiayaPerPK AS (
        -- Subquery untuk menghitung biaya bahan baku per unit produk untuk setiap Perintah Kerja
        SELECT
            pp.id_perintah_kerja,
            SUM(pp.jumlah_digunakan * sb.harga_per_satuan_dasar) / pk.jumlah_direncanakan AS biaya_per_unit
        FROM perintah_kerja_penggunaan_batch pp
        JOIN perintah_kerja pk ON pp.id_perintah_kerja = pk.id_perintah_kerja
        JOIN stok_batch sb ON pp.kode_batch_bahan = sb.kode_batch
        WHERE pk.status = 'Selesai' AND pk.jumlah_gagal > 0 AND DATE(pk.tanggal_selesai) BETWEEN ? AND ?
        GROUP BY pp.id_perintah_kerja, pk.jumlah_direncanakan
    )
    -- Query utama untuk mengambil rincian kegagalan
    SELECT
        pk.tanggal_selesai,
        pk.id_perintah_kerja,
        p.nama_produk,
        pk.jumlah_gagal,
        (pk.jumlah_gagal * COALESCE(b.biaya_per_unit, 0)) AS total_biaya_rugi
    FROM perintah_kerja pk
    JOIN produk p ON pk.id_produk = p.id_produk
    LEFT JOIN BiayaPerPK b ON pk.id_perintah_kerja = b.id_perintah_kerja
    WHERE pk.status = 'Selesai' AND pk.jumlah_gagal > 0
      AND DATE(pk.tanggal_selesai) BETWEEN ? AND ?
    ORDER BY pk.tanggal_selesai DESC
";

// Kita perlu melewatkan parameter tanggal dua kali karena digunakan di subquery dan query utama
$stmt = $pdo->prepare($sql);
$stmt->execute([$tanggal_awal, $tanggal_akhir, $tanggal_awal, $tanggal_akhir]);
$data_rincian_gagal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. Agregasi Data untuk KPI & Grafik ---
$total_gagal_pcs = 0;
$total_biaya_rugi_global = 0;
$gagal_per_produk_qty = [];
$gagal_per_produk_biaya = [];

foreach ($data_rincian_gagal as $data) {
    $total_gagal_pcs += $data['jumlah_gagal'];
    $total_biaya_rugi_global += $data['total_biaya_rugi'];
    
    // Agregasi per produk untuk grafik
    if (!isset($gagal_per_produk_qty[$data['nama_produk']])) {
        $gagal_per_produk_qty[$data['nama_produk']] = 0;
        $gagal_per_produk_biaya[$data['nama_produk']] = 0;
    }
    $gagal_per_produk_qty[$data['nama_produk']] += $data['jumlah_gagal'];
    $gagal_per_produk_biaya[$data['nama_produk']] += $data['total_biaya_rugi'];
}

// Mengurutkan produk dari yang paling banyak gagal
arsort($gagal_per_produk_qty);
arsort($gagal_per_produk_biaya);

// Menyiapkan data untuk Chart.js
$chart_qty_labels = array_keys($gagal_per_produk_qty);
$chart_qty_data = array_values($gagal_per_produk_qty);
$chart_biaya_labels = array_keys($gagal_per_produk_biaya);
$chart_biaya_data = array_values($gagal_per_produk_biaya);
?>

<div class="container-fluid py-3 px-4">
    <div class="card mb-4">
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
        <div class="col-lg-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Produk Gagal</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_gagal_pcs); ?> Pcs</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-trash fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Estimasi Biaya Kerugian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($total_biaya_rugi_global, 0, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Produk Paling Sering Gagal (berdasarkan Kuantitas)</h6></div>
                <div class="card-body">
                    <?php if (empty($data_rincian_gagal)): ?>
                        <div class="text-center p-5 text-muted">Tidak ada data produk gagal pada periode ini.</div>
                    <?php else: ?>
                        <div style="height: 350px;"><canvas id="wasteBarChartQty"></canvas></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
             <div class="card shadow h-100">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Komposisi Biaya Kerugian per Produk</h6></div>
                <div class="card-body">
                     <?php if (empty($data_rincian_gagal)): ?>
                        <div class="text-center p-5 text-muted">Tidak ada data produk gagal pada periode ini.</div>
                    <?php else: ?>
                        <div style="height: 350px;"><canvas id="wastePieChartBiaya"></canvas></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Tabel Rincian Semua Produk Gagal</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>ID Perintah</th>
                            <th>Nama Produk</th>
                            <th class="text-end">Jumlah Gagal</th>
                            <th class="text-end">Estimasi Biaya Rugi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_rincian_gagal)): ?>
                            <tr><td colspan="5" class="text-center p-5 bg-light">Tidak ada data produk gagal.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data_rincian_gagal as $data): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($data['tanggal_selesai'])); ?></td>
                                    <td>#<?php echo htmlspecialchars($data['id_perintah_kerja']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_produk']); ?></td>
                                    <td class="text-end"><?php echo htmlspecialchars($data['jumlah_gagal']); ?> Pcs</td>
                                    <td class="text-end text-danger fw-bold">Rp <?php echo number_format($data['total_biaya_rugi'], 0, ',', '.'); ?></td>
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
        <?php if (!empty($data_rincian_gagal)): ?>
            // Grafik Bar untuk Kuantitas Gagal
            new Chart(document.getElementById('wasteBarChartQty'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_qty_labels); ?>,
                    datasets: [{
                        label: 'Total Gagal (Pcs)',
                        data: <?php echo json_encode($chart_qty_data); ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Membuat bar menjadi horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true } },
                    plugins: { legend: { display: false } }
                }
            });

            // Grafik Pie untuk Komposisi Biaya
            new Chart(document.getElementById('wastePieChartBiaya'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($chart_biaya_labels); ?>,
                    datasets: [{
                        label: 'Biaya Kerugian (Rp)',
                        data: <?php echo json_encode($chart_biaya_data); ?>,
                        backgroundColor: ['#F44336', '#FFC107', '#4A90E2', '#4CAF50', '#9C27B0', '#FF9800'],
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