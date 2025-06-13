<?php
// 1. Memulai Sesi
session_start();

// 2. Cek Jika Pengguna Sudah Login
if (isset($_SESSION["login"])) {
    header("Location: dashboard.php");
    exit;
}

// 3. Memuat File Eksternal
require 'functions.php';

// 4. Inisialisasi Variabel Pesan
$error_message = '';
$success_message = '';

// 5. Menampilkan Pesan Sukses (dari Registrasi atau Ganti Password)
if (isset($_SESSION['success_message_register'])) {
    $success_message = $_SESSION['success_message_register'];
    unset($_SESSION['success_message_register']);
} elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_ganti_password') {
    $success_message = "Password berhasil diubah. Silakan login kembali.";
}

// 6. Proses Utama Saat Tombol Login Ditekan
if (isset($_POST["login"])) {
    // 7. Cek Koneksi Database
    if (isset($conn)) {
        // 8. Ambil dan Bersihkan Input dari Form
        $username = trim($_POST["username"]);
        $password = $_POST["password"];

        // 9. Validasi Input Kosong
        if (empty($username) || empty($password)) {
            $error_message = "Username dan password wajib diisi.";
        } else {
            // 10. Menyiapkan dan Menjalankan Query Database dengan Prepared Statement
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            // 11. Cek Jika Username Ditemukan
            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);
                // 12. Verifikasi Password
                if (password_verify($password, $row["password"])) {
                    // 13. Jika Berhasil: Set Sesi dan Arahkan ke Dashboard
                    $_SESSION["login"] = true;
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["user_role"] = $row["role"];

                    header("Location: dashboard.php");
                    exit;
                }
            }
            // 14. Jika Gagal: Siapkan Pesan Error
            $error_message = "Username atau password salah!";
        }
    } else {
        $error_message = "Koneksi ke database gagal.";
    }
}
