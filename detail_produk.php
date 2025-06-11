<?php
session_start();
require 'functions.php';

// Ambil ID produk dari URL
$id_produk_lihat = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produk_detail = null;
$page_title = "Detail Produk";
$merek_produk = [];
$komentar_list = [];

if ($id_produk_lihat > 0) {
    $produk_detail = get_produk_by_id($id_produk_lihat);
    if ($produk_detail) {
        $page_title = htmlspecialchars($produk_detail['name']);
        $merek_produk = get_merek_for_product($id_produk_lihat);
        $komentar_list = get_komentar_for_produk($id_produk_lihat);
    }
}

//  untuk menangani form Tambah ke Keranjang
$pesan_flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_ke_keranjang'])) {
    if (!isset($_SESSION['login'], $_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit;
    }
    $id_produk_keranjang = (int)$_POST['id_produk'];
    $kuantitas = (int)$_POST['kuantitas'];
    $user_id = (int)$_SESSION['user_id'];
    if ($id_produk_keranjang > 0 && $kuantitas > 0 && $produk_detail) {
        $result = tambah_ke_keranjang_db($user_id, $id_produk_keranjang, $kuantitas);
        $_SESSION['pesan_flash'] = $result; 
        header("Location: detail_produk.php?id=" . $id_produk_lihat);
        exit;
    }
}

// untuk menangani pengiriman komentar baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_komentar'])) {
    if (isset($_SESSION['login'], $_SESSION['user_id'])) {
        $user_id_komentar = $_SESSION['user_id'];
        $isi_komentar = $_POST['isi_komentar'];
        $result_komentar = tambah_komentar($id_produk_lihat, $user_id_komentar, $isi_komentar);
        $_SESSION['pesan_flash'] = $result_komentar;
        header("Location: detail_produk.php?id=" . $id_produk_lihat . "#ulasan");
        exit;
    }
}

