<?php
session_start();
require_once '../functions.php';
protect_admin_page();

$id_produk_hapus = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk_hapus > 0) {
    $result = hapus_produk($id_produk_hapus);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
} else {
    $_SESSION['error_message_crud'] = "ID Produk tidak valid untuk dihapus.";
}
header("Location: produk_data.php");
exit;
