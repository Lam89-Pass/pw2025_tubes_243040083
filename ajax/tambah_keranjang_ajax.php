<?php
session_start();
require '../functions.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan.', 'cart_count' => 0];

if (isset($_SESSION['login'], $_SESSION['user_id']) && isset($_POST['id_produk'], $_POST['kuantitas'])) {
    $user_id = (int)$_SESSION['user_id'];
    $product_id = (int)$_POST['id_produk'];
    $kuantitas = (int)$_POST['kuantitas'];

    if ($product_id > 0 && $kuantitas > 0) {
        $result = tambah_ke_keranjang_db($user_id, $product_id, $kuantitas);
        if ($result['success']) {
            $response['success'] = true;
            $response['message'] = 'Produk berhasil ditambahkan ke keranjang!';
        } else {
            $response['message'] = $result['message']; 
        }
    } else {
        $response['message'] = 'Data produk tidak valid.';
    }

    $response['cart_count'] = hitung_item_keranjang_db($user_id);
} else {
    $response['message'] = 'Anda harus login untuk menambahkan produk.';
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
