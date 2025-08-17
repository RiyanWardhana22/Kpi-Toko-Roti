<?php
if (isset($_GET['id_resep']) && isset($_GET['id_produk'])) {
            $id_resep = $_GET['id_resep'];
            $id_produk = $_GET['id_produk'];

            try {
                        $stmt = $pdo->prepare("DELETE FROM resep WHERE id_resep = ?");
                        $stmt->execute([$id_resep]);
                        redirect(base_url('index.php?page=resep&id_produk=' . $id_produk . '&status=sukses_hapus'));
            } catch (PDOException $e) {
                        redirect(base_url('index.php?page=resep&id_produk=' . $id_produk . '&status=gagal'));
            }
} else {
            redirect(base_url('index.php?page=dashboard'));
}
