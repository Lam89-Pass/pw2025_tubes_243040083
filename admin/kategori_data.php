<?php
session_start();
$page_title = "Manajemen Kategori";
require_once 'partials/header_admin.php';

// Untuk Tambah Kategori Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $nama_kategori = $_POST['nama_kategori'] ?? '';
    $result = tambah_kategori($nama_kategori);
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message_crud'] = $result['message'];
    }
    //untuk menghindari resubmit form saat refresh
    header("Location: kategori_data.php");
    exit;
}

// Ambil semua data kategori untuk ditampilkan di tabel
$categories = get_all_categories();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-tags-fill me-2"></i><?= htmlspecialchars($page_title); ?></h1>
</div>

<div class="row">
    <!-- Form Tambah Kategori -->
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Kategori Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="kategori_data.php">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required placeholder="Contoh: Oli Mesin">
                    </div>
                    <button type="submit" name="tambah_kategori" class="btn btn-success w-100">
                        <i class="bi bi-save-fill me-2"></i>Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Kategori -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Kategori</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="ps-3">NO</th>
                                <th scope="col">Nama Kategori</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)) : ?>
                                <?php $i = 1;
                                foreach ($categories as $kategori) : ?>
                                    <tr>
                                        <th scope="row" class="ps-3"><?= $i++; ?></th>
                                        <td><?= htmlspecialchars($kategori['nama_kategori']); ?></td>
                                        <td class="text-center action-buttons">
                                            <a href="kategori_edit.php?id=<?= $kategori['id_kategori']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="kategori_hapus.php?id=<?= $kategori['id_kategori']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori \" <?= htmlspecialchars(addslashes($kategori['nama_kategori'])); ?>\"?');">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">Belum ada data kategori.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'partials/footer_admin.php';
?>