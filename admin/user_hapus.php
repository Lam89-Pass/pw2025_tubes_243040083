<?php
session_start();
require_once '../functions.php';
protect_admin_page();

$id_user_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_admin_id = $_SESSION['user_id'] ?? 0;

if ($id_user_to_delete > 0) {
    $result = hapus_pengguna($id_user_to_delete, $current_admin_id);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
} else {
    $_SESSION['error_message_crud'] = "ID Pengguna tidak valid untuk dihapus.";
}

header("Location: user_data.php");
exit;
