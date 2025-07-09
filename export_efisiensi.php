<?php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}
if (!isset($_SESSION['id_pengguna'])) {
            exit('Akses ditolak.');
}

require_once __DIR__ . '/config/database.php';

$tanggal_akhir = isset($_GET['akhir']) ? $_GET['akhir'] : date('Y-m-d');
$tanggal_awal = isset($_GET['awal']) ? $_GET['awal'] : date('Y-m-d', strtotime('-6 days', strtotime($tanggal_akhir)));
$filter_produk = isset($_GET['produk']) && !empty($_GET['produk']) ? $_GET['produk'] : null;

$params = [$tanggal_awal, $tanggal_akhir];
$sql = "
    SELECT p.nama_produk, bb.nama_bahan, bb.satuan,
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

$filename = "laporan_efisiensi_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Nama Produk', 'Bahan Baku', 'Penggunaan Standar', 'Penggunaan Aktual', 'Selisih (Variance)', 'Efisiensi (%)', 'Satuan']);

foreach ($laporan_data as $data) {
            $standar = $data['total_standar'];
            $aktual = $data['total_aktual'];
            $selisih = $aktual - $standar;
            $efisiensi = ($standar > 0 && $aktual > 0) ? ($standar / $aktual) * 100 : 0;

            $row = [
                        $data['nama_produk'],
                        $data['nama_bahan'],
                        number_format($standar),
                        number_format($aktual),
                        number_format($selisih),
                        number_format($efisiensi) . '%',
                        $data['satuan']
            ];
            fputcsv($output, $row);
}

fclose($output);
exit();
