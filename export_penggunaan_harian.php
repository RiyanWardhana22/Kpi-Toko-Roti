<?php
// Pastikan user sudah login
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['id_pengguna'])) exit('Akses ditolak.');

// ======================= PERBAIKAN PATH DI SINI =======================
// Menghapus '/../' karena file ini berada di root folder yang sama dengan vendor dan config
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
// ====================================================================

// Gunakan class dari PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Ambil tanggal dari parameter URL, defaultnya hari ini
$tanggal_laporan = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query baru yang lebih lengkap
$sql = "
    SELECT 
        pk.id_perintah_kerja,
        pk.tanggal_dimulai,
        p.nama_produk,
        bb.nama_bahan,
        pp.kode_batch_bahan,
        pp.jumlah_digunakan,
        bb.satuan,
        sb.harga_per_satuan_dasar,
        (pp.jumlah_digunakan * sb.harga_per_satuan_dasar) AS total_biaya
    FROM 
        perintah_kerja_penggunaan_batch pp
    JOIN 
        perintah_kerja pk ON pp.id_perintah_kerja = pk.id_perintah_kerja
    JOIN 
        produk p ON pk.id_produk = p.id_produk
    JOIN 
        stok_batch sb ON pp.kode_batch_bahan = sb.kode_batch
    JOIN 
        bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE 
        pk.status IN ('Berlangsung', 'Selesai', 'Dibatalkan') 
        AND DATE(pk.tanggal_dimulai) = ?
    ORDER BY 
        pk.id_perintah_kerja ASC, pk.tanggal_dimulai ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tanggal_laporan]);
$data_laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total biaya harian untuk summary
$total_biaya_harian = array_sum(array_column($data_laporan, 'total_biaya'));

// Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- PENATAAN HEADER & JUDUL ---
$sheet->setCellValue('A1', 'Laporan Rincian Penggunaan Bahan');
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', 'Tanggal Laporan: ' . date('d F Y', strtotime($tanggal_laporan)));
$sheet->mergeCells('A2:I2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- KARTU KPI / SUMMARY ---
$sheet->setCellValue('A4', 'Total Biaya Bahan Terpakai Hari Ini');
$sheet->mergeCells('A4:B4');
$sheet->getStyle('A4')->getFont()->setBold(true);
$sheet->setCellValue('C4', $total_biaya_harian);
$sheet->getStyle('C4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('C4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle('A4:C4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');

// --- HEADER TABEL ---
$headerRow = 6;
$sheet->setCellValue('A'.$headerRow, 'ID Perintah');
$sheet->setCellValue('B'.$headerRow, 'Waktu');
$sheet->setCellValue('C'.$headerRow, 'Untuk Produk');
$sheet->setCellValue('D'.$headerRow, 'Bahan Baku');
$sheet->setCellValue('E'.$headerRow, 'Dari Batch');
$sheet->setCellValue('F'.$headerRow, 'Jumlah');
$sheet->setCellValue('G'.$headerRow, 'Satuan');
$sheet->setCellValue('H'.$headerRow, 'Harga Satuan (Rp)');
$sheet->setCellValue('I'.$headerRow, 'Total Biaya (Rp)');

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A'.$headerRow.':I'.$headerRow)->applyFromArray($headerStyle);

// --- ISI DATA TABEL ---
$row = $headerRow + 1;
if (empty($data_laporan)) {
    $sheet->setCellValue('A'.$row, 'Tidak ada data penggunaan pada tanggal ini.');
    $sheet->mergeCells('A'.$row.':I'.$row);
} else {
    foreach ($data_laporan as $data) {
        $sheet->setCellValue('A' . $row, '#' . $data['id_perintah_kerja']);
        $sheet->setCellValue('B' . $row, date('H:i:s', strtotime($data['tanggal_dimulai'])));
        $sheet->setCellValue('C' . $row, $data['nama_produk']);
        $sheet->setCellValue('D' . $row, $data['nama_bahan']);
        $sheet->setCellValue('E' . $row, $data['kode_batch_bahan']);
        $sheet->setCellValue('F' . $row, $data['jumlah_digunakan']);
        $sheet->setCellValue('G' . $row, $data['satuan']);
        $sheet->setCellValue('H' . $row, $data['harga_per_satuan_dasar']);
        $sheet->setCellValue('I' . $row, $data['total_biaya']);

        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;
    }

    // --- BARIS TOTAL DI BAGIAN BAWAH ---
    $sheet->setCellValue('A'.$row, 'GRAND TOTAL');
    $sheet->mergeCells('A'.$row.':H'.$row);
    $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->setCellValue('I'.$row, '=SUM(I'.($headerRow + 1).':I'.($row - 1).')');
    $sheet->getStyle('I'.$row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
    $sheet->getStyle('A'.$row.':I'.$row)->getFont()->setBold(true);
}

// --- FINALISASI ---
foreach (range('A', 'I') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

$filename = 'Laporan_Penggunaan_Bahan_' . $tanggal_laporan . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>