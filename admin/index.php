<?php
session_start();
$page_title = "Dashboard Admin";
require_once 'partials/header_admin.php';

// Statistik dari database
$total_produk = query("SELECT COUNT(*) as total FROM product")[0]['total'] ?? 0;
$total_kategori = query("SELECT COUNT(*) as total FROM categories")[0]['total'] ?? 0;
$total_user = query("SELECT COUNT(*) as total FROM users")[0]['total'] ?? 0;
$total_pesanan = query("SELECT COUNT(*) as total FROM pesanan")[0]['total'] ?? 0;
$total_komentar = query("SELECT COUNT(*) as total FROM komentar_produk")[0]['total'] ?? 0;
$total_merek = query("SELECT COUNT(*) as total FROM merek")[0]['total'] ?? 0;
?>

<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <h1 class="h2 mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <span class="navbar-text ms-auto fs-5">
        Halo, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>!
    </span>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-green">
            <div class="card-body">
                <h3><?= $total_produk; ?></h3>
                <p>Total Produk</p>
                <div class="icon"><i class="bi bi-box-seam"></i></div>
            </div>
            <a href="produk_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-red">
            <div class="card-body">
                <h3><?= $total_pesanan; ?></h3>
                <p>Total Pesanan</p>
                <div class="icon"><i class="bi bi-cart-check"></i></div>
            </div>
            <a href="pesanan_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-yellow">
            <div class="card-body">
                <h3><?= $total_user; ?></h3>
                <p>Total Pengguna</p>
                <div class="icon"><i class="bi bi-people"></i></div>
            </div>
            <a href="user_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-purple">
            <div class="card-body">
                <h3><?= $total_kategori; ?></h3>
                <p>Total Kategori</p>
                <div class="icon"><i class="bi bi-tags"></i></div>
            </div>
            <a href="kategori_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-blue">
            <div class="card-body">
                <h3><?= $total_merek; ?></h3>
                <p>Total Merek</p>
                <div class="icon"><i class="bi bi-building"></i></div>
            </div>
            <a href="merek_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stat-card card-orange">
            <div class="card-body">
                <h3><?= $total_komentar; ?></h3>
                <p>Total Komentar</p>
                <div class="icon"><i class="bi bi-chat-dots"></i></div>
            </div>
            <a href="komentar_data.php" class="stat-card-footer">Lihat Detail <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
</div>

<?php
require_once 'partials/footer_admin.php';
?>