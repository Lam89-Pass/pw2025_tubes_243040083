<?php
session_start();
if (isset($_SESSION["login"])) {
    header("Location: dashboard.php");
    exit;
}

require 'functions.php';

$error_message = '';
$success_message = '';

// Menangkap pesan sukses dari halaman registrasi atau ubah password
if (isset($_SESSION['success_message_register'])) {
    $success_message = $_SESSION['success_message_register'];
    unset($_SESSION['success_message_register']);
} elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_ganti_password') {
    $success_message = "Password berhasil diubah. Silakan login kembali.";
}

if (isset($_POST["login"])) {
    if (isset($conn)) {
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        if (empty($username) || empty($password)) {
            $error_message = "Username dan password wajib diisi.";
        } else {
            // Menggunakan prepared statement untuk keamanan
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                if (password_verify($password, $row["password"])) {
                    // Set session untuk pengguna yang berhasil login
                    $_SESSION["login"] = true;
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["username"] = $row["username"];
                    // Simpan peran pengguna (role) ke dalam session
                    $_SESSION["user_role"] = $row["role"];

                    header("Location: dashboard.php");
                    exit;
                }
            }
            // Jika username tidak ditemukan atau password salah
            $error_message = "Username atau password salah!";
        }
    } else {
        $error_message = "Koneksi ke database gagal.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BengkelinAja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login_user.css?v=<?= time(); ?>">
</head>

<body class="login-layout">
    <div class="container-fluid p-0">
        <div class="row g-0 full-height">
            <div class="col-lg-5 info-panel d-none d-lg-flex">
                <div>
                    <div class="logo-area"><img src="img/logoputih.png" alt="BengkelinAja Logo Putih" style="max-width: 300px;"></div>
                    <p class="app-description">Satu akun untuk semua kebutuhan. Login untuk mengakses dashboard, keranjang belanja, dan riwayat pesanan Anda.</p>
                </div>
            </div>
            <div class="col-lg-7 col-md-12 form-container">
                <div class="mx-auto" style="max-width: 450px; width: 100%;">
                    <div class="logo-area d-lg-none text-center"><img src="img/logoasli.png" alt="BengkelinAja Logo"></div>
                    <h2 class="form-title">Selamat Datang Kembali!</h2>

                    <?php if (!empty($success_message)) : ?>
                        <div class="alert alert-success" role="alert"><?= htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)) : ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required></div>
                        <div class="mb-4"><label for="password" class="form-label">Password</label><input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required></div>
                        <button type="submit" name="login" class="btn btn-submit w-100">Login</button>
                    </form>
                    <p class="mt-4 text-center">Belum punya akun? <a href="register.php" class="switch-link">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>