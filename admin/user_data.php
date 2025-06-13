<?php
session_start();
$page_title = "Manajemen Pengguna";
require_once '../functions.php';
protect_super_admin_page();
require_once 'partials/header_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id_to_update = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $new_role = $_POST['new_role'] ?? '';

    if ($user_id_to_update > 0 && !empty($new_role)) {
        $result = update_user_role($user_id_to_update, $new_role);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message_crud'] = $result['message'];
        }
    } else {
        $_SESSION['error_message_crud'] = "Data tidak valid untuk mengubah peran.";
    }
    header("Location: user_data.php");
    exit;
}


// PAGINATION & PENCARIAN
$users_per_halaman = 10;
$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman_aktif < 1) $halaman_aktif = 1;
$offset = ($halaman_aktif - 1) * $users_per_halaman;

$search_keyword_user = isset($_GET['search_user']) ? trim($_GET['search_user']) : '';

$base_sql_users = "FROM users";
$where_conditions_users = [];
$params_users = [];
$types_users = "";

if (!empty($search_keyword_user)) {
    $where_conditions_users[] = "(username LIKE ? OR email LIKE ? OR role LIKE ?)";
    $search_param_user = "%" . $search_keyword_user . "%";
    $params_users = [$search_param_user, $search_param_user, $search_param_user];
    $types_users .= "sss";
}
$where_clause_users = !empty($where_conditions_users) ? " WHERE " . implode(" AND ", $where_conditions_users) : "";

$total_users = 0;
$jumlah_halaman = 0;
if (isset($conn)) {
    $count_sql_users = "SELECT COUNT(*) " . $base_sql_users . $where_clause_users;
    $stmt_count_users = mysqli_prepare($conn, $count_sql_users);
    if ($stmt_count_users) {
        if (!empty($params_users)) mysqli_stmt_bind_param($stmt_count_users, $types_users, ...$params_users);
        mysqli_stmt_execute($stmt_count_users);
        $total_users = mysqli_stmt_get_result($stmt_count_users)->fetch_row()[0];
        $jumlah_halaman = ceil($total_users / $users_per_halaman);
        mysqli_stmt_close($stmt_count_users);
    }
}

$users_list = [];
$sql_data_users = "SELECT id, username, email, role, created_at, nama_lengkap FROM users" . $where_clause_users . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_data_users = $params_users;
$params_data_users[] = $users_per_halaman;
$params_data_users[] = $offset;
$types_data_users = $types_users . "ii";

if (isset($conn) && $total_users > 0) {
    $stmt_data_users = mysqli_prepare($conn, $sql_data_users);
    if ($stmt_data_users) {
        mysqli_stmt_bind_param($stmt_data_users, $types_data_users, ...$params_data_users);
        mysqli_stmt_execute($stmt_data_users);
        $result_db_users = mysqli_stmt_get_result($stmt_data_users);
        while ($row_user = mysqli_fetch_assoc($result_db_users)) $users_list[] = $row_user;
        mysqli_stmt_close($stmt_data_users);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people-fill me-2"></i><?= htmlspecialchars($page_title); ?></h1>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pengguna</h5>
        <form method="GET" action="user_data.php" class="d-flex" style="width: 100%; max-width: 300px;">
            <input type="text" class="form-control" name="search_user" placeholder="Cari pengguna..." value="<?= htmlspecialchars($search_keyword_user); ?>">
            <button class="btn btn-secondary ms-2" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" class="ps-3">NO</th>
                        <th scope="col">Username</th>
                        <th scope="col">Nama Lengkap</th>
                        <th scope="col">Email</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Bergabung</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users_list)) : $i = $offset + 1;
                        foreach ($users_list as $user_item) : ?>
                            <tr>
                                <th scope="row" class="ps-3"><?= $i++; ?></th>
                                <td><strong><?= htmlspecialchars($user_item['username']); ?></strong></td>
                                <td><?= htmlspecialchars($user_item['nama_lengkap'] ?: '-'); ?></td>
                                <td><?= htmlspecialchars($user_item['email']); ?></td>
                                <td>
                                    <?php
                                    $role_class = 'bg-secondary'; 
                                    if ($user_item['role'] == 'admin') $role_class = 'bg-success';
                                    if ($user_item['role'] == 'staff') $role_class = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?= $role_class; ?>"><?= ucfirst($user_item['role']); ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($user_item['created_at'])); ?></td>
                                <td class="text-center action-buttons">
                                    <form method="POST" action="user_data.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $user_item['id']; ?>">
                                        <select name="new_role" class="form-select form-select-sm d-inline" style="width: auto;" onchange="this.form.submit()">
                                            <option value="user" <?= ($user_item['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                            <option value="staff" <?= ($user_item['role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                            <option value="admin" <?= ($user_item['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <input type="hidden" name="update_role" value="1">
                                    </form>
                                    <?php if ($user_item['id'] != ($_SESSION['user_id'] ?? 0)) : ?>
                                        <a href="user_hapus.php?id=<?= $user_item['id']; ?>" class="btn btn-danger btn-sm" title="Hapus Pengguna" data-bs-toggle="modal" data-bs-target="#konfirmasiHapusModal">
                                            <i class="bi bi-person-x-fill"></i>
                                        </a> <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" title="Tidak dapat menghapus diri sendiri" disabled><i class="bi bi-person-x-fill"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach;
                    else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Data pengguna tidak ditemukan.</td>
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
                    $base_url = "user_data.php?";
                    $query_params = [];
                    if (!empty($search_keyword_user)) $query_params['search_user'] = $search_keyword_user;
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

<?php
require_once 'partials/footer_admin.php';
?>