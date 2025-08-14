<?php
// Tentukan tanggal filter, defaultnya adalah hari ini
$selected_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query untuk mengambil rencana produksi pada tanggal yang dipilih
// Kita gunakan LEFT JOIN ke produksi_log untuk mengecek status
$sql = "
    SELECT 
        pr.id_rencana,
        p.nama_produk,
        pr.target_produksi,
        pl.id_log 
    FROM produksi_rencana pr
    JOIN produk p ON pr.id_produk = p.id_produk
    LEFT JOIN produksi_log pl ON pr.id_rencana = pl.id_rencana
    WHERE pr.tanggal_produksi = ?
    ORDER BY p.nama_produk ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$selected_date]);
$rencana_produksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h6 class="m-0">Rencana Produksi</h6>
            <div class="d-flex align-items-center">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="page" value="produksi">
                    <input type="date" name="tanggal" class="form-control form-control-sm me-2" value="<?php echo htmlspecialchars($selected_date); ?>">
                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                </form>
                <a href="<?php echo base_url('index.php?page=produksi_rencana_form'); ?>" class="btn btn-primary btn-sm ms-2">+ Tambah</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>NAMA PRODUK</th>
                            <th class="text-end">TARGET PRODUKSI</th>
                            <th class="text-center">STATUS</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rencana_produksi)): ?>
                        <tr>
                            <td colspan="4" class="text-center bg-light p-5">Tidak ada rencana produksi pada tanggal ini.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($rencana_produksi as $rencana): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($rencana['nama_produk']); ?></div>
                            </td>
                            <td class="text-end"><?php echo htmlspecialchars($rencana['target_produksi']); ?> Pcs</td>
                            <td class="text-center">
                                <?php if (is_null($rencana['id_log'])): ?>
                                    <span class="badge bg-warning text-dark">Menunggu Hasil</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (is_null($rencana['id_log'])): ?>
                                    <a href="<?php echo base_url('index.php?page=produksi_input_form&id_rencana=' . $rencana['id_rencana']); ?>" class="btn btn-success btn-sm">Input Hasil</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Sudah Diinput</button>
                                <?php endif; ?>
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