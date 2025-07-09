<?php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo isset($page_title) ? $page_title . ' - ' . htmlspecialchars($settings['judul_default']) : htmlspecialchars($settings['judul_default']); ?></title>
            <link rel="icon" href="<?php echo base_url('assets/images/' . htmlspecialchars($settings['favicon'])); ?>">
            <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
            <link href="<?php echo base_url('assets/css/style.css'); ?>" rel="stylesheet">

</head>

<body>

            <div class="sidebar" id="sidebar">
                        <h4 class="text-center text-white mb-4 px-2"><?php echo htmlspecialchars($settings['nama_website']); ?></h4>
                        <ul class="nav flex-column">
                                    <li class="nav-item">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=dashboard'); ?>">Dashboard Utama</a>
                                    </li>
                                    <li class="nav-item">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=produksi'); ?>">Manajemen Produksi</a>
                                    </li>
                                    <li class="nav-item">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=laporan'); ?>">Laporan & Analisis</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Data Master
                                                </a>
                                                <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=produk'); ?>">Data Produk</a></li>
                                                            <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=bahan_baku'); ?>">Data Bahan Baku</a></li>
                                                </ul>
                                    </li>
                                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                                                <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Settings
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                        <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=pengguna'); ?>">Data Staf</a></li>
                                                                        <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=settings'); ?>">Pengaturan Web</a></li>
                                                            </ul>
                                                </li>
                                    <?php endif; ?>
                                    <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-outline-danger btn-sm">Logout</a>
                        </ul>
            </div>

            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <div class="content-wrapper">
                        <nav class="topbar d-flex justify-content-between align-items-center">
                                    <button class="hamburger-btn" id="hamburger-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                                                            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                                                </svg>
                                    </button>
                                    <div class="d-flex align-items-center">
                                                <div class="text-end me-3">
                                                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                                                </div>

                                    </div>
                        </nav>
                        <div class="main-content">