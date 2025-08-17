<?php
// pages/stok_harian.php

// --- LOGIKA PHP (Lengkap, Final, dan Efisien) ---

// Untuk menampilkan notifikasi dari halaman lain
$status = isset($_GET['status']) ? $_GET['status'] : '';
$notif_sukses = '';
$notif_gagal = '';
if ($status === 'sukses_hapus') {
    $notif_sukses = 'Batch stok berhasil dihapus.';
} elseif ($status === 'gagal_hapus_digunakan') {
    $notif_gagal = 'Gagal menghapus! Batch ini sudah tercatat dalam riwayat produksi.';
}

// 1. Query untuk mengambil SEMUA batch yang tersedia
$stmt_semua = $pdo->query("
    SELECT 
        sb.*, 
        bb.nama_bahan, 
        bb.satuan AS satuan_dasar,
        DATEDIFF(sb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    FROM stok_batch sb
    JOIN bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE sb.sisa_dasar > 0
    ORDER BY sb.tanggal_kadaluarsa ASC
");
$semua_batch_tersedia = $stmt_semua->fetchAll(PDO::FETCH_ASSOC);

// 2. Query SPESIFIK untuk mengambil HANYA batch yang akan kadaluarsa (<= 3 hari)
$stmt_kadaluarsa = $pdo->query("
    SELECT 
        sb.kode_batch, bb.nama_bahan, sb.sisa_dasar, bb.satuan,
        DATEDIFF(sb.tanggal_kadaluarsa, CURDATE()) AS sisa_hari
    FROM stok_batch sb
    JOIN bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE sb.sisa_dasar > 0 AND DATEDIFF(sb.tanggal_kadaluarsa, CURDATE()) <= 3
    ORDER BY sb.tanggal_kadaluarsa ASC
");
$batch_kadaluarsa = $stmt_kadaluarsa->fetchAll(PDO::FETCH_ASSOC);

/**
 * Fungsi bantuan untuk format angka agar rapi.
 * Menghilangkan ,00 jika tidak ada desimal.
 */
function format_angka($angka)
{
    if (floor($angka) == $angka) {
        return number_format($angka, 0, ',', '.');
    }
    return number_format($angka, 2, ',', '.');
}

?>

<div class="container-fluid py-3">
    <?php if ($notif_sukses): ?>
        <div class="alert alert-success"><?php echo $notif_sukses; ?></div>
    <?php endif; ?>
    <?php if ($notif_gagal): ?>
        <div class="alert alert-danger"><?php echo $notif_gagal; ?></div>
    <?php endif; ?>

    <?php if (!empty($batch_kadaluarsa)): ?>
        <div class="card card-danger mb-4 shadow-sm">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold"><i class="fa-solid fa-triangle-exclamation"></i> Peringatan: Stok Mendekati Kadaluarsa!</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Kode Batch</th>
                                <th>Nama Bahan</th>
                                <th class="text-end">Sisa Stok</th>
                                <th class="text-center">Kadaluarsa Dalam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batch_kadaluarsa as $batch): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($batch['kode_batch']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($batch['nama_bahan']); ?></td>
                                    <td class="text-end"><?php echo format_angka($batch['sisa_dasar']); ?> <?php echo htmlspecialchars($batch['satuan']); ?></td>
                                    <td class="text-center">
                                        <?php
                                        $sisa_hari_text = $batch['sisa_hari'] . ' hari';
                                        if ($batch['sisa_hari'] < 0) $sisa_hari_text = 'Lewat ' . abs($batch['sisa_hari']) . ' hari';
                                        if ($batch['sisa_hari'] == 0) $sisa_hari_text = 'Hari Ini';
                                        if ($batch['sisa_hari'] == 1) $sisa_hari_text = 'Besok';
                                        ?>
                                        <span class="badge <?php echo $batch['sisa_hari'] < 0 ? 'bg-dark' : 'bg-danger'; ?>">
                                            <?php echo $sisa_hari_text; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Stok & Batch Tersedia</h6>
            <a href="index.php?page=tambah_stok_form" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-plus"></i> Tambah Stok Baru</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Batch</th>
                            <th>Nama Bahan</th>
                            <th class="text-end">Harga Pokok / Satuan Dasar</th>
                            <th class="text-end">Jumlah Awal</th>
                            <th class="text-end">Sisa Stok</th>
                            <th class="text-center">Tgl Masuk</th>
                            <th class="text-center">Tgl Kadaluarsa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($semua_batch_tersedia)): ?>
                            <tr>
                                <td colspan="8" class="text-center p-5 bg-light">Belum ada data stok yang tersedia. Silakan tambah stok baru.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($semua_batch_tersedia as $batch): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($batch['kode_batch']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($batch['nama_bahan']); ?></td>

                                    <td class="text-end">
                                        <?php echo 'Rp ' . format_angka($batch['harga_per_satuan_dasar']) . ' / ' . htmlspecialchars($batch['satuan_dasar']); ?>
                                    </td>

                                    <td class="text-end"><?php echo htmlspecialchars($batch['jumlah_display']) . ' ' . htmlspecialchars($batch['satuan_display']); ?></td>
                                    <td class="text-end fw-bold"><?php echo format_angka($batch['sisa_dasar']); ?> <?php echo htmlspecialchars($batch['satuan_dasar']); ?></td>
                                    <td class="text-center"><?php echo date('d M Y', strtotime($batch['tanggal_masuk'])); ?></td>
                                    <td class="text-center"><?php echo date('d M Y', strtotime($batch['tanggal_kadaluarsa'])); ?></td>
                                    <td class="text-center">
                                        <a href="<?php echo base_url('index.php?page=hapus_stok_batch&id=' . $batch['id_batch']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus batch ini? Aksi ini tidak bisa dibatalkan.');" title="Hapus Batch">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
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