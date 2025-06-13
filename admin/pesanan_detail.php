<?php
session_start();
$page_title = "Detail Pesanan";
require_once '../functions.php';
protect_admin_page();

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pesanan <= 0) {
    $_SESSION['error_message_crud'] = "ID Pesanan tidak valid.";
    header("Location: pesanan_data.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status_baru = $_POST['status_pesanan'];
    if (update_status_pesanan($id_pesanan, $status_baru)) {
        $_SESSION['success_message'] = "Status pesanan berhasil diperbarui.";
    } else {
        $_SESSION['error_message_crud'] = "Gagal memperbarui status pesanan.";
    }
    header("Location: pesanan_detail.php?id=" . $id_pesanan);
    exit;
}

$pesanan = get_pesanan_by_id($id_pesanan);
$detail_items = get_detail_pesanan_items($id_pesanan);

if (!$pesanan) {
    $_SESSION['error_message_crud'] = "Pesanan dengan ID No. " . $id_pesanan . " tidak ditemukan.";
    header("Location: pesanan_data.php");
    exit;
}
require_once 'partials/header_admin.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><a href="pesanan_data.php" class="text-dark text-decoration-none"><i class="bi bi-arrow-left"></i></a> Detail Pesanan No. <?= $pesanan['id_pesanan']; ?></h1>
</div>

<div class="row g-4">
    <!-- Detail Item Pesanan -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam-fill me-2"></i>Item yang Dipesan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Produk</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-center">Harga Satuan</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail_items as $item): ?>
                                <tr>
                                    <td class="d-flex align-items-center ps-3">
                                        <img src="../img/img_produk/<?= htmlspecialchars($item['image'] ?: 'placeholder.png'); ?>" class="product-image me-3">
                                        <span><?= htmlspecialchars($item['nama_produk_saat_pesan']); ?></span>
                                    </td>
                                    <td class="text-center"><?= $item['kuantitas']; ?></td>
                                    <td class="text-center">Rp <?= number_format($item['harga_saat_pesan'], 0, ',', '.'); ?></td>
                                    <td class="text-end pe-3">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-group-divider">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end pe-3">Total Harga Produk</td>
                                <td class="text-end pe-3">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Info & Aksi -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Informasi Pelanggan</h5>
            </div>
            <div class="card-body">
                <strong><?= htmlspecialchars($pesanan['nama_penerima']); ?></strong>
                <p class="text-muted mb-1">Username: <?= htmlspecialchars($pesanan['username']); ?></p>
                <p class="text-muted mb-0"><?= htmlspecialchars($pesanan['no_hp_penerima']); ?></p>
                <hr>
                <p class="mb-1"><strong>Alamat Pengiriman:</strong></p>
                <p class="text-muted"><?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])); ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i>Status & Aksi</h5>
            </div>
            <div class="card-body">
                <p>
                    Metode Pembayaran: <strong class="text-success"><?= $pesanan['metode_pembayaran']; ?></strong>
                </p>
                <form method="POST" action="pesanan_detail.php?id=<?= $id_pesanan; ?>">
                    <label for="status_pesanan" class="form-label">Ubah Status Pesanan:</label>
                    <select class="form-select mb-2" name="status_pesanan">
                        <option value="Menunggu Pembayaran" <?= ($pesanan['status_pesanan'] == 'Menunggu Pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                        <option value="Diproses" <?= ($pesanan['status_pesanan'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Dikirim" <?= ($pesanan['status_pesanan'] == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="Selesai" <?= ($pesanan['status_pesanan'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= ($pesanan['status_pesanan'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'partials/footer_admin.php'; ?>