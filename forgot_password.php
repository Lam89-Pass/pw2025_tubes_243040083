<?php
session_start();
require 'functions.php';
// Anda perlu fungsi kirim_email() dari pembahasan kita sebelumnya

$message = '';
if (isset($_POST['submit_email'])) {
    $email = $_POST['email'];
    // Cari user berdasarkan email
    $user = query("SELECT * FROM users WHERE email = '$email'")[0] ?? null;

    if ($user) {
        // Buat token unik
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", time() + 3600); // Token berlaku 1 jam

        // Simpan hash token ke database
        $hashed_token = hash('sha256', $token);
        query("UPDATE users SET password_reset_token = '$hashed_token', password_reset_expires = '$expires' WHERE email = '$email'");

        // Kirim email ke pengguna
        $reset_link = "http://localhost/pw2025_tubes_243040083/reset_password.php?token=$token";
        $subjek = "Link Reset Password BengkelinAja";
        $body = "Klik link berikut untuk mereset password Anda: <a href='$reset_link'>$reset_link</a>";

        // Asumsi fungsi kirim_email() sudah ada
        // kirim_email($email, $subjek, $body); 
    }
    $message = "Jika email Anda terdaftar, link untuk reset password telah dikirim.";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Lupa Password</title>
</head>

<body>
    <h2>Lupa Password</h2>
    <?php if ($message): ?><p><?= $message; ?></p><?php endif; ?>
    <form method="post">
        <label for="email">Masukkan Email Anda:</label>
        <input type="email" name="email" required>
        <button type="submit" name="submit_email">Kirim</button>
    </form>
</body>

</html>