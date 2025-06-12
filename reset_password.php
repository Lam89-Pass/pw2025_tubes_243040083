<?php
session_start();
require 'functions.php';

if (!isset($_GET['token'])) {
    die("Token tidak ditemukan.");
}

$token = $_GET['token'];
$hashed_token = hash('sha256', $token);

$user = query("SELECT * FROM users WHERE password_reset_token = '$hashed_token' AND password_reset_expires > NOW()")[0] ?? null;

if (!$user) {
    die("Link reset password tidak valid atau sudah kedaluwarsa.");
}

if (isset($_POST['reset_password'])) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if ($password_baru === $konfirmasi_password) {
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
        $user_id = $user['id'];

        // Update password dan hapus token
        query("UPDATE users SET password = '$hashed_password', password_reset_token = NULL, password_reset_expires = NULL WHERE id = $user_id");

        header("Location: login.php?pesan=sukses_reset_password");
        exit;
    } else {
        $error = "Password tidak cocok!";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <h2>Reset Password Anda</h2>
    <?php if (isset($error)): ?><p style="color:red;"><?= $error; ?></p><?php endif; ?>
    <form method="post">
        <label>Password Baru</label>
        <input type="password" name="password_baru" required>
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="konfirmasi_password" required>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
</body>

</html>