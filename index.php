<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
            session_start();
}

if (!isset($_SESSION['id_pengguna'])) {
            header('Location: login.php');
            exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/functions.php';

$settings = load_settings($pdo);

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages =
            [
                        'dashboard',
                        'produk',
                        'produk_form',
                        'produk_hapus',
                        'bahan_baku',
                        'bahan_baku_form',
                        'bahan_baku_hapus',
                        'resep',
                        'resep_hapus',
                        'produksi',
                        'produksi_rencana_form',
                        'produksi_input_form',
                        'laporan',
                        'laporan_waste',
                        'laporan_efisiensi',
                        'laporan_pencapaian',
                        'pengguna',
                        'pengguna_form',
                        'pengguna_hapus',
                        'settings'
            ];

$page_title = ucfirst(str_replace('_', ' ', $page));
include __DIR__ . '/templates/header.php';

if (in_array($page, $allowed_pages) && file_exists(__DIR__ . '/pages/' . $page . '.php')) {
            include __DIR__ . '/pages/' . $page . '.php';
} else {
            echo '<div class="alert alert-danger">Error: Halaman tidak ditemukan!</div>';
}

include __DIR__ . '/templates/footer.php';

ob_end_flush();
