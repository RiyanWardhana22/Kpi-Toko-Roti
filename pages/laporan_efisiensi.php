<?php
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days', strtotime($tanggal_akhir)));
$filter_produk = isset($_GET['produk']) && !empty($_GET['produk']) ? $_GET['produk'] : null;

$stmt_produk_list = $pdo->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
$produk_list = $stmt_produk_list->fetchAll(PDO::FETCH_ASSOC);

$params = [$tanggal_awal, $tanggal_akhir];
$sql = "
    SELECT
        p.nama_produk,
        bb.nama_bahan,
        bb.satuan,
        SUM(ppb.jumlah_aktual) as total_aktual,
        SUM(r.jumlah_standar * pl.jumlah_sukses) as total_standar
    FROM produksi_log pl
    JOIN produksi_penggunaan_bahan ppb ON pl.id_log = ppb.id_log
    JOIN produk p ON pl.id_produk = p.id_produk
    JOIN bahan_baku bb ON ppb.id_bahan_baku = bb.id_bahan_baku
    JOIN resep r ON pl.id_produk = r.id_produk AND ppb.id_bahan_baku = r.id_bahan_baku
    WHERE DATE(pl.tanggal_aktual) BETWEEN ? AND ?
";

if ($filter_produk) {
    $sql .= " AND p.id_produk = ?";
    $params[] = $filter_produk;
}

$sql .= " GROUP BY p.id_produk, p.nama_produk, ppb.id_bahan_baku, bb.nama_bahan, bb.satuan ORDER BY p.nama_produk, bb.nama_bahan";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Laporan Efisiensi Bahan Baku</h1>
    <p class="mb-4">Bandingkan penggunaan bahan baku standar (sesuai resep & jumlah produksi sukses) dengan penggunaan aktual.</p>

    <div class="card shadow mb-4">
        <div class="card-header">Filter Laporan</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="laporan_efisiensi">
                <div class="row align-items-end">
                    <div class="col-md-3"><label>Tanggal Awal</label><input type="date" name="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-md-3"><label>Tanggal Akhir</label><input type="date" name="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-md-4">
                        <label>Produk</label>
                        <select name="produk" class="form-control">
                            <option value="">-- Semua Produk --</option>
                            <?php foreach ($produk_list as $p): ?>
                                <option value="<?php echo $p['id_produk']; ?>" <?php echo ($filter_produk == $p['id_produk']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nama_produk']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Terapkan</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Hasil Analisis Efisiensi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Penggunaan Standar</th>
                            <th class="text-end">Penggunaan Aktual</th>
                            <th class="text-end">Selisih (Variance)</th>
                            <th class="text-end">Efisiensi (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data produksi yang sesuai dengan filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporan_data as $data): ?>
                                <?php
                                $standar = $data['total_standar'];
                                $aktual = $data['total_aktual'];
                                $selisih = $aktual - $standar;
                                $efisiensi = ($standar > 0 && $aktual > 0) ? ($standar / $aktual) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['nama_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_bahan']); ?></td>
                                    <td class="text-end"><?php echo number_format($standar, 3) . ' ' . htmlspecialchars($data['satuan']); ?></td>
                                    <td class="text-end"><?php echo number_format($aktual, 3) . ' ' . htmlspecialchars($data['satuan']); ?></td>
                                    <td class="text-end">
                                        <span class="badge bg-<?php echo ($selisih > 0) ? 'danger' : 'success'; ?>">
                                            <?php echo number_format($selisih, 3) . ' ' . htmlspecialchars($data['satuan']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-<?php echo ($efisiensi < 95) ? 'warning' : 'info'; ?>">
                                            <?php echo number_format($efisiensi, 2) . '%'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <strong>Keterangan:</strong>
                    <ul>
                        <li><strong>Penggunaan Standar:</strong> Kebutuhan bahan sesuai resep dikalikan jumlah produk sukses.</li>
                        <li><strong>Selisih (Variance):</strong> `Aktual - Standar`. Nilai positif (merah) berarti pemborosan. Nilai negatif (hijau) berarti penghematan.</li>
                        <li><strong>Efisiensi:</strong> `(Standar / Aktual) * 100%`. Nilai di atas 100% berarti hemat, di bawah 100% berarti boros.</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>