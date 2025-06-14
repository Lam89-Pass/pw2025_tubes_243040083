<?php
session_start();
require 'functions.php';
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$produk_beranda = query("SELECT * FROM product ORDER BY created_at DESC LIMIT 6");
if ($produk_beranda === false) {
    error_log("Gagal mengambil data produk untuk beranda dari database.");
    $produk_beranda = [];
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
    <title>Selamat Datang di Seiko Motor!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="bg-light py-2 shadow-sm">
            <div class="container">
                <ul class="nav nav-link-categories justify-content-center flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Semua Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?kategori=Aksesoris">Aksesoris</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?kategori=Oli">Oli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?kategori=Helm">Helm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?kategori=Suku Cadang">Suku Cadang</a>
                    </li>
                </ul>
            </div>
        </div>

        <section class="hero-section">
            <div class="container text-center">
                <h1 class="display-4">Semua Sparepart Motor Anda, Ada Di Sini.</h1>
                <p class="lead">Temukan ribuan suku cadang motor original dan aftermarket berkualitas tinggi dengan harga terbaik. Jaminan keaslian dan pengiriman cepat ke seluruh Indonesia.</p>
                <a href="index.php" class="btn btn-light btn-lg">
                    <i class="bi bi-search me-2"></i>Mulai Cari Produk
                </a>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-3 fade-in-element">
                        <div class="feature-card">
                            <i class="bi bi-box-seam-fill"></i>
                            <h5 class="fw-bold">Produk Lengkap</h5>
                            <p class="text-muted small">Lebih dari 1.500+ jenis sparepart dari berbagai merek ternama.</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in-element">
                        <div class="feature-card">
                            <i class="bi bi-patch-check-fill"></i>
                            <h5 class="fw-bold">100% Original</h5>
                            <p class="text-muted small">Kami menjamin keaslian setiap produk yang kami jual.</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in-element">
                        <div class="feature-card">
                            <i class="bi bi-credit-card-2-front-fill"></i>
                            <h5 class="fw-bold">Pembayaran Aman</h5>
                            <p class="text-muted small">Tersedia berbagai metode pembayaran yang aman dan terpercaya.</p>
                        </div>
                    </div>
                    <div class="col-md-3 fade-in-element">
                        <div class="feature-card">
                            <i class="bi bi-truck"></i>
                            <h5 class="fw-bold">Pengiriman Cepat</h5>
                            <p class="text-muted small">Layanan pengiriman cepat dan terjamin ke seluruh wilayah.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="produk-pilihan py-5 bg-white">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-title text-success">Produk Pilihan Untuk Anda</h2>
                    <p class="section-subtitle">Temukan produk-produk terlaris dan paling dicari oleh pelanggan kami.</p>
                </div>

                <!-- KARTU PRODUK -->
                <div class="row g-3">
                    <?php if (!empty($produk_beranda)) : ?>
                        <?php foreach ($produk_beranda as $row) : ?>
                            <div class="col-lg-2 col-md-4 col-6 fade-in-element">
                                <div class="card card-product h-100">
                                    <div class="product-image-container">
                                        <a href="detail_produk.php?id=<?= htmlspecialchars($row["id"]); ?>">
                                            <img src="img/img_produk/<?= htmlspecialchars($row["image"] ?: 'placeholder.png'); ?>" class="card-img-top" alt="<?= htmlspecialchars($row["name"]); ?>">
                                        </a>
                                    </div>
                                    <div class="card-body text-center d-flex flex-column p-2">
                                        <h6 class="product-title flex-grow-1">
                                            <a href="detail_produk.php?id=<?= htmlspecialchars($row["id"]); ?>" class="text-decoration-none text-dark">
                                                <?= htmlspecialchars($row["name"]); ?>
                                            </a>
                                        </h6>
                                        <div class="mt-2">
                                            <?php if (!empty($row["original_price"]) && $row["original_price"] > $row["price"]) : ?>
                                                <p class="small text-muted text-decoration-line-through mb-0" style="font-size: 0.8rem;">Rp <?= number_format($row["original_price"], 0, ',', '.'); ?></p>
                                            <?php endif; ?>
                                            <p class="product-price mb-2">Rp <?= number_format($row["price"], 0, ',', '.'); ?></p>
                                        </div>
                                        <a href="detail_produk.php?id=<?= htmlspecialchars($row["id"]); ?>" class="btn btn-sm btn-outline-success mt-auto">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-5">
                    <a href="index.php" class="btn btn-outline-success rounded-pill px-4 py-2">
                        Lihat Semua Produk <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h2 class="section-title text-success">Kunjungi Toko Offline Kami</h2>
                        <p class="text-muted mb-4">Dapatkan pengalaman berbelanja langsung dan konsultasi dengan tim ahli kami. Kami siap melayani Anda di lokasi.</p>
                        <div class="mb-3">
                            <p class="mb-1"><strong class="text-success"><i class="bi bi-geo-alt-fill me-2"></i>Alamat:</strong></p>
                            <p>Jl. Ciwaruga No. 26, Parongpong, Kab. Bandung Barat, Jawa Barat</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="https://www.instagram.com/seiko.motor" target="_blank" class="btn btn-dark"><i class="bi bi-instagram me-2"></i>Instagram</a>
                            <a href="https://wa.me/6285221560909" target="_blank" class="btn btn-success"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ratio ratio-16x9 rounded shadow-lg overflow-hidden">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d513.792017056297!2d107.5749829090878!3d-6.863952499999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e6a095e66d47%3A0x4a56cef2729d5b5a!2s4HPG%2B94X%2C%20Jalan%20Waruga%20Jaya%2C%20Ciwaruga%2C%20Kec.%20Parongpong%2C%20Kota%20Bandung%2C%20Jawa%20Barat%2040559!5e1!3m2!1sid!2sid!4v1749747452167!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
</body>

</html>