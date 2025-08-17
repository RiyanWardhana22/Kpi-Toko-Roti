<?php
// pages/produk_form.php

// --- LOGIKA UNTUK MENANGANI MODE EDIT & TAMBAH ---
$is_edit_mode = isset($_GET['id']) && !empty($_GET['id']);
$id_produk_edit = null;
$produk_data = ['nama_produk' => '', 'masa_simpan_hari' => '']; 
$resep_data = [];

if ($is_edit_mode) {
    $id_produk_edit = (int)$_GET['id'];
    
    $stmt_produk = $pdo->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt_produk->execute([$id_produk_edit]);
    $produk_data = $stmt_produk->fetch(PDO::FETCH_ASSOC);

    $stmt_resep = $pdo->prepare("SELECT * FROM resep WHERE id_produk = ?");
    $stmt_resep->execute([$id_produk_edit]);
    $resep_data = $stmt_resep->fetchAll(PDO::FETCH_ASSOC);
}

$stmt_bahan = $pdo->query("SELECT id_bahan_baku, nama_bahan, satuan FROM bahan_baku ORDER BY nama_bahan ASC");
$semua_bahan_baku = $stmt_bahan->fetchAll(PDO::FETCH_ASSOC);

// --- LOGIKA PENYIMPANAN DATA (UPDATE & INSERT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_edit_from_post = isset($_POST['id_produk']) && !empty($_POST['id_produk']);
    
    $nama_produk = $_POST['nama_produk'];
    $masa_simpan_hari = $_POST['masa_simpan_hari'];
    $resep_bahan = isset($_POST['resep']) ? $_POST['resep'] : [];

    try {
        $pdo->beginTransaction();

        if ($is_edit_from_post) {
            $id_produk_target = (int)$_POST['id_produk'];
            $stmt_produk = $pdo->prepare("UPDATE produk SET nama_produk = ?, masa_simpan_hari = ? WHERE id_produk = ?");
            $stmt_produk->execute([$nama_produk, $masa_simpan_hari, $id_produk_target]);

            $stmt_delete_resep = $pdo->prepare("DELETE FROM resep WHERE id_produk = ?");
            $stmt_delete_resep->execute([$id_produk_target]);

        } else {
            $stmt_produk = $pdo->prepare("INSERT INTO produk (nama_produk, masa_simpan_hari) VALUES (?, ?)");
            $stmt_produk->execute([$nama_produk, $masa_simpan_hari]);
            $id_produk_target = $pdo->lastInsertId();
        }

        $stmt_resep = $pdo->prepare("INSERT INTO resep (id_produk, id_bahan_baku, jumlah) VALUES (?, ?, ?)");
        foreach ($resep_bahan as $bahan) {
            if (!empty($bahan['id_bahan_baku']) && !empty($bahan['jumlah']) && $bahan['jumlah'] > 0) {
                $stmt_resep->execute([$id_produk_target, $bahan['id_bahan_baku'], $bahan['jumlah']]);
            }
        }

        $pdo->commit();
        
        // ===================================================================
        //               PERBAIKAN UTAMA MENGGUNAKAN BASE_URL
        // ===================================================================
        // Membuat URL absolut yang pasti benar, tidak akan salah folder lagi.
        header('Location: ' . BASE_URL . 'index.php?page=produk&status=sukses');
        exit();
        // ===================================================================

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0"><?php echo $is_edit_mode ? 'Edit Produk & Resepnya' : 'Tambah Produk Baru & Resepnya'; ?></h6>
        </div>
        <form method="POST">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="id_produk" value="<?php echo $id_produk_edit; ?>">
            <?php endif; ?>

            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($produk_data['nama_produk']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="masa_simpan_hari" class="form-label">Masa Simpan (dalam Hari)</label>
                    <input type="number" class="form-control" id="masa_simpan_hari" name="masa_simpan_hari" value="<?php echo htmlspecialchars($produk_data['masa_simpan_hari'] ?? '0'); ?>" min="0" required>
                    <small class="form-text text-muted">Berapa hari produk ini layak jual setelah diproduksi?</small>
                </div>
                <hr>
                
                <h6 class="mb-3">Resep Bahan Baku</h6>
                <div id="resep-container">
                    </div>

                <button type="button" id="tambah-bahan-btn" class="btn btn-sm btn-outline-success mt-2">
                    <i class="fa-solid fa-plus"></i> Tambah Bahan
                </button>
            </div>

            <div class="card-footer text-end">
                <a href="<?php echo BASE_URL; ?>index.php?page=produk" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<template id="resep-row-template">
    <div class="row align-items-center resep-row mb-2">
        <div class="col-md-6">
            <select class="form-select" name="resep[INDEX][id_bahan_baku]" required>
                <option value="">-- Pilih Bahan Baku --</option>
                <?php foreach ($semua_bahan_baku as $bahan): ?>
                    <option value="<?php echo $bahan['id_bahan_baku']; ?>" data-satuan="<?php echo htmlspecialchars($bahan['satuan']); ?>">
                        <?php echo htmlspecialchars($bahan['nama_bahan']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="number" step="any" class="form-control" name="resep[INDEX][jumlah]" placeholder="Jumlah" required>
                <span class="input-group-text satuan-text">Satuan</span>
            </div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm hapus-bahan-btn w-100">
                <i class="fa-solid fa-trash"></i> Hapusa
            </button>
        </div>
    </div>
</template>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('resep-container');
    const addButton = document.getElementById('tambah-bahan-btn');
    const template = document.getElementById('resep-row-template');
    let resepIndex = 0;

    function addResepRow(data = null) {
        const fragment = template.content.cloneNode(true);
        const newRow = fragment.querySelector('.resep-row');
        newRow.innerHTML = newRow.innerHTML.replace(/\[INDEX\]/g, `[${resepIndex}]`);
        const select = newRow.querySelector('select');
        const jumlahInput = newRow.querySelector('input[type="number"]');
        select.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuanElement = this.closest('.resep-row').querySelector('.satuan-text');
            if (satuanElement) {
                satuanElement.textContent = selectedOption.getAttribute('data-satuan') || 'Satuan';
            }
        });
        const hapusButton = newRow.querySelector('.hapus-bahan-btn');
        hapusButton.addEventListener('click', function() {
            this.closest('.resep-row').remove();
        });
        if (data) {
            select.value = data.id_bahan_baku;
            jumlahInput.value = data.jumlah;
            select.dispatchEvent(new Event('change'));
        }
        container.appendChild(newRow);
        resepIndex++;
    }

    const resepDataFromPHP = <?php echo json_encode($resep_data); ?>;
    if (resepDataFromPHP && resepDataFromPHP.length > 0) {
        resepDataFromPHP.forEach(function(resep) {
            addResepRow(resep);
        });
    } else {
        addResepRow();
    }
    addButton.addEventListener('click', function() {
        addResepRow();
    });
});
</script>