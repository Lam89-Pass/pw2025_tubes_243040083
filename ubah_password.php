<?php
session_start();
require 'functions.php';

if (!isset($_SESSION["login"]) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_profile = get_user_by_id($user_id);
if (!$user_profile) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$page_title = "Ubah Password";
$pesan_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ubah_password'])) {
    $result = ubah_password_user($user_id, $_POST['password_saat_ini'], $_POST['password_baru'], $_POST['konfirmasi_password_baru']);
    if ($result['success']) {
        session_destroy();
        header("Location: login.php?pesan=sukses_ganti_password");
        exit;
    } else {
        $pesan_password = '<div class="alert alert-danger" role="alert">' . htmlspecialchars($result['message']) . '</div>';
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title); ?> | Bengkelin Aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
    <style>
        .profile-nav-card .list-group-item.active {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'partials/navbar_user.php'; ?>

    <div class="main-content-wrapper">
        <div class="container my-5">
            <div class="row g-4">
                <!-- Kolom Kiri Navigasi Profil -->
                <div class="col-lg-4">
                    <div class="card shadow-sm profile-nav-card">
                        <div class="card-body text-center">
                            <img src="img/foto_profil/<?= htmlspecialchars($user_profile['foto_profil'] ?: 'placeholder_profile.png'); ?>" alt="Foto Profil" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;border:3px solid var(--bs-success);">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($user_profile['nama_lengkap'] ?: $user_profile['username']); ?></h5>
                            <p class="text-muted small">@<?= htmlspecialchars($user_profile['username']); ?></p>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-house-door-fill me-2"></i>Beranda</a>
                            <a href="profile.php" class="list-group-item list-group-item-action"><i class="bi bi-person-fill me-2"></i>Profil Saya</a>
                            <a href="pesanan_saya.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam-fill me-2"></i>Riwayat Pesanan</a>
                            <a href="ubah_password.php" class="list-group-item list-group-item-action active"><i class="bi bi-key-fill me-2"></i>Ubah Password</a>
                            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan Form Ubah Password -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Ubah Password Anda</h4>
                        </div>
                        <div class="card-body p-4">
                            <?= $pesan_password; ?>
                            <form method="POST" action="ubah_password.php">
                                <div class="mb-3">
                                    <label for="password_saat_ini" class="form-label fw-medium">Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_saat_ini" name="password_saat_ini" required>
                                </div>
                                <hr class="my-4">
                                <div class="mb-3">
                                    <label for="password_baru" class="form-label fw-medium">Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru" required minlength="6">
                                    <small class="form-text text-muted">Minimal 6 karakter.</small>
                                </div>
                                <div class="mb-4">
                                    <label for="konfirmasi_password_baru" class="form-label fw-medium">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="konfirmasi_password_baru" name="konfirmasi_password_baru" required minlength="6">
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="profile.php" class="btn btn-outline-secondary px-4">Batal</a>
                                    <button type="submit" name="submit_ubah_password" class="btn btn-success px-4"><i class="bi bi-key-fill me-2"></i>Ubah Password</button>
                                </div>
                            </form>
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