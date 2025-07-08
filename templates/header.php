<?php
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
            <title><?php echo isset($page_title) ? $page_title : 'Dashboard KPI Toko Roti'; ?></title>
            <link href="<?php echo base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
            <style>
                        body {
                                    background-color: #f8f9fa;
                        }

                        .sidebar {
                                    height: 100vh;
                                    position: fixed;
                                    top: 0;
                                    left: 0;
                                    width: 250px;
                                    padding-top: 20px;
                                    background-color: #343a40;
                                    color: white;
                        }

                        .main-content {
                                    margin-left: 260px;
                                    padding: 20px;
                        }
            </style>
</head>

<body>

            <div class="sidebar">
                        <h4 class="text-center mb-4">Dashboard Roti</h4>
                        <ul class="nav flex-column">
                                    <li class="nav-item">
                                                <a class="nav-link text-white" href="<?php echo base_url('index.php?page=dashboard'); ?>">Dashboard Utama</a>
                                    </li>
                                    <li class="nav-item">
                                                <a class="nav-link text-white" href="<?php echo base_url('index.php?page=produksi'); ?>">Manajemen Produksi</a>
                                    </li>
                                    <li class="nav-item">
                                                <a class="nav-link text-white" href="#">Laporan & Analisis</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Data Master
                                                </a>
                                                <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=produk'); ?>">Data Produk</a></li>
                                                            <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=bahan_baku'); ?>">Data Bahan Baku</a></li>
                                                            <li><a class="dropdown-item" href="#">Data Staf</a></li>
                                                </ul>
                                    </li>
                        </ul>
            </div>

            <div class="main-content">