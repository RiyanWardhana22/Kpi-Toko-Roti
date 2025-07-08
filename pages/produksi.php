<?php
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT pr.id_rencana, p.nama_produk, pr.target_produksi, pl.id_log
        FROM produksi_rencana pr
        JOIN produk p ON pr.id_produk = p.id_produk
        LEFT JOIN produksi_log pl ON pr.id_rencana = pl.id_rencana
        WHERE pr.tanggal_produksi = ?
        ORDER BY p.nama_produk ASC
    ");
    $stmt->execute([$tanggal_filter]);
    $rencana_produksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $rencana_produksi = [];
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Manajemen Produksi</h1>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            if ($_GET['status'] == 'sukses') echo "Rencana produksi berhasil disimpan!";
            if ($_GET['status'] == 'input_sukses') echo "Hasil produksi berhasil dicatat!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Rencana Produksi untuk Tanggal: <?php echo htmlspecialchars($tanggal_filter); ?></h6>
            <a href="<?php echo base_url('index.php?page=produksi_rencana_form'); ?>" class="btn btn-primary btn-sm">Tambah Rencana</a>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <input type="hidden" name="page" value="produksi">
                <div class="row">
                    <div class="col-md-3">
                        <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal_filter); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info">Filter</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
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
                                <td colspan="4" class="text-center">Belum ada rencana produksi untuk tanggal ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rencana_produksi as $rencana): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rencana['nama_produk']); ?></td>
                                    <td><?php echo htmlspecialchars($rencana['target_produksi']); ?></td>
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