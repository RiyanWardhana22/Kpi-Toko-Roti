<?php

$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
try {
    $stmt = $pdo->prepare("SELECT pr.id_rencana, p.nama_produk, pr.target_produksi, pl.id_log FROM produksi_rencana pr JOIN produk p ON pr.id_produk = p.id_produk LEFT JOIN produksi_log pl ON pr.id_rencana = pl.id_rencana WHERE pr.tanggal_produksi = ? ORDER BY p.nama_produk ASC");
    $stmt->execute([$tanggal_filter]);
    $rencana_produksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $rencana_produksi = [];
}
?>

<div class="container-fluid py-3">

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            if ($_GET['status'] == 'sukses') echo "Rencana produksi berhasil disimpan!";
            if ($_GET['status'] == 'input_sukses') echo "Hasil produksi berhasil dicatat!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h6 class="m-0">Rencana Produksi</h6>
            <div class="d-flex flex-wrap align-items-center">
                <form method="GET" class="d-flex me-2">
                    <input type="hidden" name="page" value="produksi">
                    <input type="date" name="tanggal" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tanggal_filter); ?>">
                    <button type="submit" class="btn btn-sm btn-info ms-2">Filter</button>
                </form>
                <a href="<?php echo base_url('index.php?page=produksi_rencana_form'); ?>" class="btn btn-primary btn-sm">
                    + Tambah Rencana
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Target Produksi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rencana_produksi)): ?>
                            <tr>
                                <td colspan="4" class="text-center bg-light">Belum ada rencana produksi untuk tanggal ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rencana_produksi as $rencana): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($rencana['nama_produk']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($rencana['target_produksi']); ?> Pcs
                                    </td>
                                    <td>
                                        <?php if ($rencana['id_log']): ?>
                                            <span class="badge bg-success">Sudah Diinput</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Menunggu Hasil</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rencana['id_log']): ?>
                                            <a href="#" class="btn btn-secondary btn-sm disabled">Input Hasil</a>
                                        <?php else: ?>
                                            <a href="<?php echo base_url('index.php?page=produksi_input_form&id_rencana=' . $rencana['id_rencana']); ?>" class="btn btn-success btn-sm">Input Hasil</a>
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