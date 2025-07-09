<?php
if ($_SESSION['role'] !== 'Admin') {
            redirect(base_url('index.php?page=dashboard&status=terlarang'));
}

if (isset($_GET['id'])) {
            $id_to_delete = $_GET['id'];
            if ($id_to_delete == $_SESSION['id_pengguna']) {
                        redirect(base_url('index.php?page=pengguna&status_hapus=gagal'));
            }

            try {
                        $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
                        $stmt->execute([$id_to_delete]);
                        redirect(base_url('index.php?page=pengguna&status_hapus=sukses'));
            } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
                        redirect(base_url('index.php?page=pengguna'));
            }
}
