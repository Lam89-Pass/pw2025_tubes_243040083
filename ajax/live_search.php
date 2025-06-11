<?php
require '../functions.php'; 
if (isset($_GET['q'])) {
    $search_query = trim($_GET['q']);
    $results = [];

    if (!empty($search_query) && isset($conn)) {
        $search_param = "%" . $search_query . "%";

        // Query mencari berdasarkan NAMA PRODUK atau NAMA KATEGORI
        $sql = "SELECT p.id, p.name, p.image, p.price, c.nama_kategori 
                FROM product p
                LEFT JOIN categories c ON p.category_id = c.id_kategori
                WHERE p.name LIKE ? OR c.nama_kategori LIKE ?
                ORDER BY p.name ASC 
                LIMIT 5"; 

        // Siapkan dan eksekusi statement
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Tampilkan hasil pencarian
    if (!empty($results)) {
        foreach ($results as $product) {
            echo '<a href="detail_produk.php?id=' . htmlspecialchars($product['id']) . '" class="list-group-item list-group-item-action d-flex align-items-center">';
            echo '  <img src="img/img_produk/' . htmlspecialchars($product['image'] ?: 'placeholder.png') . '" alt="" style="width: 50px; height: 50px; object-fit: cover; margin-right: 15px; border-radius: 0.375rem;">';
            echo '  <div class="flex-grow-1">';
            echo '      <div class="fw-semibold">' . htmlspecialchars($product['name']) . '</div>';
            echo '      <small class="text-muted">di Kategori: ' . htmlspecialchars($product['nama_kategori'] ?? 'Lainnya') . '</small>';
            echo '  </div>';
            echo '  <span class="fw-bold text-success">Rp ' . number_format($product['price'], 0, ',', '.') . '</span>';
            echo '</a>';
        }
    } elseif (!empty($search_query)) {
        // Jika tidak ada hasil
        echo '<div class="list-group-item text-muted text-center">Tidak ada hasil yang cocok ditemukan.</div>';
    }
}
