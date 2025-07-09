<?php
// templates/header.php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}
require_once __DIR__ . '/../core/functions.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo isset($page_title) ? $page_title . ' - Dashboard KPI' : 'Dashboard KPI Toko Roti'; ?></title>
            <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
            <style>
                        /* CSS untuk Layout Baru */
                        body {
                                    background-color: #f8f9fa;
                        }

                        /* 1. Sidebar Styling */
                        .sidebar {
                                    width: 250px;
                                    /* Lebar sidebar */
                                    height: 100vh;
                                    /* Tinggi penuh */
                                    position: fixed;
                                    /* Tetap di tempat saat scroll */
                                    top: 0;
                                    left: 0;
                                    background-color: #343a40;
                                    /* Warna gelap */
                                    padding-top: 20px;
                                    z-index: 1000;
                                    /* Pastikan di atas konten lain */
                        }

                        .sidebar .nav-link {
                                    color: #adb5bd;
                        }

                        .sidebar .nav-link:hover,
                        .sidebar .nav-link.active {
                                    color: #ffffff;
                                    background-color: #495057;
                        }

                        .sidebar .dropdown-menu {
                                    background-color: #343a40;
                                    border: none;
                        }

                        .sidebar .dropdown-item {
                                    color: #adb5bd;
                        }

                        .sidebar .dropdown-item:hover {
                                    color: #ffffff;
                                    background-color: #495057;
                        }


                        /* 2. Content Wrapper Styling */
                        .content-wrapper {
                                    margin-left: 250px;
                                    /* Jarak sebesar lebar sidebar */
                                    padding-top: 0;
                        }


                        /* 3. Topbar Header Styling */
                        .topbar {
                                    height: 70px;
                                    background-color: #ffffff;
                                    border-bottom: 1px solid #e3e6f0;
                                    padding: 0 1.5rem;
                        }

                        /* 4. Main Content Styling */
                        .main-content {
                                    padding: 24px;
                        }
            </style>
</head>

<body>

            <div class="sidebar">
                        <h4 class="text-center text-white mb-4">Dashboard Roti</h4>
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

                                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                                                <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Data Master
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                        <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=produk'); ?>">Data Produk</a></li>
                                                                        <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=bahan_baku'); ?>">Data Bahan Baku</a></li>
                                                                        <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=pengguna'); ?>">Data Staf</a></li>
                                                            </ul>
                                                </li>
                                    <?php endif; ?>
                                    <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-danger">Logout</a>
                        </ul>
            </div>

            <div class="content-wrapper">

                        <nav class="topbar d-flex justify-content-end align-items-center">
                                    <div class="d-flex align-items-center">
                                                <div class="text-end me-3">
                                                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                                                </div>
                                    </div>
                        </nav>

                        <div class="main-content">