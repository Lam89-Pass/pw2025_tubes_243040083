<?php
session_start();
require 'functions.php';
if (!isset($_SESSION["login"]) || !isset($_SESSION['user_id'])) {
    $_SESSION['info_message'] = "Silakan login untuk melanjutkan ke pembayaran.";
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$keranjang_items = ambil_keranjang_dari_db($user_id);
if (empty($keranjang_items)) {
    header("Location: keranjang.php");
    exit;
}
$user_profile = get_user_by_id($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buat_pesanan'])) {
    $result = buat_pesanan($user_id, $_POST);

    if ($result['success']) {
        $_SESSION['success_message_checkout'] = $result['message'];
        header("Location: konfirmasi_pesanan.php?order_id=" . $result['order_id']);
        exit;
    } else {
        $_SESSION['error_message_checkout'] = $result['message'];
        header("Location: checkout.php");
        exit;
    }
}

// Hitung total belanja untuk ditampilkan di ringkasan
$total_belanja = 0;
foreach ($keranjang_items as $item) {
    $total_belanja += $item['harga'] * $item['kuantitas'];
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Selamat datang di Seiko Motor, tempat terbaik untuk menemukan sparepart motor berkualitas tinggi.">
    <meta name="keywords" content="sparepart motor, suku cadang motor, oli motor, helm motor, aksesoris motor">
    <meta name="author" content="Seiko Motor">
    <meta name="theme-color" content="#28a745">
    <link rel="icon" href="img/seiko.png" type="image/png">
    <link rel="apple-touch-icon" href="img/seiko.png">
    <title>Checkout | Seiko Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body style="background-color: #f0f2f5;">
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="container my-5">
            <div class="text-center mb-5">
                <h1 class="h2 fw-bolder">Langkah Terakhir</h1>
                <p class="text-muted">Selesaikan pesanan Anda dengan mengisi detail di bawah ini.</p>
            </div>

            <?php if (isset($_SESSION['error_message_checkout'])) : ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['error_message_checkout']); ?>
                </div>
                <?php unset($_SESSION['error_message_checkout']); ?>
            <?php endif; ?>

            <form method="POST" action="checkout.php">
                <div class="row g-5">
                    <!-- Form Alamat & Pembayaran -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h4 class="mb-3"><i class="bi bi-geo-alt-fill me-2 text-success"></i>Informasi Pengiriman</h4>
                                <div class="mb-3">
                                    <label for="nama_penerima" class="form-label">Nama Lengkap Penerima</label>
                                    <input type="text" class="form-control" id="nama_penerima" name="nama_penerima" value="<?= htmlspecialchars($user_profile['nama_lengkap'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="no_hp_penerima" class="form-label">Nomor HP</label>
                                    <input type="tel" class="form-control" id="no_hp_penerima" name="no_hp_penerima" value="<?= htmlspecialchars($user_profile['no_hp'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_pengiriman" class="form-label">Alamat Lengkap Pengiriman</label>
                                    <textarea class="form-control" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, dan kodepos"><?= htmlspecialchars($user_profile['alamat_lengkap'] ?? ''); ?></textarea>
                                    <div class="form-text">Pastikan alamat sudah benar untuk menghindari kesalahan pengiriman.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h4 class="mb-3"><i class="bi bi-credit-card-2-front-fill me-2 text-success"></i>Metode Pembayaran</h4>

                                <div class="payment-option mb-3">
                                    <label class="form-check-label" data-bs-toggle="collapse" href="#collapseBank" role="button" aria-expanded="true" aria-controls="collapseBank">
                                        <i class="bi bi-bank"></i>
                                        <div>
                                            <strong>Transfer Bank</strong><br>
                                            <small class="text-muted">Bayar ke rekening virtual account kami.</small>
                                        </div>
                                        <i class="bi bi-chevron-down ms-auto"></i>
                                    </label>
                                </div>
                                <div class="collapse show" id="collapseBank">
                                    <div class="ps-5 mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="bca" value="Transfer Bank BCA" checked required>
                                            <label class="form-check-label" for="bca">Bank BCA</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="bri" value="Transfer Bank BRI">
                                            <label class="form-check-label" for="bri">Bank BRI</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="mandiri" value="Transfer Bank Mandiri">
                                            <label class="form-check-label" for="mandiri">Bank Mandiri</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="payment-option">
                                    <label class="form-check-label" data-bs-toggle="collapse" href="#collapseEwallet" role="button" aria-expanded="false" aria-controls="collapseEwallet">
                                        <i class="bi bi-wallet2"></i>
                                        <div>
                                            <strong>E-Wallet</strong><br>
                                            <small class="text-muted">Pembayaran melalui DANA, OVO, atau GoPay.</small>
                                        </div>
                                        <i class="bi bi-chevron-down ms-auto"></i>
                                    </label>
                                </div>
                                <div class="collapse" id="collapseEwallet">
                                    <div class="ps-5 mt-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="dana" value="E-Wallet DANA">
                                            <label class="form-check-label" for="dana">DANA</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="ovo" value="E-Wallet OVO">
                                            <label class="form-check-label" for="ovo">OVO</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="metode_pembayaran" id="gopay" value="E-Wallet GoPay">
                                            <label class="form-check-label" for="gopay">GoPay</label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Pesanan -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm summary-card border-0">
                            <div class="card-header bg-light">
                                <h4 class="my-0 fw-normal">Ringkasan Pesanan</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($keranjang_items as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between lh-sm">
                                            <div class="d-flex align-items-center">
                                                <img src="img/img_produk/<?= htmlspecialchars($item['gambar']); ?>" class="me-3 product-thumb" alt="<?= htmlspecialchars($item['nama']); ?>">
                                                <div>
                                                    <h6 class="my-0"><?= htmlspecialchars($item['nama']); ?></h6>
                                                    <small class="text-muted">Qty: <?= $item['kuantitas']; ?></small>
                                                </div>
                                            </div>
                                            <span class="text-muted">Rp <?= number_format($item['harga'] * $item['kuantitas'], 0, ',', '.'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <li class="list-group-item d-flex justify-content-between bg-light mt-3">
                                        <span>Total (IDR)</span>
                                        <strong>Rp <?= number_format($total_belanja, 0, ',', '.'); ?></strong>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer p-3">
                                <button class="w-100 btn btn-success btn-lg" type="submit" name="buat_pesanan">
                                    <i class="bi bi-shield-check-fill me-2"></i>Buat Pesanan Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
</body>

</html>