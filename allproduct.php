<?php
session_start();
require 'functions.php';

if (!isset($_SESSION["login"])) {
    $_SESSION['info_message'] = "Silakan login untuk melihat produk.";
    header("Location: login.php");
    exit;
}

// PAGINATION DINAMIS
if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $produk_per_halaman = 12;
} else {
    $produk_per_halaman = 20;
}
$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
$offset = ($halaman_aktif - 1) * $produk_per_halaman;

// SORTING
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$order_by_clause = "ORDER BY p.created_at DESC";
switch ($sort_option) {
    case 'termurah':
        $order_by_clause = "ORDER BY p.price ASC";
        break;
    case 'termahal':
        $order_by_clause = "ORDER BY p.price DESC";
        break;
    case 'nama_asc':
        $order_by_clause = "ORDER BY p.name ASC";
        break;
    case 'nama_desc':
        $order_by_clause = "ORDER BY p.name DESC";
        break;
}

// FILTER DAN PENCARIAN 
$judul_halaman = "Semua Produk";
$keyword_pencarian = '';
$kategori_nama_filter = '';
$base_sql_select = "SELECT p.id, p.name, p.price, p.original_price, p.stock, p.image, p.deskripsi, p.created_at, c.nama_kategori FROM product p LEFT JOIN categories c ON p.category_id = c.id_kategori";
$count_sql = "SELECT COUNT(*) FROM product p LEFT JOIN categories c ON p.category_id = c.id_kategori";
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kategori_nama_filter = $_GET['kategori'];
    $judul_halaman = "Produk Kategori: " . htmlspecialchars($kategori_nama_filter);
    $where_conditions[] = "c.nama_kategori = ?";
    $params[] = $kategori_nama_filter;
    $types .= "s";
} elseif (isset($_GET['search']) && !empty($_GET['search'])) {
    $keyword_pencarian = $_GET['search'];
    $judul_halaman = "Hasil pencarian untuk: \"" . htmlspecialchars($keyword_pencarian) . "\"";
    $search_term_like = "%" . $keyword_pencarian . "%";
    $where_conditions[] = "(p.name LIKE ? OR p.deskripsi LIKE ? OR c.nama_kategori LIKE ?)";
    $params = [$search_term_like, $search_term_like, $search_term_like];
    $types .= "sss";
}

$where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";


$all_categories = get_all_categories();
$all_merek = get_all_merek();
$initial_products = query("SELECT p.id, p.name, p.price, p.original_price, p.image FROM product p ORDER BY created_at DESC LIMIT 12");


// HITUNG TOTAL PRODUK DAN HALAMAN
$total_produk = 0;
$jumlah_halaman = 0;
if (isset($conn) && $conn) {
    $stmt_count = mysqli_prepare($conn, $count_sql . $where_clause);
    if ($stmt_count) {
        if (!empty($params)) mysqli_stmt_bind_param($stmt_count, $types, ...$params);
        mysqli_stmt_execute($stmt_count);
        $total_produk = mysqli_stmt_get_result($stmt_count)->fetch_row()[0];
        $jumlah_halaman = ceil($total_produk / $produk_per_halaman);
        mysqli_stmt_close($stmt_count);
    }
}

// AMBIL DATA PRODUK
$produk_list = [];
$sql_data = $base_sql_select . $where_clause . " " . $order_by_clause . " LIMIT ? OFFSET ?";
$params_data = $params;
$params_data[] = $produk_per_halaman;
$params_data[] = $offset;
$types_data = $types . "ii";

