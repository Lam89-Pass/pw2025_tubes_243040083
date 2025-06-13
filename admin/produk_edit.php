<?php
session_start();
require_once '../functions.php';
protect_admin_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk_update = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0);

    $result = update_produk($_POST, $_FILES, $id_produk_update);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: produk_data.php");
        exit;
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
        header("Location: produk_edit.php?id=" . $id_produk_update);
        exit;
    }
}


$page_title = "Edit Produk";
$id_produk_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produk_lama = null;

if ($id_produk_edit <= 0) {
    $_SESSION['error_message_crud'] = "ID Produk tidak valid.";
    header("Location: produk_data.php");
    exit;
}

$produk_lama = get_produk_by_id($id_produk_edit);
if (!$produk_lama) {
    $_SESSION['error_message_crud'] = "Produk tidak ditemukan.";
    header("Location: produk_data.php");
    exit;
}

$categories = get_all_categories();
$all_merek = get_all_merek();
$product_current_merek = array_keys(get_merek_for_product($id_produk_edit));

$nama_produk_form = $produk_lama['name'];
$category_id_form = $produk_lama['category_id'];
$harga_form = $produk_lama['price'];
$original_price_form = $produk_lama['original_price'];
$stok_form = $produk_lama['stock'];
$deskripsi_form = $produk_lama['deskripsi'];
$gambar_lama_form = $produk_lama['image'];
$selected_merek = $product_current_merek;
require_once 'partials/header_admin.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Produk: <?= htmlspecialchars($nama_produk_form); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="produk_data.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>Kembali ke Daftar Produk
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="produk_edit.php?id=<?= $id_produk_edit; ?>" enctype="multipart/form-data">
            <input type="hidden" name="id_produk" value="<?= $id_produk_edit; ?>">
            <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($gambar_lama_form); ?>">

            <div class="mb-3">
                <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($nama_produk_form); ?>" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php if (!empty($categories)) : foreach ($categories as $kategori) : ?>
                                <option value="<?= $kategori['id_kategori']; ?>" <?= ($category_id_form == $kategori['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="harga" class="form-label">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="harga" name="harga" placeholder="Contoh: 65000" value="<?= htmlspecialchars($harga_form); ?>" required min="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="original_price" class="form-label">Harga Asli/Coret (Rp) (Opsional)</label>
                    <input type="number" class="form-control" id="original_price" name="original_price" placeholder="Contoh: 70000" value="<?= htmlspecialchars($original_price_form); ?>" min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($stok_form); ?>" required min="0">
                </div>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($deskripsi_form); ?></textarea>
            </div>

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
                            <p class="text-muted">Belum ada data merek.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-9 mb-3">
                    <label for="gambar_produk" class="form-label">Ganti Gambar Produk (Opsional)</label>
                    <input class="form-control" type="file" id="gambar_produk" name="gambar_produk" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
                <div class="col-md-3 mb-3 text-center text-md-start">
                    <?php if (!empty($gambar_lama_form)) : ?>
                        <p class="mb-1 small">Gambar Saat Ini:</p>
                        <img src="../img/img_produk/<?= htmlspecialchars($gambar_lama_form); ?>" alt="Gambar Produk Lama" style="max-height: 80px; max-width: 100px; border-radius: 0.25rem; border: 1px solid #dee2e6;">
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" name="update_produk" class="btn btn-primary btn-lg px-4">
                    <i class="bi bi-save-fill me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once 'partials/footer_admin.php';
?>