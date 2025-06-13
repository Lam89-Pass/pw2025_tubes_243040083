<?php
session_start();
require 'functions.php';
if (!isset($_SESSION["login"]) || !isset($_SESSION['user_id'])) {
    $_SESSION['info_message'] = "Silakan login untuk melihat keranjang belanja Anda.";
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$page_title = "Keranjang Belanja Saya";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_kuantitas_item'])) {
        $id_produk_update = (int)$_POST['id_produk_update'];
        $kuantitas_baru = (int)$_POST['kuantitas_baru'];
        if ($kuantitas_baru > 0) {
            if (update_kuantitas_db($user_id, $id_produk_update, $kuantitas_baru)) {
                $_SESSION['success_message_cart'] = "Kuantitas produk berhasil diperbarui.";
            }
        } else {
            hapus_item_keranjang_db($user_id, $id_produk_update);
            $_SESSION['success_message_cart'] = "Produk berhasil dihapus dari keranjang.";
        }
    } elseif (isset($_POST['hapus_item_keranjang'])) {
        hapus_item_keranjang_db($user_id, (int)$_POST['id_produk_hapus']);
        $_SESSION['success_message_cart'] = "Produk berhasil dihapus dari keranjang.";
    } elseif (isset($_POST['kosongkan_keranjang'])) {
        kosongkan_keranjang_db($user_id);
        $_SESSION['success_message_cart'] = "Keranjang belanja berhasil dikosongkan.";
    }
    header("Location: keranjang.php");
    exit;
}

$keranjang_items = ambil_keranjang_dari_db($user_id);
$total_belanja = 0;
$jumlah_item_keranjang = !empty($keranjang_items) ? count($keranjang_items) : 0;
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
    <title><?= htmlspecialchars($page_title); ?> | Seiko Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>

    <div class="container my-4">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h2 class="fw-bolder text-success mb-0"><i class="bi bi-cart-check-fill me-2"></i><?= htmlspecialchars($page_title); ?></h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali Belanja
            </a>
        </div>

        <?php if (isset($_SESSION['success_message_cart'])) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message_cart']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message_cart']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message_cart'])) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message_cart']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message_cart']); ?>
        <?php endif; ?>

        <?php if (!empty($keranjang_items)) : ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-cart">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" colspan="2" class="ps-3">Produk</th>
                                            <th scope="col" class="text-center">Harga Satuan</th>
                                            <th scope="col" class="text-center">Kuantitas</th>
                                            <th scope="col" class="text-center">Subtotal</th>
                                            <th scope="col" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($keranjang_items as $id_item => $item) : ?>
                                            <?php
                                            $subtotal_item = $item['harga'] * $item['kuantitas'];
                                            $total_belanja += $subtotal_item;
                                            ?>
                                            <tr>
                                                <td class="ps-3 py-2" style="width: 80px;">
                                                    <img src="img/img_produk/<?= htmlspecialchars($item['gambar'] ?? 'placeholder_product.png'); ?>" alt="<?= htmlspecialchars($item['nama']); ?>" class="product-image-cart">
                                                </td>
                                                <td class="py-2">
                                                    <a href="detail_produk.php?id=<?= $id_item; ?>" class="text-decoration-none text-dark product-name"><?= htmlspecialchars($item['nama']); ?></a>
                                                </td>
                                                <td class="text-center py-2">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td class="text-center py-2">
                                                    <form method="POST" action="keranjang.php" class="d-inline-flex align-items-center justify-content-center">
                                                        <input type="hidden" name="id_produk_update" value="<?= $id_item; ?>">
                                                        <input type="number" name="kuantitas_baru" class="form-control form-control-sm quantity-input-cart" value="<?= $item['kuantitas']; ?>" min="1" max="99">
                                                        <button type="submit" name="update_kuantitas_item" class="btn btn-link btn-sm p-0 ms-2 text-success" title="Update Kuantitas"><i class="bi bi-arrow-repeat fs-5"></i></button>
                                                    </form>
                                                </td>
                                                <td class="text-center py-2 fw-semibold">Rp <?= number_format($subtotal_item, 0, ',', '.'); ?></td>
                                                <td class="text-center py-2">
                                                    <form method="POST" action="keranjang.php" class="d-inline">
                                                        <input type="hidden" name="id_produk_hapus" value="<?= $id_item; ?>">
                                                        <button type="submit" name="hapus_item_keranjang" class="btn btn-danger btn-sm" title="Hapus Item" onclick="return confirm('Yakin ingin menghapus item ini dari keranjang?');">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="h5 fw-bold mb-3">Ringkasan Belanja</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Item</span>
                            <span><?= $jumlah_item_keranjang; ?> item</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Total Belanja</span>
                            <span>Rp <?= number_format($total_belanja, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-success btn-lg">
                                <i class="bi bi-wallet-fill me-2"></i>Lanjut ke Pembayaran
                            </a>
                            <form method="POST" action="keranjang.php" class="d-grid" id="form-kosongkan-keranjang">
                                <input type="hidden" name="kosongkan_keranjang" value="1">
                                <button type="button" class="btn btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#konfirmasiAksiModal"
                                    data-pesan-konfirmasi="Apakah Anda yakin ingin mengosongkan seluruh isi keranjang?">
                                    <i class="bi bi-cart-x-fill me-2"></i>Kosongkan Keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php else : ?>
            <div class="alert alert-secondary text-center py-5">
                <i class="bi bi-cart-check fs-1 mb-3 d-block"></i>
                <h4 class="alert-heading">Keranjang Belanja Anda Kosong</h4>
                <p>Yuk, mulai belanja dan temukan produk favorit Anda!</p>
                <a href="index.php" class="btn btn-success mt-3"><i class="bi bi-shop me-2"></i>Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
</body>

</html>