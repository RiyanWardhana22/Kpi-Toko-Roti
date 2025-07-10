<?php
if (session_status() == PHP_SESSION_NONE) {
            session_start();
}
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
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
                        <ul class="nav flex-column">
                                    <li class="nav-item <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=dashboard'); ?>">
                                                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                                                        <polyline points="9 22 9 12 15 12 15 22" />
                                                            </svg>
                                                            Dashboard
                                                </a>
                                    </li>
                                    <li class="nav-item <?php echo str_starts_with($current_page, 'produksi') ? 'active' : ''; ?>">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=produksi'); ?>">
                                                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                                                        <polyline points="14 2 14 8 20 8" />
                                                                        <line x1="16" y1="13" x2="8" y2="13" />
                                                                        <line x1="16" y1="17" x2="8" y2="17" />
                                                                        <polyline points="10 9 9 9 8 9" />
                                                            </svg>
                                                            Manajemen Produksi
                                                </a>
                                    </li>
                                    <li class="nav-item <?php echo str_starts_with($current_page, 'laporan') ? 'active' : ''; ?>">
                                                <a class="nav-link" href="<?php echo base_url('index.php?page=laporan'); ?>">
                                                            <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                                                                        <path d="M22 12A10 10 0 0 0 12 2v10z" />
                                                            </svg>
                                                            Laporan & Analisis
                                                </a>
                                    </li>
                                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                                                <li class="nav-item">
                                                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="collapse" data-bs-target="#dataMasterSubmenu">
                                                                        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34" />
                                                                                    <polygon points="18 2 22 6 12 16 8 16 8 12 18 2" />
                                                                        </svg>
                                                                        Data Master
                                                            </a>
                                                            <div class="collapse" id="dataMasterSubmenu">
                                                                        <ul class="nav flex-column">
                                                                                    <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=produk'); ?>">Data Produk</a></li>
                                                                                    <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=bahan_baku'); ?>">Data Bahan Baku</a></li>
                                                                        </ul>
                                                            </div>
                                                </li>
                                                <li class="nav-item">
                                                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="collapse" data-bs-target="#settingsSubmenu">
                                                                        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                                    <circle cx="12" cy="12" r="3" />
                                                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                                                        </svg>
                                                                        Settings
                                                            </a>
                                                            <div class="collapse" id="settingsSubmenu">
                                                                        <ul class="nav flex-column">
                                                                                    <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=pengguna'); ?>">Data Staf</a></li>
                                                                                    <li><a class="dropdown-item" href="<?php echo base_url('index.php?page=settings'); ?>">Pengaturan Web</a></li>
                                                                        </ul>
                                                            </div>
                                                </li>
                                    <?php endif; ?>
                        </ul>
                        <div class="logout-btn px-3">
                                    <a href="<?php echo base_url('logout.php'); ?>" class="btn btn-outline-danger w-100">Logout</a>
                        </div>
            </aside>

            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <main class="content-wrapper">
                        <header class="topbar d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                                <button class="hamburger-btn" id="hamburger-btn">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                                                                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                                                            </svg>
                                                </button>
                                                <h1 class="page-title ms-3 mb-0"></h1>
                                    </div>
                                    <div class="d-flex align-items-center">
                                                <div class="text-end me-3">
                                                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                                                </div>
                                    </div>
                        </header>
                        <div class="main-content">