<?php
session_start();
$page_title = "Manajemen Komentar";
require_once 'partials/header_admin.php';

// menghapus komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_komentar'])) {
    $id_komentar_hapus = (int)$_POST['id_komentar'];
    $result = hapus_komentar_admin($id_komentar_hapus);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
    header("Location: komentar_data.php");
    exit;
}

// Ambil semua komentar dari database untuk ditampilkan
$all_comments = query("SELECT k.id_komentar, k.komentar, k.created_at, u.username, p.name as product_name, p.id as product_id_link 
                      FROM komentar_produk k 
                      JOIN users u ON k.user_id = u.id 
                      JOIN product p ON k.product_id = p.id 
                      ORDER BY k.created_at DESC");

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-chat-square-dots-fill me-2"></i><?= htmlspecialchars($page_title); ?></h1>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Semua Komentar</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" class="ps-3">Pengguna</th>
                        <th scope="col" style="width: 40%;">Komentar</th>
                        <th scope="col">Pada Produk</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_comments)) : ?>
                        <?php foreach ($all_comments as $comment) : ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-medium"><?= htmlspecialchars($comment['username']); ?></span>
                                </td>
                                <td><small class="text-muted"><?= nl2br(htmlspecialchars($comment['komentar'])); ?></small></td>
                                <td><a href="../detail_produk.php?id=<?= $comment['product_id_link']; ?>" target="_blank" class="text-decoration-none"><?= htmlspecialchars($comment['product_name']); ?></a></td>
                                <td><?= date('d M Y, H:i', strtotime($comment['created_at'])); ?></td>
                                <td class="text-center action-buttons">
                                    <a href="komentar_hapus.php?id=<?= $comment['id_komentar']; ?>" class="btn btn-danger btn-sm" title="Hapus Komentar" data-bs-toggle="modal" data-bs-target="#konfirmasiHapusModal">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Belum ada komentar dari pengguna.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'partials/footer_admin.php';
?>