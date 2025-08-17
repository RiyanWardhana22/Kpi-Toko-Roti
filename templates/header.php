<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Grup halaman untuk menu dropdown
$data_master_pages = ['produk', 'produk_form', 'bahan_baku', 'bahan_baku_form', 'resep'];
$settings_pages = ['pengguna', 'pengguna_form', 'settings'];

// =====================================================================
// --- MODIFIKASI 1: 'produk_jadi' dipindahkan ke grup $produksi_pages ---
// =====================================================================
$stok_pages = ['stok_harian', 'tambah_stok_form'];
$produksi_pages = ['produksi', 'produksi_daftar', 'produksi_buat', 'produksi_detail', 'produk_jadi'];

// Variabel untuk menentukan menu mana yang aktif
$is_data_master_active = in_array($current_page, $data_master_pages);
$is_settings_active = in_array($current_page, $settings_pages);
$is_stok_active = in_array($current_page, $stok_pages);
$is_produksi_active = in_array($current_page, $produksi_pages);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . htmlspecialchars($settings['judul_default']) : htmlspecialchars($settings['judul_default']); ?></title>
    <link rel="icon" href="<?php echo base_url('assets/images/' . htmlspecialchars($settings['favicon'])); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/css/style.css'); ?>" rel="stylesheet">
</head>

<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <?php echo htmlspecialchars($settings['nama_website']); ?>
        </div>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo base_url('index.php?page=dashboard'); ?>">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /><polyline points="9 22 9 12 15 12 15 22" /></svg>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle <?php echo $is_produksi_active ? 'active' : ''; ?>" href="#produksiSubmenu" role="button" data-bs-toggle="collapse" aria-expanded="<?php echo $is_produksi_active ? 'true' : 'false'; ?>">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /><polyline points="10 9 9 9 8 9" /></svg>
                    Manajemen Produksi
                    <span class="chevron-icon"></span>
                </a>
                <div class="collapse submenu <?php echo $is_produksi_active ? 'show' : ''; ?>" id="produksiSubmenu">
                    <ul class="nav flex-column">
                        <li><a class="dropdown-item <?php echo $current_page == 'produksi_daftar' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=produksi_daftar'); ?>">Perintah Kerja</a></li>
                        
                        <li><a class="dropdown-item <?php echo $current_page == 'produk_jadi' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=produk_jadi'); ?>">Daftar Produk Jadi</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link dropdown-toggle <?php echo $is_stok_active ? 'active' : ''; ?>" href="#stokSubmenu" role="button" data-bs-toggle="collapse" aria-expanded="<?php echo $is_stok_active ? 'true' : 'false'; ?>">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    Manajemen Stok
                    <span class="chevron-icon"></span>
                </a>
                <div class="collapse submenu <?php echo $is_stok_active ? 'show' : ''; ?>" id="stokSubmenu">
                    <ul class="nav flex-column">
                        <li><a class="dropdown-item <?php echo $current_page == 'stok_harian' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=stok_harian'); ?>">Stok Bahan Baku</a></li>
                        <li><a class="dropdown-item <?php echo $current_page == 'tambah_stok_form' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=tambah_stok_form'); ?>">Tambah Stok Bahan Baku</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle <?php echo $is_data_master_active ? 'active' : ''; ?>" href="#dataMasterSubmenu" role="button" data-bs-toggle="collapse" aria-expanded="<?php echo $is_data_master_active ? 'true' : 'false'; ?>">
                    <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34" /><polygon points="18 2 22 6 12 16 8 16 8 12 18 2" /></svg>
                    Data Master
                    <span class="chevron-icon"></span>
                </a>
                <div class="collapse submenu <?php echo $is_data_master_active ? 'show' : ''; ?>" id="dataMasterSubmenu">
                    <ul class="nav flex-column">
                        <li><a class="dropdown-item <?php echo in_array($current_page, ['produk', 'produk_form', 'resep']) ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=produk'); ?>">Data Produk</a></li>
                        <li><a class="dropdown-item <?php echo in_array($current_page, ['bahan_baku', 'bahan_baku_form']) ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=bahan_baku'); ?>">Data Bahan Baku</a></li>
                    </ul>
                </div>
            </li>

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <li class="nav-item <?php echo str_starts_with($current_page, 'laporan') ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo base_url('index.php?page=laporan'); ?>">
                        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83" /><path d="M22 12A10 10 0 0 0 12 2v10z" /></svg>
                        Laporan & Analisis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $is_settings_active ? 'active' : ''; ?>" href="#settingsSubmenu" role="button" data-bs-toggle="collapse" aria-expanded="<?php echo $is_settings_active ? 'true' : 'false'; ?>">
                        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" /></svg>
                        Settings
                        <span class="chevron-icon"></span>
                    </a>
                    <div class="collapse submenu <?php echo $is_settings_active ? 'show' : ''; ?>" id="settingsSubmenu">
                        <ul class="nav flex-column">
                            <li><a class="dropdown-item <?php echo in_array($current_page, ['pengguna', 'pengguna_form']) ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=pengguna'); ?>">Data Staf</a></li>
                            <li><a class="dropdown-item <?php echo $current_page == 'settings' ? 'active' : ''; ?>" href="<?php echo base_url('index.php?page=settings'); ?>">Pengaturan Web</a></li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>
        </ul>

    </aside>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main class="content-wrapper">
        <header class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="hamburger-btn" id="hamburger-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" /></svg>
                </button>
            </div>

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="text-end me-2">
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                        <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                    </div>
                    <i class="fa-solid fa-caret-down text-dark"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="<?php echo base_url('logout.php'); ?>">Logout</a></li>
                </ul>
            </div>
        </header>
        <div class="main-content">