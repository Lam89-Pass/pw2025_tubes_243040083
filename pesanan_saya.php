<?php
session_start();
require 'functions.php';

if (!isset($_SESSION["login"], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_profile = get_user_by_id($user_id);

$page_title = "Riwayat Pesanan";
$pesanan_list = get_pesanan_by_user_id($user_id);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title; ?> | Seiko Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="container my-5">
            <div class="row">
                <div class="col-lg-4">
                    <!-- Navigasi Profil -->
                    <div class="card shadow-sm">
                        <div class="card-body text-center" style="padding-top: 2rem;">
                            <img src="img/foto_profil/<?= htmlspecialchars($user_profile['foto_profil'] ?: 'placeholder_profile.png'); ?>" alt="Foto Profil" class="rounded-circle" style="width:120px;height:120px;object-fit:cover;border:4px solid #fff;box-shadow:0 4px 8px rgba(0,0,0,.1);margin-top:-60px;margin-bottom:1rem;">
                            <h4 class="card-title mb-0"><?= htmlspecialchars($user_profile['nama_lengkap'] ?: $user_profile['username']); ?></h4>
                            <p class="text-muted">@<?= htmlspecialchars($user_profile['username']); ?></p>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-house-door-fill me-2"></i>Beranda</a>
                            <a href="profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-fill me-2"></i>Profil Saya</a>
                            <a href="pesanan_saya.php" class="list-group-item list-group-item-action active"><i class="bi bi-box-seam-fill me-2"></i>Riwayat Pesanan</a>
                            <a href="ubah_password.php" class="list-group-item list-group-item-action"><i class="bi bi-key-fill me-2"></i>Ubah Password</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 mt-4 mt-lg-0">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Riwayat Pesanan Saya</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pesanan_list)): ?>
                                <div class="accordion" id="accordionPesanan">
                                    <?php foreach ($pesanan_list as $pesanan): ?>
                                        <?php $detail_items = get_detail_pesanan_items($pesanan['id_pesanan']); ?>
                                        <div class="accordion-item mb-2">
                                            <h2 class="accordion-header" id="heading<?= $pesanan['id_pesanan']; ?>">
                                                <button class="accordion-button collapsed fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $pesanan['id_pesanan']; ?>">
                                                    <div class="d-flex w-100 justify-content-between align-items-center pe-2">
                                                        <div>
                                                            Nomor Pesanan: <strong><?= $pesanan['id_pesanan']; ?></strong>
                                                            <br><small class="text-muted"><?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></small>
                                                        </div>
                                                        <?php
                                                        $status_class = 'bg-secondary';
                                                        if ($pesanan['status_pesanan'] == 'Diproses') $status_class = 'bg-primary';
                                                        if ($pesanan['status_pesanan'] == 'Dikirim') $status_class = 'bg-info text-dark';
                                                        if ($pesanan['status_pesanan'] == 'Selesai') $status_class = 'bg-success';
                                                        if ($pesanan['status_pesanan'] == 'Dibatalkan') $status_class = 'bg-danger';
                                                        if ($pesanan['status_pesanan'] == 'Menunggu Pembayaran') $status_class = 'bg-warning text-dark';
                                                        ?>
                                                        <span class="badge <?= $status_class; ?>"><?= $pesanan['status_pesanan']; ?></span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse<?= $pesanan['id_pesanan']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPesanan">
                                                <div class="accordion-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong class="text-muted">Penerima:</strong><br><?= htmlspecialchars($pesanan['nama_penerima']); ?></p>
                                                            <p class="mb-0"><strong class="text-muted">No. HP:</strong><br><?= htmlspecialchars($pesanan['no_hp_penerima']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong class="text-muted">Metode Pembayaran:</strong><br><?= htmlspecialchars($pesanan['metode_pembayaran']); ?></p>
                                                            <p class="mb-0"><strong class="text-muted">Alamat:</strong><br><?= htmlspecialchars($pesanan['alamat_pengiriman']); ?></p>
                                                        </div>
                                                    </div>

                                                    <h6 class="fw-bold">Rincian Produk:</h6>
                                                    <ul class="list-group list-group-flush mb-3">
                                                        <?php foreach ($detail_items as $item_detail): ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="img/img_produk/<?= htmlspecialchars($item_detail['image'] ?: 'placeholder.png'); ?>" class="order-item-thumb me-3">
                                                                    <div>
                                                                        <?= htmlspecialchars($item_detail['nama_produk_saat_pesan']); ?>
                                                                        <br><small class="text-muted"><?= $item_detail['kuantitas']; ?> x Rp <?= number_format($item_detail['harga_saat_pesan'], 0, ',', '.'); ?></small>
                                                                    </div>
                                                                </div>
                                                                <span>Rp <?= number_format($item_detail['subtotal'], 0, ',', '.'); ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <div class="d-flex justify-content-between fw-bold border-top pt-2">
                                                        <span>Total Pesanan</span>
                                                        <span>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">Anda belum memiliki riwayat pesanan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
</body>

</html>