// Ambil pesan flash dari sesi (jika ada) dan hapus setelahnya
if (isset($_SESSION['pesan_flash'])) {
    $pesan_flash_data = $_SESSION['pesan_flash'];
    unset($_SESSION['pesan_flash']);
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title; ?> | Bengkelin Aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
    <style>
        .main-product-image {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: contain;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }

        .thumbnail-images img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 0.375rem;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s ease;
        }

        .thumbnail-images img.active,
        .thumbnail-images img:hover {
            border-color: var(--bs-success);
        }

        .product-title-detail {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .price-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bs-success);
        }

        .action-card {
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
            position: sticky;
            top: 100px;
        }

        .quantity-input {
            width: 80px;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--bs-success);
            border-color: var(--bs-success);
            background-color: transparent;
        }

        .tab-content-container {
            background-color: #fff;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
        }
    </style>
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="container my-4">
            <?php if ($produk_detail) : ?>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-light p-3 rounded-pill">
                        <li class="breadcrumb-item"><a href="dashboard.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="allproduct.php">Semua Produk</a></li>
                        <li class="breadcrumb-item"><a href="allproduct.php?kategori=<?= urlencode($produk_detail['nama_kategori'] ?? ''); ?>"><?= htmlspecialchars($produk_detail['nama_kategori'] ?? 'Kategori'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= substr(htmlspecialchars($produk_detail['name']), 0, 50) . '...'; ?></li>
                    </ol>
                </nav>

                <?php if (isset($pesan_flash_data)): ?>
                    <div class="alert <?= $pesan_flash_data['success'] ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                        <?= $pesan_flash_data['message']; 
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="bg-white p-4 rounded shadow-sm">
                    <div class="row g-5">
                        <!-- Kolom Kiri Gambar Produk -->
                        <div class="col-lg-4">
                            <img src="img/img_produk/<?= htmlspecialchars($produk_detail['image'] ?: 'placeholder.png'); ?>" class="img-fluid main-product-image" id="mainImage" alt="<?= htmlspecialchars($produk_detail['name']); ?>">
                        </div>

                        <!-- Kolom Tengah Info Produk & Ulasan -->
                        <div class="col-lg-5">
                            <h1 class="product-title-detail mb-2"><?= htmlspecialchars($produk_detail['name']); ?></h1>
                            <p class="mb-3 text-muted">Kategori: <a href="allproduct.php?kategori=<?= urlencode($produk_detail['nama_kategori']); ?>"><?= htmlspecialchars($produk_detail['nama_kategori']); ?></a></p>
                            <div class="price-display mb-4">Rp <?= number_format($produk_detail['price'], 0, ',', '.'); ?></div>

                            <ul class="nav nav-tabs" id="productTab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#deskripsi-pane">Info Produk</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ulasan-pane">Ulasan (<?= count($komentar_list); ?>)</button></li>
                            </ul>
                            <div class="tab-content" id="productTabContent">
                                <div class="tab-pane fade show active tab-content-container" id="deskripsi-pane">
                                    <h6 class="fw-bold">Detail</h6>
                                    <p class="text-muted"><?= !empty($produk_detail['deskripsi']) ? nl2br(htmlspecialchars($produk_detail['deskripsi'])) : 'Tidak ada deskripsi untuk produk ini.'; ?></p>
                                </div>
                                <div class="tab-pane fade tab-content-container" id="ulasan-pane">
                                    <h5 class="mb-3">Semua Ulasan</h5>
                                    <?php if (isset($_SESSION['login'])) : ?>
                                        <form method="POST" action="detail_produk.php?id=<?= $id_produk_lihat; ?>#ulasan" class="mb-4">
                                            <div class="mb-2"><textarea class="form-control" name="isi_komentar" rows="3" required placeholder="Tulis ulasan Anda..."></textarea></div>
                                            <button type="submit" name="submit_komentar" class="btn btn-success btn-sm">Kirim Ulasan</button>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-light border small"><a href="login.php">Login</a> untuk memberikan ulasan.</div>
                                    <?php endif; ?>

                                    <?php if (!empty($komentar_list)) : foreach ($komentar_list as $komentar) : ?>
                                            <div class="d-flex mb-3 pt-3 border-top">
                                                <div class="flex-shrink-0"><img src="img/foto_profil/<?= htmlspecialchars($komentar['foto_profil'] ?: 'placeholder_profile.png'); ?>" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"></div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mt-0 mb-0 fw-bold"><?= htmlspecialchars($komentar['username']); ?></h6><small class="text-muted"><?= date('d F Y', strtotime($komentar['created_at'])); ?></small>
                                                    <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($komentar['komentar'])); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach;
                                    else : ?><p class="text-muted">Belum ada ulasan untuk produk ini.</p><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan Aksi Pembelian -->
                        <div class="col-lg-3">
                            <div class="card action-card">
                                <div class="card-body">
                                    <h6 class="card-title">Atur jumlah</h6>
                                    <form method="POST" action="detail_produk.php?id=<?= $id_produk_lihat; ?>">
                                        <input type="hidden" name="id_produk" value="<?= $produk_detail['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center my-3">
                                            <label for="kuantitas" class="form-label mb-0">Kuantitas:</label>
                                            <input type="number" class="form-control form-control-sm text-center" id="kuantitas" name="kuantitas" value="1" min="1" max="<?= $produk_detail['stock'] > 0 ? $produk_detail['stock'] : 1; ?>" style="width: 80px;">
                                            <div>Stok: <span class="fw-bold"><?= $produk_detail['stock']; ?></span></div>
                                        </div>
                                        <p class="text-end mb-2">Subtotal: <strong class="fs-5 text-success" id="subtotal-price">Rp <?= number_format($produk_detail['price'], 0, ',', '.'); ?></strong></p>
                                        <div class="d-grid">
                                            <button type="submit" name="tambah_ke_keranjang" class="btn btn-success btn-lg" <?= $produk_detail['stock'] <= 0 ? 'disabled' : ''; ?>>+ Keranjang</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="alert alert-danger text-center py-5">
                    <h4 class="alert-heading">Produk Tidak Ditemukan</h4>
                    <p>Maaf, produk yang Anda cari tidak dapat ditemukan.</p><a href="allproduct.php" class="btn btn-primary mt-3">Kembali ke Semua Produk</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
    <script>
        document.getElementById('kuantitas')?.addEventListener('input', function() {
            const qty = parseInt(this.value) || 0;
            const price = <?= $produk_detail['price'] ?? 0; ?>;
            const subtotal = qty * price;
            document.getElementById('subtotal-price').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        });
    </script>
</body>

</html>