if (isset($conn) && $conn && $total_produk > 0) {
    $stmt_data = mysqli_prepare($conn, $sql_data);
    if ($stmt_data) {
        mysqli_stmt_bind_param($stmt_data, $types_data, ...$params_data);
        mysqli_stmt_execute($stmt_data);
        $result_data = mysqli_stmt_get_result($stmt_data);
        while ($row = mysqli_fetch_assoc($result_data)) $produk_list[] = $row;
        mysqli_stmt_close($stmt_data);
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($judul_halaman); ?> | Seiko Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>
    <div class="bg-light py-2 shadow-sm">
        <div class="container">
            <ul class="nav nav-link-categories justify-content-center flex-wrap">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link <?php echo (empty($kategori_nama_filter) && empty($keyword_pencarian)) ? 'active' : ''; ?>" href="allproduct.php">Semua Produk</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($kategori_nama_filter == 'Aksesoris') ? 'active' : ''; ?>" href="allproduct.php?kategori=Aksesoris">Aksesoris</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($kategori_nama_filter == 'Oli') ? 'active' : ''; ?>" href="allproduct.php?kategori=Oli">Oli</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($kategori_nama_filter == 'Helm') ? 'active' : ''; ?>" href="allproduct.php?kategori=Helm">Helm</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($kategori_nama_filter == 'Suku Cadang') ? 'active' : ''; ?>" href="allproduct.php?kategori=Suku Cadang">Suku Cadang</a></li>
            </ul>
        </div>
    </div>


    <!-- Bagian Konten Produk -->
    <div class="container my-4">
        <div class="produk-section p-4 shadow-sm bg-white rounded">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <p class="text-muted mb-0 fs-5"><?= htmlspecialchars($judul_halaman); ?></p>
                <form method="GET" action="allproduct.php" class="d-flex align-items-center">
                    <?php if (!empty($kategori_nama_filter)): ?><input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_nama_filter); ?>"><?php endif; ?>
                    <?php if (!empty($keyword_pencarian)): ?><input type="hidden" name="search" value="<?= htmlspecialchars($keyword_pencarian); ?>"><?php endif; ?>
                    <label for="sort" class="form-label me-2 mb-0 fw-medium text-muted">Urutkan:</label>
                    <select class="form-select form-select-sm" name="sort" id="sort" style="width: auto;" onchange="this.form.submit()">
                        <option value="terbaru" <?= $sort_option == 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="termurah" <?= $sort_option == 'termurah' ? 'selected' : ''; ?>>Harga: Termurah</option>
                        <option value="termahal" <?= $sort_option == 'termahal' ? 'selected' : ''; ?>>Harga: Termahal</option>
                        <option value="nama_asc" <?= $sort_option == 'nama_asc' ? 'selected' : ''; ?>>Nama: A-Z</option>
                        <option value="nama_desc" <?= $sort_option == 'nama_desc' ? 'selected' : ''; ?>>Nama: Z-A</option>
                    </select>
                </form>
            </div>
            <div class="row g-3">
                <?php if (!empty($produk_list)) : ?>
                    <?php foreach ($produk_list as $row) : ?>
                        <div class="col-lg-2 col-md-4 col-6 fade-in-element">
                            <div class="card card-product h-100"> <a href="detail_produk.php?id=<?= htmlspecialchars($row["id"]); ?>" class="text-decoration-none text-dark d-flex flex-column h-100">
                                    <div class="product-image-container">
                                        <img src="img/img_produk/<?= htmlspecialchars($row["image"] ?: 'placeholder.png'); ?>" class="card-img-top" alt="<?= htmlspecialchars($row["name"]); ?>">
                                    </div>
                                    <div class="card-body text-center d-flex flex-column p-2">
                                        <h6 class="product-title flex-grow-1">
                                            <?= htmlspecialchars($row["name"]); ?>
                                        </h6>
                                        <div class="mt-2">
                                            <?php if (!empty($row["original_price"]) && $row["original_price"] > $row["price"]) : ?>
                                                <p class="small text-muted text-decoration-line-through mb-0" style="font-size: 0.8rem;">Rp <?= number_format($row["original_price"], 0, ',', '.'); ?></p>
                                            <?php endif; ?>
                                            <p class="product-price mb-2">Rp <?= number_format($row["price"], 0, ',', '.'); ?></p>
                                        </div>
                                        <a href="detail_produk.php?id=<?= htmlspecialchars($row["id"]); ?>" class="btn btn-sm btn-outline-success mt-auto">Lihat Detail</a>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-light text-center py-5">
                            <i class="bi bi-search fs-1 mb-3"></i>
                            <h4 class="fw-bold">Produk Tidak Ditemukan</h4>
                            <p class="text-muted">Maaf, kami tidak dapat menemukan produk yang sesuai dengan pencarian atau filter Anda.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($jumlah_halaman > 1) : ?>
                <nav class="mt-4 d-flex justify-content-center">
                    <ul class="pagination">
                        <?php
                        $base_url = "allproduct.php?";
                        $query_params = [];
                        if (!empty($kategori_nama_filter)) $query_params['kategori'] = $kategori_nama_filter;
                        if (!empty($keyword_pencarian)) $query_params['search'] = $keyword_pencarian;
                        if (!empty($sort_option) && $sort_option != 'terbaru') $query_params['sort'] = $sort_option;
                        $base_url .= http_build_query($query_params);
                        $separator = empty($query_params) ? '' : '&';
                        ?>
                        <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url . $separator . 'halaman=' . ($halaman_aktif - 1); ?>">Sebelumnya</a></li>
                        <?php for ($i = 1; $i <= $jumlah_halaman; $i++) : ?>
                            <li class="page-item <?= ($i == $halaman_aktif) ? 'active' : ''; ?>"><a class="page-link" href="<?= $base_url . $separator . 'halaman=' . $i; ?>"><?= $i; ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($halaman_aktif >= $jumlah_halaman) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url . $separator . 'halaman=' . ($halaman_aktif + 1); ?>">Berikutnya</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'partials/footer_user.php'; ?>
    <?php include 'partials/footer_script.php'; ?>
</body>

</html>