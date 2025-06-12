<?php
require '../functions.php';

// Ambil semua parameter filter dari request
$keyword = $_POST['keyword'] ?? '';
$kategori = $_POST['kategori'] ?? [];
$merek = $_POST['merek'] ?? [];
$sort = $_POST['sort'] ?? 'terbaru';

// Bangun query SQL secara dinamis
$sql = "SELECT DISTINCT p.id, p.name, p.price, p.original_price, p.image 
        FROM product p 
        LEFT JOIN categories c ON p.category_id = c.id_kategori
        LEFT JOIN produk_merek pm ON p.id = pm.product_id";

$where_conditions = [];
$params = [];
$types = "";

if (!empty($keyword)) {
    $where_conditions[] = "p.name LIKE ?";
    $params[] = "%" . $keyword . "%";
    $types .= "s";
}

if (!empty($kategori)) {
    $placeholders = implode(',', array_fill(0, count($kategori), '?'));
    $where_conditions[] = "p.category_id IN ($placeholders)";
    foreach ($kategori as $cat_id) {
        $params[] = $cat_id;
    }
    $types .= str_repeat('i', count($kategori));
}

if (!empty($merek)) {
    $placeholders = implode(',', array_fill(0, count($merek), '?'));
    $where_conditions[] = "pm.merek_id IN ($placeholders)";
    foreach ($merek as $merek_id) {
        $params[] = $merek_id;
    }
    $types .= str_repeat('i', count($merek));
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(' AND ', $where_conditions);
}

// Tambahkan logika sorting
switch ($sort) {
    case 'termurah':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'termahal':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'terbaru':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Eksekusi query dengan prepared statement
$stmt = mysqli_prepare($conn, $sql);
if ($stmt && !empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Tampilkan hasilnya dalam bentuk HTML
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="col-lg-3 col-md-4 col-6 fade-in-element">'; // Ubah lg-2 menjadi lg-3 agar lebih besar
        echo '    <div class="card card-product h-100">';
        echo '        <a href="detail_produk.php?id=' . htmlspecialchars($row["id"]) . '" class="text-decoration-none text-dark d-flex flex-column h-100">';
        echo '            <div class="product-image-container">';
        echo '                <img src="img/img_produk/' . htmlspecialchars($row["image"] ?: 'placeholder.png') . '" class="card-img-top" alt="' . htmlspecialchars($row["name"]) . '">';
        echo '            </div>';
        echo '            <div class="card-body text-center d-flex flex-column p-2">';
        echo '                <h6 class="product-title flex-grow-1">' . htmlspecialchars($row["name"]) . '</h6>';
        echo '                <div class="mt-2">';
        if (!empty($row["original_price"]) && $row["original_price"] > $row["price"]) {
            echo '                    <p class="small text-muted text-decoration-line-through mb-0" style="font-size: 0.8rem;">Rp ' . number_format($row["original_price"], 0, ',', '.') . '</p>';
        }
        echo '                    <p class="product-price mb-2">Rp ' . number_format($row["price"], 0, ',', '.') . '</p>';
        echo '                </div>';
        echo '                <a href="detail_produk.php?id=' . htmlspecialchars($row["id"]) . '" class="btn btn-sm btn-outline-success mt-auto">Lihat Detail</a>';
        echo '            </div>';
        echo '        </a>';
        echo '    </div>';
        echo '</div>';
    }
} else {
    echo '<div class="col-12"><div class="alert alert-warning text-center">Produk tidak ditemukan. Coba ubah filter Anda.</div></div>';
}
mysqli_stmt_close($stmt);
