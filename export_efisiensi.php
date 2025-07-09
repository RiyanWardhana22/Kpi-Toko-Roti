<?php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}
if (!isset($_SESSION['id_pengguna'])) {
            exit('Akses ditolak.');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Efisiensi');

$headers = ['Nama Produk', 'Bahan Baku', 'Penggunaan Standar', 'Penggunaan Aktual', 'Selisih (Variance)', 'Efisiensi (%)'];
$sheet->fromArray($headers, null, 'A1');

$rowIndex = 2;
foreach ($laporan_data as $data) {
            $standar = $data['total_standar'];
            $aktual = $data['total_aktual'];
            $selisih = $aktual - $standar;
            $efisiensi = ($standar > 0 && $aktual > 0) ? ($standar / $aktual) * 100 : 0;

            $sheet->setCellValue('A' . $rowIndex, $data['nama_produk']);
            $sheet->setCellValue('B' . $rowIndex, $data['nama_bahan']);
            $sheet->setCellValue('C' . $rowIndex, number_format($standar) . ' ' . $data['satuan']);
            $sheet->setCellValue('D' . $rowIndex, number_format($aktual) . ' ' . $data['satuan']);
            $sheet->setCellValue('E' . $rowIndex, number_format($selisih) . ' ' . $data['satuan']);

            $sheet->setCellValue('F' . $rowIndex, number_format($efisiensi) . '%');
            $rowIndex++;
}

$lastRow = $rowIndex - 1;
$sheet->getStyle("A1:F1")->getFont()->setBold(true);
$sheet->getStyle("A1:F$lastRow")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle("A1:F$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:F$lastRow")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = "laporan_efisiensi_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
