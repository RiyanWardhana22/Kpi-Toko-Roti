<?php
if (isset($_GET['id'])) {
            $id_produk = $_GET['id'];
            try {
                        $stmt_resep = $pdo->prepare("DELETE FROM resep WHERE id_produk = ?");
                        $stmt_resep->execute([$id_produk]);

                        $stmt_produk = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
                        $stmt_produk->execute([$id_produk]);

                        redirect(base_url('index.php?page=produk&status=dihapus'));
            } catch (PDOException $e) {
                        redirect(base_url('index.php?page=produk&status=gagal'));
            }
} else {
            redirect(base_url('index.php?page=produk'));
}
