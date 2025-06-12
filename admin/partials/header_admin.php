<?php
require_once '../functions.php';
protect_admin_page();
?>


<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel'; ?> | Bengkelin Aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin_style.css?v=<?= time(); ?>">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-sticky mt-3">
                    <!-- Menu Dashboard -->
                    <div>
                        <a href="index.php" class="sidebar-brand">
                            <img src="../img/logoputih.png" alt="BengkelinAja Logo Putih" style="max-height: 80px; width: auto;"><br>
                        </a>
                        <ul class="nav flex-column mt-3">
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            </li>
                            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-uppercase">Manajemen Konten</h6>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'produk_') !== false ? 'active' : ''; ?>" href="produk_data.php"><i class="bi bi-box-seam-fill"></i> Produk</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'kategori_') !== false ? 'active' : ''; ?>" href="kategori_data.php"><i class="bi bi-tags-fill"></i> Kategori</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'komentar_') !== false ? 'active' : ''; ?>" href="komentar_data.php"><i class="bi bi-chat-square-dots-fill"></i> Komentar</a>
                            </li>
                            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-uppercase">Administrasi</h6>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'pesanan_') !== false ? 'active' : ''; ?>" href="pesanan_data.php"><i class="bi bi-receipt"></i> Pesanan</a>
                            </li>
                            <?php if (is_admin()): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= strpos(basename($_SERVER['PHP_SELF']), 'user_') !== false ? 'active' : ''; ?>" href="user_data.php"><i class="bi bi-people-fill"></i> Pengguna</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="logout-wrapper mb-5">
                        <!-- Kembali ke dashboard user dan Logout -->
                        <a href="../dashboard.php" target="_blank" class="btn btn-outline-light w-100 mb-2"><i class="bi bi-globe me-2"></i>Lihat Situs</a>
                        <a href="logout_admin.php" class="btn logout-btn"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
                    </div>
                </div>
            </nav>

            <!-- Konten Utama -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10">
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message_crud'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message_crud']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    unset($_SESSION['error_message_crud']);
                }
                ?>