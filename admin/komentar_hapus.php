<?php
session_start();
require_once '../functions.php';
protect_admin_page();

$id_komentar_hapus = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_komentar_hapus > 0) {
    $result = hapus_komentar_admin($id_komentar_hapus);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
} else {
    $_SESSION['error_message_crud'] = "ID Komentar tidak valid untuk dihapus.";
}

header("Location: komentar_data.php");
exit;
