<?php
// Mengambil filter tanggal, default 7 hari terakhir
$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days', strtotime($tanggal_akhir)));
$filter_produk = isset($_GET['produk']) && !empty($_GET['produk']) ? $_GET['produk'] : null;

// Mengambil daftar produk untuk dropdown filter
$stmt_produk_list = $pdo->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
$produk_list = $stmt_produk_list->fetchAll(PDO::FETCH_ASSOC);

// ======================= QUERY LAPORAN DENGAN KALKULASI BIAYA =======================
$params = [$tanggal_awal, $tanggal_akhir];
$sql = "
    SELECT
        p.nama_produk,
        bb.nama_bahan,
        bb.satuan,
        SUM(r.jumlah * pk.jumlah_sukses) AS total_standar,
        SUM(r.jumlah * pk.jumlah_direncanakan) AS total_aktual,
        -- Menghitung estimasi biaya kerugian berdasarkan harga beli terakhir
        SUM(r.jumlah * pk.jumlah_gagal * COALESCE(lp.harga_terbaru, 0)) AS biaya_kerugian
    FROM
        perintah_kerja pk
    JOIN
        produk p ON pk.id_produk = p.id_produk
    JOIN
        resep r ON p.id_produk = r.id_produk
    JOIN
        bahan_baku bb ON r.id_bahan_baku = bb.id_bahan_baku
    -- Subquery untuk mendapatkan harga beli terakhir untuk setiap bahan baku
    LEFT JOIN (
        SELECT
            sb1.id_bahan_baku,
            sb1.harga_per_satuan_dasar AS harga_terbaru
        FROM
            stok_batch sb1
        INNER JOIN (
            SELECT
                id_bahan_baku,
                MAX(tanggal_masuk) AS max_tanggal
            FROM
                stok_batch
            GROUP BY
                id_bahan_baku
        ) sb2 ON sb1.id_bahan_baku = sb2.id_bahan_baku AND sb1.tanggal_masuk = sb2.max_tanggal
        GROUP BY sb1.id_bahan_baku, sb1.harga_per_satuan_dasar
    ) AS lp ON r.id_bahan_baku = lp.id_bahan_baku
    WHERE
        pk.status = 'Selesai' 
        AND DATE(pk.tanggal_selesai) BETWEEN ? AND ?
";

if ($filter_produk) {
    $sql .= " AND p.id_produk = ?";
    $params[] = $filter_produk;
}

$sql .= " 
    GROUP BY
        p.id_produk, p.nama_produk, r.id_bahan_baku, bb.nama_bahan, bb.satuan
    HAVING 
        SUM(pk.jumlah_direncanakan) > 0 -- Hanya tampilkan jika ada produksi
    ORDER BY
        p.nama_produk, bb.nama_bahan
";
// =================================================================================

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3 px-4">
    <div class="card mb-4">
        <div class="card-header">Filter Laporan</div>
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="page" value="laporan_efisiensi">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0"><label>Tanggal Awal</label><input type="date" name="awal" class="form-control" value="<?php echo $tanggal_awal; ?>"></div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0"><label>Tanggal Akhir</label><input type="date" name="akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>"></div>
                    <div class="col-lg-4 col-md-6 mb-2 mb-lg-0"><label>Produk</label>
                        <select name="produk" class="form-control">
                            <option value="">-- Semua Produk --</option>
                            <?php foreach ($produk_list as $p): ?>
                                <option value="<?php echo $p['id_produk']; ?>" <?php echo ($filter_produk == $p['id_produk']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nama_produk']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6"><button type="submit" class="btn btn-primary btn-sm w-100">Terapkan</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">Hasil Analisis Efisiensi & Biaya</h6>
            <?php $export_query_string = http_build_query(['awal' => $tanggal_awal, 'akhir' => $tanggal_akhir, 'produk' => $filter_produk]); ?>
            <a href="export_efisiensi.php?<?php echo $export_query_string; ?>" class="btn btn-success btn-sm">+ Export ke Excel</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Bahan Baku</th>
                            <th class="text-end">Standar</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-end">Efisiensi</th>
                            <th class="text-end">Biaya Kerugian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_data)): ?>
                            <tr>
                                <td colspan="7" class="text-center bg-light p-5">Tidak ada data produksi yang sesuai dengan filter.</td>
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
                                    <td class="text-end"><?php echo number_format($standar, 2, ',', '.') . ' ' . htmlspecialchars($data['satuan']); ?></td>
                                    <td class="text-end"><?php echo number_format($aktual, 2, ',', '.') . ' ' . htmlspecialchars($data['satuan']); ?></td>
                                    <td class="text-end"><span class="badge bg-<?php echo ($selisih > 0) ? 'danger' : 'success'; ?>"><?php echo number_format($selisih, 2, ',', '.'); ?></span></td>
                                    <td class="text-end"><span class="badge bg-<?php echo ($efisiensi < 95) ? 'warning text-dark' : 'info'; ?>"><?php echo number_format($efisiensi, 1, ',', '.'); ?>%</span></td>
                                    <td class="text-end text-danger fw-bold">
                                        <?php if ($data['biaya_kerugian'] > 0): ?>
                                            Rp <?php echo number_format($data['biaya_kerugian'], 0, ',', '.'); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
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
                        <li><strong>Standar:</strong> Kebutuhan bahan sesuai resep dikalikan jumlah produk **sukses**.</li>
                        <li><strong>Aktual:</strong> Kebutuhan bahan sesuai resep dikalikan jumlah produk **direncanakan**.</li>
                        <li><strong>Selisih:</strong> `Aktual - Standar`. Nilai positif (merah) berarti pemborosan.</li>
                        <li><strong>Efisiensi:</strong> `(Standar / Aktual) * 100%`. Nilai di bawah 100% berarti ada pemborosan bahan.</li>
                        <li><strong>Biaya Kerugian:</strong> Estimasi nilai Rupiah dari bahan yang terbuang (Selisih) berdasarkan harga beli terakhir.</li>
                    </ul>
                </small>
            </div>
        </div>
    </div>
</div>