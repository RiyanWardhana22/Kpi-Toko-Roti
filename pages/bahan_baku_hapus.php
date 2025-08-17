<?php
if (isset($_GET['id'])) {
            $id_bahan_baku = $_GET['id'];
            try {
                        $stmt = $pdo->prepare("DELETE FROM bahan_baku WHERE id_bahan_baku = ?");
                        $stmt->execute([$id_bahan_baku]);
                        redirect(base_url('index.php?page=bahan_baku&status=dihapus'));
            } catch (PDOException $e) {
                        redirect(base_url('index.php?page=bahan_baku&status=gagal'));
            }
} else {
            redirect(base_url('index.php?page=bahan_baku'));
}
