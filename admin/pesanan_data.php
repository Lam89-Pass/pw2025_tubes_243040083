<?php
session_start();
$page_title = "Manajemen Pesanan";
require_once 'partials/header_admin.php';

// PAGINATION, FILTERING, PENCARIAN
$pesanan_per_halaman = 10;
$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
$offset = ($halaman_aktif - 1) * $pesanan_per_halaman;

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_option = isset($_GET['sort_option']) ? $_GET['sort_option'] : 'id_asc'; // Default ke ID terlama

// --- PERBAIKAN: LOGIKA UNTUK FILTER URUTKAN ---
$sort_column = 'id_pesanan';
$sort_order = 'ASC';

switch ($sort_option) {
    case 'tanggal_desc':
        $sort_column = 'tanggal_pesanan';
        $sort_order = 'DESC';
        break;
    case 'tanggal_asc':
        $sort_column = 'tanggal_pesanan';
        $sort_order = 'ASC';
        break;
    case 'id_desc':
        $sort_column = 'id_pesanan';
        $sort_order = 'DESC';
        break;
    case 'id_asc':
    default:
        $sort_column = 'id_pesanan';
        $sort_order = 'ASC';
        break;
}

$base_sql = "FROM pesanan p JOIN users u ON p.user_id = u.id";
$where_conditions = [];
$params = [];
$types = "";

if (!empty($filter_status)) {
    $where_conditions[] = "p.status_pesanan = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if (!empty($search_query)) {
    $where_conditions[] = "(p.id_pesanan = ? OR u.username LIKE ? OR p.nama_penerima LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_query;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "iss";
}
$where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

// Hitung total pesanan
$total_pesanan = 0;
$jumlah_halaman = 0;
if (isset($conn) && $conn) {
    $count_sql = "SELECT COUNT(*) " . $base_sql . $where_clause;
    $stmt_count = mysqli_prepare($conn, $count_sql);
    if ($stmt_count) {
        if (!empty($params)) mysqli_stmt_bind_param($stmt_count, $types, ...$params);
        mysqli_stmt_execute($stmt_count);
        $total_pesanan = mysqli_stmt_get_result($stmt_count)->fetch_row()[0];
        $jumlah_halaman = ceil($total_pesanan / $pesanan_per_halaman);
        mysqli_stmt_close($stmt_count);
    }
}

// Ambil data pesanan untuk halaman ini
$pesanan_list = [];
$sql_data = "SELECT p.id_pesanan, p.tanggal_pesanan, p.total_harga, p.status_pesanan, u.username, p.nama_penerima " . $base_sql . $where_clause . " ORDER BY p.$sort_column $sort_order LIMIT ? OFFSET ?";
$params_data = $params;
$params_data[] = $pesanan_per_halaman;
$params_data[] = $offset;
$types_data = $types . "ii";
$stmt_data = mysqli_prepare($conn, $sql_data);
if ($stmt_data) {
    mysqli_stmt_bind_param($stmt_data, $types_data, ...$params_data);
    mysqli_stmt_execute($stmt_data);
    $result_data = mysqli_stmt_get_result($stmt_data);
    while ($row = mysqli_fetch_assoc($result_data)) {
        $pesanan_list[] = $row;
    }
    mysqli_stmt_close($stmt_data);
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-receipt me-2"></i><?= htmlspecialchars($page_title); ?></h1>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0">Daftar Semua Pesanan</h5>
        <form method="GET" action="pesanan_data.php" class="d-flex align-items-center gap-2" style="max-width: 600px;">
            <input type="text" class="form-control" name="search" placeholder="Cari ID/Username..." value="<?= htmlspecialchars($search_query); ?>">
            <select class="form-select" name="status">
                <option value="">Semua Status</option>
                <option value="Menunggu Pembayaran" <?= ($filter_status == 'Menunggu Pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                <option value="Diproses" <?= ($filter_status == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                <option value="Dikirim" <?= ($filter_status == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                <option value="Selesai" <?= ($filter_status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                <option value="Dibatalkan" <?= ($filter_status == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
            </select>
            <select class="form-select" name="sort_option" onchange="this.form.submit()">
                <option value="id_asc" <?= ($sort_option == 'id_asc') ? 'selected' : ''; ?>>Terlama</option>
                <option value="tanggal_desc" <?= ($sort_option == 'tanggal_desc') ? 'selected' : ''; ?>>Terbaru</option>
            </select>
            <button class="btn btn-info text-nowrap" type="submit">Cari</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pesanan_list)) : foreach ($pesanan_list as $pesanan) : ?>
                            <tr>
                                <td><strong><?= $pesanan['id_pesanan']; ?></strong></td>
                                <td><?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                                <td><?= htmlspecialchars($pesanan['username']); ?><br><small class="text-muted"><?= htmlspecialchars($pesanan['nama_penerima']); ?></small></td>
                                <td>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $status_class = 'bg-secondary';
                                    if ($pesanan['status_pesanan'] == 'Diproses') $status_class = 'bg-primary';
                                    if ($pesanan['status_pesanan'] == 'Dikirim') $status_class = 'bg-info text-dark';
                                    if ($pesanan['status_pesanan'] == 'Selesai') $status_class = 'bg-success';
                                    if ($pesanan['status_pesanan'] == 'Dibatalkan') $status_class = 'bg-danger';
                                    if ($pesanan['status_pesanan'] == 'Menunggu Pembayaran') $status_class = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $status_class; ?>"><?= $pesanan['status_pesanan']; ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="pesanan_detail.php?id=<?= $pesanan['id_pesanan']; ?>" class="btn btn-sm btn-outline-dark" title="Lihat Detail">
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada data pesanan yang cocok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($jumlah_halaman > 1) : ?>
        <div class="card-footer">
            <nav class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    <?php
                    // Logika pagination tidak perlu diubah, hanya pastikan parameter sort_option ikut terbawa
                    $base_url = "pesanan_data.php?";
                    $query_params = [];
                    if (!empty($search_query)) $query_params['search'] = $search_query;
                    if (!empty($filter_status)) $query_params['status'] = $filter_status;
                    if ($sort_option != 'id_asc') $query_params['sort_option'] = $sort_option; // Bawa parameter jika bukan default
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
        </div>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer_admin.php'; ?>