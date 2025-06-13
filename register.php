<?php
session_start();
require 'functions.php';

if (isset($_SESSION["login"])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

if (isset($_POST["register"])) {
    $result = registrasi($_POST);
    if (isset($result['success']) && $result['success']) {
        $_SESSION['success_message_register'] = $result['message'];
        header("Location: login.php");
        exit;
    } else {
        $error_message = $result['message'] ?? 'Terjadi kesalahan.';
    }
}
?>
<!DOCTYPE html>
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
    <title>Daftar Akun | Seiko Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login_user.css?v=<?= time(); ?>">
</head>

<body class="login-layout">
    <div class="container-fluid p-0">
        <div class="row g-0 full-height">
            <div class="col-lg-5 info-panel d-none d-lg-flex">
                <div>
                    <div class="logo-area">
                        <img src="img/logoputih.png" alt="Seiko Motor" style="max-width: 300px;">
                    </div>
                    <p class="app-description">
                        Satu langkah lebih dekat dengan sparepart berkualitas. Daftar sekarang dan nikmati kemudahan berbelanja.
                    </p>
                </div>
            </div>

            <div class="col-lg-7 col-md-12 form-container">
                <div class="mx-auto" style="max-width: 450px; width: 100%;">
                    <div class="logo-area d-lg-none text-center">
                        <img src="img/logoasli.png" alt="BengkelinAja Logo" style="max-height: 70px;">
                    </div>

                    <h2 class="form-title">Buat Akun Baru Anda</h2>

                    <?php if (!empty($error_message)) : ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Buat username unik" required value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Ulangi password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-submit btn-block w-100">Daftar</button>
                    </form>
                    <p class="mt-4 text-center">
                        Sudah punya akun? <a href="login.php" class="switch-link">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>