<?php
session_start();
require 'functions.php';
if (!isset($_SESSION["login"]) || !isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu untuk mengakses profil Anda.";
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

// Tentukan mode tampilan 'view' (default) atau 'edit'
$mode = isset($_GET['mode']) && $_GET['mode'] === 'edit' ? 'edit' : 'view';

$page_title = "Profil Saya";
$pesan_update = '';

// Proses form jika ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $_POST['foto_profil_lama'] = $user_profile['foto_profil'] ?? '';
    $result = update_user_profile($user_id, $_POST, $_FILES);

    if ($result['success']) {
        $_SESSION['success_message_profile'] = $result['message'];
        header("Location: profile.php");
        exit;
    } else {
        $mode = 'edit';
        $pesan_update = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($result['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

// Ambil pesan sukses dari sesi
if (isset($_SESSION['success_message_profile'])) {
    $pesan_update = '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message_profile']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['success_message_profile']);
}

// Inisialisasi variabel untuk form/tampilan
$nama_lengkap_form = $user_profile['nama_lengkap'] ?? '';
$jenis_kelamin_form = $user_profile['jenis_kelamin'] ?? '';
$tanggal_lahir_form = $user_profile['tanggal_lahir'] ?? '';
$no_hp_form = $user_profile['no_hp'] ?? '';
$alamat_lengkap_form = $user_profile['alamat_lengkap'] ?? '';
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title); ?> | Bengkelin Aja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?= time(); ?>">
    <style>
        .profile-nav-card .list-group-item.active {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: white;
        }

        .info-label {
            font-weight: 500;
            color: #6c757d;
        }

        .info-value {
            font-weight: 500;
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
                            <a href="profile.php" class="list-group-item list-group-item-action active"><i class="bi bi-person-fill me-2"></i>Profil Saya</a>
                            <a href="pesanan_saya.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam-fill me-2"></i>Riwayat Pesanan</a>
                            <a href="ubah_password.php" class="list-group-item list-group-item-action"><i class="bi bi-key-fill me-2"></i>Ubah Password</a>
                            <a href="logout.php" class="list-group-item list-group-item-action text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan Tampilan atau Form Edit -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>Informasi Akun</h4>
                            <?php if ($mode === 'view') : ?>
                                <a href="profile.php?mode=edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i> Edit Profil</a>
                            <?php else : ?>
                                <a href="profile.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i> Batal Edit</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4">
                            <?= $pesan_update; ?>
                            <?php if ($mode === 'edit') : ?>
                                <form method="POST" action="profile.php?mode=edit" enctype="multipart/form-data">
                                    <input type="hidden" name="foto_profil_lama" value="<?= htmlspecialchars($user_profile['foto_profil']); ?>">
                                    <div class="mb-3"><label for="nama_lengkap" class="form-label">Nama Lengkap</label><input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap_form); ?>"></div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Jenis Kelamin</label>
                                            <div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_kelamin" id="laki-laki" value="Laki-laki" <?= ($jenis_kelamin_form == 'Laki-laki') ? 'checked' : ''; ?>><label class="form-check-label" for="laki-laki">Laki-laki</label></div>
                                                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="Perempuan" <?= ($jenis_kelamin_form == 'Perempuan') ? 'checked' : ''; ?>><label class="form-check-label" for="perempuan">Perempuan</label></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3"><label for="tanggal_lahir" class="form-label">Tanggal Lahir</label><input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= htmlspecialchars($tanggal_lahir_form); ?>"></div>
                                    </div>
                                    <div class="mb-3"><label for="no_hp" class="form-label">Nomor HP</label><input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($no_hp_form); ?>"></div>
                                    <div class="mb-3"><label for="alamat_lengkap" class="form-label">Alamat Lengkap</label><textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3"><?= htmlspecialchars($alamat_lengkap_form); ?></textarea></div>
                                    <div class="mb-4"><label for="foto_profil_baru" class="form-label">Ganti Foto Profil</label><input class="form-control" type="file" id="foto_profil_baru" name="foto_profil_baru"></div>
                                    <div class="d-flex justify-content-end"><button type="submit" name="update_profil" class="btn btn-success px-4"><i class="bi bi-save2-fill me-2"></i>Simpan</button></div>
                                </form>
                            <?php else : ?>
                                <dl class="row">
                                    <dt class="col-sm-4 info-label">Username</dt>
                                    <dd class="col-sm-8 info-value">@<?= htmlspecialchars($user_profile['username']); ?></dd>
                                    <dt class="col-sm-4 info-label">Email</dt>
                                    <dd class="col-sm-8 info-value"><?= htmlspecialchars($user_profile['email']); ?></dd>
                                    <hr class="my-3">
                                    <dt class="col-sm-4 info-label">Nama Lengkap</dt>
                                    <dd class="col-sm-8 info-value"><?= htmlspecialchars($nama_lengkap_form ?: '-'); ?></dd>
                                    <dt class="col-sm-4 info-label">Jenis Kelamin</dt>
                                    <dd class="col-sm-8 info-value"><?= htmlspecialchars($jenis_kelamin_form ?: '-'); ?></dd>
                                    <dt class="col-sm-4 info-label">Tanggal Lahir</dt>
                                    <dd class="col-sm-8 info-value"><?= !empty($tanggal_lahir_form) ? date('d F Y', strtotime($tanggal_lahir_form)) : '-'; ?></dd>
                                    <dt class="col-sm-4 info-label">Nomor HP</dt>
                                    <dd class="col-sm-8 info-value"><?= htmlspecialchars($no_hp_form ?: '-'); ?></dd>
                                    <dt class="col-sm-4 info-label">Alamat Lengkap</dt>
                                    <dd class="col-sm-8 info-value"><?= nl2br(htmlspecialchars($alamat_lengkap_form ?: '-')); ?></dd>
                                </dl>
                            <?php endif; ?>
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