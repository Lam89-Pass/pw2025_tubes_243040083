<?php
session_start();
require_once '../functions.php'; 
protect_admin_page(); 

// Proses form submission SEBELUM output HTML apa pun.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = tambah_produk($_POST, $_FILES);
    if ($result['success']) {
        // Jika sukses, set pesan dan redirect.
        $_SESSION['success_message'] = $result['message'];
        header("Location: produk_data.php"); 
        exit;
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
}

// Data yang dibutuhkan untuk menampilkan form.
$page_title = "Tambah Produk Baru";
$all_merek = get_all_merek();
$categories = get_all_categories();

// Inisialisasi variabel untuk repopulate form fields jika ada error
$nama_produk = $_POST['nama_produk'] ?? '';
$category_id_selected = $_POST['category_id'] ?? '';
$harga = $_POST['harga'] ?? '';
$original_price = $_POST['original_price'] ?? '';
$stok = $_POST['stok'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';
$selected_merek = $_POST['merek'] ?? [];
require_once 'partials/header_admin.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($page_title); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="produk_data.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>Kembali ke Daftar Produk
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="produk_tambah.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($nama_produk); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php if (!empty($categories)) : foreach ($categories as $kategori) : ?>
                                <option value="<?= $kategori['id_kategori']; ?>" <?= ($category_id_selected == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="harga" class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="harga" name="harga" placeholder="Contoh: 65000" value="<?= htmlspecialchars($harga); ?>" required min="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="original_price" class="form-label">Harga Asli/Coret (Rp) (Opsional)</label>
                    <input type="number" class="form-control" id="original_price" name="original_price" placeholder="Contoh: 70000" value="<?= htmlspecialchars($original_price); ?>" min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($stok); ?>" required min="0">
                </div>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($deskripsi); ?></textarea>
            </div>

            <!-- MEREK MOTOR -->
            <div class="mb-3">
                <label class="form-label fw-medium">Kompatibilitas Merek Motor (Opsional)</label>
                <div class="border rounded p-3" style="max-height: 150px; overflow-y: auto;">
                    <div class="row">
                        <?php if (!empty($all_merek)) : foreach ($all_merek as $merek) : ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="merek[]" value="<?= $merek['id_merek']; ?>" id="merek_<?= $merek['id_merek']; ?>"
                                            <?php if (in_array($merek['id_merek'], $selected_merek)) echo 'checked'; ?>>
                                        <label class="form-check-label" for="merek_<?= $merek['id_merek']; ?>">
                                            <?= htmlspecialchars($merek['nama_merek']); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <p class="text-muted">Belum ada data merek. Silakan tambahkan di Manajemen Kategori.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="gambar_produk" class="form-label">Gambar Produk (Opsional, Maks 2MB)</label>
                <input class="form-control" type="file" id="gambar_produk" name="gambar_produk" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-save-fill me-2"></i>Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>