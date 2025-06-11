<?php
session_start();
require 'functions.php';

// Pastikan pengguna sudah login dan ada ID pesanan yang valid
if (!isset($_SESSION["login"], $_SESSION['user_id'], $_GET['order_id'])) {
    header("Location: dashboard.php");
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = (int)$_SESSION['user_id'];
$pesanan = get_pesanan_by_id($order_id);
if (!$pesanan || $pesanan['user_id'] != $user_id) {
    header("Location: pesanan_saya.php"); 
    exit;
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil | Bengkelin Aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body style="background-color: #f0f2f5;">
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-5">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                            <h1 class="display-5 mt-3 fw-bold">Pesanan Berhasil!</h1>

                            <?php if (isset($_SESSION['success_message_checkout'])) : ?>
                                <p class="lead text-muted"><?= htmlspecialchars($_SESSION['success_message_checkout']); ?></p>
                                <?php unset($_SESSION['success_message_checkout']); ?>
                            <?php endif; ?>

                            <p>Nomor pesanan Anda adalah: <strong class="text-success fs-5">#<?= htmlspecialchars($order_id); ?></strong></p>

                            <?php if ($pesanan['metode_pembayaran'] == 'Transfer Bank'): ?>
                                <div class="alert alert-success mt-4">
                                    <h4 class="alert-heading">Instruksi Pembayaran</h4>
                                    <p>Silakan lakukan pembayaran sebesar <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></strong> ke rekening berikut:</p>
                                    <hr>
                                    <p class="mb-0">Bank BCA: <strong>1234-5678-90</strong> a/n BengkelinAja</p>
                                    <p>Mohon sertakan nomor pesanan Anda (#<?= htmlspecialchars($order_id); ?>) pada berita transfer.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mt-4">
                                    <p class="mb-0">Anda memilih metode <strong>Bayar di Tempat (COD)</strong>. Mohon siapkan uang pas saat kurir kami tiba.</p>
                                </div>
                            <?php endif; ?>

                            <hr class="my-4">
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <a href="dashboard.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                                <a href="pesanan_saya.php" class="btn btn-success">Lihat Riwayat Pesanan</a>
                            </div>
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