<?php
session_start();
require_once '../functions.php';
protect_admin_page();

$id_kategori_hapus = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_kategori_hapus > 0) {
    $result = hapus_kategori($id_kategori_hapus);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
} else {
    $_SESSION['error_message_crud'] = "ID Kategori tidak valid untuk dihapus.";
}

header("Location: kategori_data.php");
exit;
