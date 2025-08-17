<?php
// pages/produksi_detail.php

// Ambil ID dari URL, pastikan itu angka
$id_perintah_kerja = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_perintah_kerja) {
    echo "ID tidak valid.";
    exit;
}

// Query untuk mengambil data utama perintah kerja
$stmt_pk = $pdo->prepare("
    SELECT pk.*, p.nama_produk 
    FROM perintah_kerja pk
    JOIN produk p ON pk.id_produk = p.id_produk
    WHERE pk.id_perintah_kerja = ?
");
$stmt_pk->execute([$id_perintah_kerja]);
$pk = $stmt_pk->fetch(PDO::FETCH_ASSOC);

// Jika perintah kerja tidak ditemukan, hentikan
if (!$pk) {
    echo "Perintah kerja tidak ditemukan.";
    exit;
}

// Query untuk mengambil rincian bahan baku yang direncanakan untuk digunakan
$stmt_bahan = $pdo->prepare("
    SELECT pp.kode_batch_bahan, bb.nama_bahan, bb.satuan, pp.jumlah_digunakan
    FROM perintah_kerja_penggunaan_batch pp
    JOIN stok_batch sb ON pp.kode_batch_bahan = sb.kode_batch
    JOIN bahan_baku bb ON sb.id_bahan_baku = bb.id_bahan_baku
    WHERE pp.id_perintah_kerja = ?
");
$stmt_bahan->execute([$id_perintah_kerja]);
$bahan_list = $stmt_bahan->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">Detail Perintah Kerja #<?php echo htmlspecialchars($pk['id_perintah_kerja']); ?></h6>
            <a href="index.php?page=produksi_daftar" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-3"><strong>Produk:</strong><br><?php echo htmlspecialchars($pk['nama_produk']); ?></p>
                    <p class="mb-3"><strong>Jumlah Direncanakan:</strong><br><?php echo number_format($pk['jumlah_direncanakan']); ?> unit</p>
                    <?php if ($pk['status'] == 'Selesai'): ?>
                        <p class="mb-3"><strong>Hasil Produksi:</strong><br><span class="text-success fw-bold"><?php echo htmlspecialchars($pk['jumlah_sukses']); ?> Sukses</span>, <span class="text-danger fw-bold"><?php echo htmlspecialchars($pk['jumlah_gagal']); ?> Gagal</span></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <p class="mb-3"><strong>Tanggal Dibuat:</strong><br><?php echo date('d M Y, H:i', strtotime($pk['tanggal_dibuat'])); ?></p>
                    <p class="mb-3"><strong>Status:</strong><br>
                        <?php
                            $status = $pk['status'];
                            $badge_class = 'bg-secondary';
                            if ($status == 'Direncanakan') $badge_class = 'bg-info';
                            if ($status == 'Berlangsung') $badge_class = 'bg-warning text-dark';
                            if ($status == 'Selesai') $badge_class = 'bg-success';
                            if ($status == 'Dibatalkan') $badge_class = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badge_class; ?> fs-6"><?php echo htmlspecialchars($status); ?></span>
                    </p>
                </div>
                 <?php if (!empty($pk['catatan'])): ?>
                <div class="col-12"><p class="mb-0"><strong>Catatan:</strong><br><?php echo nl2br(htmlspecialchars($pk['catatan'])); ?></p></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card-footer text-end">
            <?php if ($pk['status'] == 'Direncanakan'): ?>
                <form action="index.php?page=produksi_aksi_mulai" method="POST" class="d-inline">
                    <input type="hidden" name="id_perintah_kerja" value="<?php echo $pk['id_perintah_kerja']; ?>">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Anda yakin ingin memulai produksi ini? Stok bahan baku akan langsung dikurangi dan tidak dapat dibatalkan.')">
                        Mulai Produksi & Kurangi Stok
                    </button>
                </form>
            <?php elseif ($pk['status'] == 'Berlangsung'): ?>
                <form action="index.php?page=produksi_aksi_selesai" method="POST" onsubmit="return validasiHasil()">
                    <input type="hidden" name="id_perintah_kerja" value="<?php echo $pk['id_perintah_kerja']; ?>">
                    
                    <div class="mb-3 text-start">
                        <label for="kode_batch" class="form-label fw-bold">Kode Batch Produksi (Custom)</label>
                        <input type="text" class="form-control" name="kode_batch" id="kode_batch" placeholder="Contoh: KUE-STR-160825-01" required>
                    </div>

                    <div class="row align-items-end justify-content-end">
                        <div class="col-md-3 mb-2">
                            <label for="jumlah_sukses" class="form-label d-block text-start">Jumlah Sukses</label>
                            <input type="number" class="form-control" name="jumlah_sukses" id="jumlah_sukses" min="0" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="jumlah_gagal" class="form-label d-block text-start">Jumlah Gagal</label>
                            <input type="number" class="form-control" name="jumlah_gagal" id="jumlah_gagal" min="0" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label d-block text-start invisible">Aksi</label> 
                            <button type="submit" class="btn btn-info w-100">Selesaikan Produksi</button>
                        </div>
                    </div>
                </form>
                <script>
                function validasiHasil() {
                    const jumlahDirencanakan = <?php echo $pk['jumlah_direncanakan']; ?>;
                    const jumlahSukses = parseInt(document.getElementById('jumlah_sukses').value) || 0;
                    const jumlahGagal = parseInt(document.getElementById('jumlah_gagal').value) || 0;

                    if ((jumlahSukses + jumlahGagal) > jumlahDirencanakan) {
                        alert('Error: Total jumlah Sukses dan Gagal (' + (jumlahSukses + jumlahGagal) + ') tidak boleh melebihi Jumlah yang Direncanakan (' + jumlahDirencanakan + ').');
                        return false;
                    }
                    return true;
                }
                </script>
            <?php else: ?>
                <p class="mb-0 text-muted">Tidak ada aksi yang bisa dilakukan.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0">Bahan Baku yang Digunakan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Kode Batch</th>
                            <th>Nama Bahan</th>
                            <th class="text-end">Jumlah Digunakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bahan_list as $bahan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bahan['kode_batch_bahan']); ?></td>
                            <td><?php echo htmlspecialchars($bahan['nama_bahan']); ?></td>
                            <td class="text-end"><?php echo number_format($bahan['jumlah_digunakan'], 2) . ' ' . htmlspecialchars($bahan['satuan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>