<?php
session_start();
$page_title = "Edit Kategori";
require_once 'partials/header_admin.php';


$id_kategori_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kategori_lama = null;
$nama_kategori_edit = '';

if ($id_kategori_edit > 0) {
    $kategori_lama = get_kategori_by_id($id_kategori_edit);
    if ($kategori_lama) {
        $nama_kategori_edit = $kategori_lama['nama_kategori'];
    } else {
        $_SESSION['error_message_crud'] = "Kategori tidak ditemukan.";
        header("Location: kategori_data.php");
        exit;
    }
} else {
    $_SESSION['error_message_crud'] = "ID Kategori tidak valid.";
    header("Location: kategori_data.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kategori'])) {
    $nama_kategori_baru = $_POST['nama_kategori_edit'] ?? '';
    $result = update_kategori($id_kategori_edit, $nama_kategori_baru);

    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
        header("Location: kategori_data.php");
        exit;
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
        $nama_kategori_edit = $nama_kategori_baru;
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($page_title); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="kategori_data.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle-fill me-2"></i>Kembali ke Daftar Kategori
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil-fill me-2"></i>Form Edit Kategori</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="kategori_edit.php?id=<?= $id_kategori_edit; ?>">
                    <div class="mb-3">
                        <label for="nama_kategori_edit" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori_edit" name="nama_kategori_edit" value="<?= htmlspecialchars($nama_kategori_edit); ?>" required>
                    </div>
                    <button type="submit" name="update_kategori" class="btn btn-primary w-100">
                        <i class="bi bi-save-fill me-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php require_once 'partials/footer_admin.php'; ?>