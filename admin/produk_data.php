<?php
session_start();
$page_title = "Daftar Produk";
require_once '../functions.php';
// Staff bisa mengakses halaman ini
protect_admin_page();
require_once 'partials/header_admin.php';

// PAGINATION, SORTING, FILTERING 
$produk_per_halaman_admin = 10;
$halaman_aktif_admin = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif_admin < 1) $halaman_aktif_admin = 1;
$offset_admin = ($halaman_aktif_admin - 1) * $produk_per_halaman_admin;

$search_keyword_admin = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori_admin = isset($_GET['filter_kategori']) ? (int)$_GET['filter_kategori'] : 0;
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$allowed_sort_columns = ['name', 'nama_kategori', 'price', 'stock', 'created_at'];
if (!in_array($sort_column, $allowed_sort_columns)) {
    $sort_column = 'created_at';
}
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

$base_sql_select_admin = "SELECT p.id, p.name, p.price, p.stock, p.image, p.created_at, c.nama_kategori FROM product p LEFT JOIN categories c ON p.category_id = c.id_kategori";
$count_sql_admin = "SELECT COUNT(*) FROM product p LEFT JOIN categories c ON p.category_id = c.id_kategori";
$where_conditions_admin = [];
$params_admin = [];
$types_admin = "";

if (!empty($search_keyword_admin)) {
    $where_conditions_admin[] = "(p.name LIKE ? OR c.nama_kategori LIKE ?)";
    $search_param_admin = "%" . $search_keyword_admin . "%";
    $params_admin[] = $search_param_admin;
    $params_admin[] = $search_param_admin;
    $types_admin .= "ss";
}
if ($filter_kategori_admin > 0) {
    $where_conditions_admin[] = "p.category_id = ?";
    $params_admin[] = $filter_kategori_admin;
    $types_admin .= "i";
}
$where_clause_admin = "";
if (!empty($where_conditions_admin)) {
    $where_clause_admin = " WHERE " . implode(" AND ", $where_conditions_admin);
}

//  HITUNG TOTAL DATA & HALAMAN 
$total_produk_admin = 0;
$jumlah_halaman_admin = 0;
if (isset($conn)) {
    $stmt_count_admin = mysqli_prepare($conn, $count_sql_admin . $where_clause_admin);
    if ($stmt_count_admin) {
        if (!empty($params_admin)) mysqli_stmt_bind_param($stmt_count_admin, $types_admin, ...$params_admin);
        mysqli_stmt_execute($stmt_count_admin);
        $total_produk_admin = mysqli_stmt_get_result($stmt_count_admin)->fetch_row()[0];
        $jumlah_halaman_admin = ceil($total_produk_admin / $produk_per_halaman_admin);
        mysqli_stmt_close($stmt_count_admin);
    }
}

//  AMBIL DATA UNTUK HALAMAN AKTIF 
$produk_list_admin = [];
$sql_data_admin = $base_sql_select_admin . $where_clause_admin . " ORDER BY $sort_column $sort_order LIMIT ? OFFSET ?";
$params_data_admin = $params_admin;
$params_data_admin[] = $produk_per_halaman_admin;
$params_data_admin[] = $offset_admin;
$types_data_admin = $types_admin . "ii";
if (isset($conn) && $total_produk_admin > 0) {
    $stmt_data_admin = mysqli_prepare($conn, $sql_data_admin);
    if ($stmt_data_admin) {
        mysqli_stmt_bind_param($stmt_data_admin, $types_data_admin, ...$params_data_admin);
        mysqli_stmt_execute($stmt_data_admin);
        $result_data_admin = mysqli_stmt_get_result($stmt_data_admin);
        while ($row = mysqli_fetch_assoc($result_data_admin)) {
            $produk_list_admin[] = $row;
        }
        mysqli_stmt_close($stmt_data_admin);
    }
}
$all_categories = get_all_categories();
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 mb-0">Daftar Produk</h1>
            <p class="text-muted mb-0">Kelola semua produk yang ada di toko Anda.</p>
        </div>
        <div>
            <a href="produk_tambah.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Produk</a>
            <a href="laporan_produk_pdf.php" class="btn btn-outline-secondary" target="_blank"><i class="bi bi-file-earmark-pdf me-2"></i>Export PDF</a>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="p-3">
        <form method="GET" action="produk_data.php" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search" placeholder="Cari produk berdasarkan nama atau kategori..." value="<?= htmlspecialchars($search_keyword_admin); ?>">
            </div>
            <div class="col-md-5">
                <select class="form-select" name="filter_kategori">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($all_categories as $kategori): ?>
                        <option value="<?= $kategori['id_kategori']; ?>" <?= ($filter_kategori_admin == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kategori['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-secondary" type="submit"><i class="bi bi-search me-2"></i>Cari</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produk_list_admin)) : $i = $offset_admin + 1;
                    foreach ($produk_list_admin as $produk) : ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../img/img_produk/<?= htmlspecialchars($produk['image'] ?: 'placeholder.png'); ?>" alt="<?= htmlspecialchars($produk['name']); ?>" class="product-image me-3">
                                    <strong><?= htmlspecialchars($produk['name']); ?></strong>
                                </div>
                            </td>
                            <td>Rp <?= number_format($produk['price'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($produk['stock']); ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($produk['nama_kategori'] ?? 'N/A'); ?></span></td>
                            <td class="text-center action-buttons">
                                <a href="produk_edit.php?id=<?= $produk['id']; ?>" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                <a href="produk_hapus.php?id=<?= $produk['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin hapus produk ini?');"><i class="bi bi-trash3-fill"></i></a>
                            </td>
                        </tr>
                    <?php endforeach;
                else : ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Data tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($jumlah_halaman_admin > 1) : ?>
        <div class="card-footer bg-white">
            <nav class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <?php
                    $base_url_admin = "produk_data.php?";
                    $query_params_admin = [];
                    if (!empty($search_keyword_admin)) $query_params_admin['search'] = $search_keyword_admin;
                    if ($filter_kategori_admin > 0) $query_params_admin['filter_kategori'] = $filter_kategori_admin;
                    if (isset($_GET['sort'])) $query_params_admin['sort'] = $_GET['sort'];
                    if (isset($_GET['order'])) $query_params_admin['order'] = $_GET['order'];
                    $base_url_admin .= http_build_query($query_params_admin);
                    $separator_admin = empty($query_params_admin) ? '' : '&';
                    ?>
                    <li class="page-item <?= ($halaman_aktif_admin <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url_admin . $separator_admin . 'halaman=' . ($halaman_aktif_admin - 1); ?>">Sebelumnya</a></li>
                    <?php for ($i = 1; $i <= $jumlah_halaman_admin; $i++) : ?>
                        <li class="page-item <?= ($i == $halaman_aktif_admin) ? 'active' : ''; ?>"><a class="page-link" href="<?= $base_url_admin . $separator_admin . 'halaman=' . $i; ?>"><?= $i; ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman_aktif_admin >= $jumlah_halaman_admin) ? 'disabled' : ''; ?>"><a class="page-link" href="<?= $base_url_admin . $separator_admin . 'halaman=' . ($halaman_aktif_admin + 1); ?>">Berikutnya</a></li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'partials/footer_admin.php';
?>