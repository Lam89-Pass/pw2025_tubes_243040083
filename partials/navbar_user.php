<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
    <div class="container-fluid px-md-4">
        <a class="navbar-brand p-0 me-lg-3" href="dashboard.php" aria-label="Bengkelin Aja Home">
            <img src="img/logoasli.png" alt="BengkelinAja Logo" style="max-height: 50px; width: auto;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUserContent" aria-controls="navbarUserContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarUserContent">

            <div class="mx-auto my-2 my-lg-0" style="width: 100%; max-width: 550px;">
                <div class="search-container">
                    <form class="d-flex" role="search" action="allproduct.php" method="GET" autocomplete="off">
                        <div class="input-group search-input-group w-100">
                            <span class="input-group-text ps-3">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input class="form-control" type="search" name="search" placeholder="Cari suku cadang, oli, helm..." aria-label="Cari Produk" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit" class="d-none" aria-hidden="true"></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex align-items-center nav-icons fs-4 ms-lg-auto">
                <a href="keranjang.php" class="nav-icon-link nav-link me-3 position-relative" aria-label="Keranjang Belanja">
                    <i class="bi bi-cart3"></i>
                    <?php
                    $jumlah_item_di_navbar = 0;
                    if (isset($_SESSION['user_id'])) {
                        $jumlah_item_di_navbar = hitung_item_keranjang_db($_SESSION['user_id']);
                    }
                    if ($jumlah_item_di_navbar > 0) :
                    ?>
                        <span id="cart-item-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65em;">
                            <?= $jumlah_item_di_navbar; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <a href="#" class="nav-icon-link nav-link dropdown-toggle" id="profileUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Profil Pengguna">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileUserDropdown">
                        <li class="px-3 pt-2 pb-1">
                            <span class="small text-muted">Selamat datang,</span><br>
                            <strong class="text-dark"><?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna'); ?></strong>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-fill me-2"></i>Profil Saya</a></li>
                        <li><a class="dropdown-item" href="pesanan_saya.php"><i class="bi bi-box-seam-fill me-2"></i>Riwayat Pesanan</a></li>
                        <li><a class="dropdown-item" href="ubah_password.php"><i class="bi bi-key-fill me-2"></i>Ubah Password</a></li>

                        <?php if (can_access_panel()): ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item fw-bold text-success" href="admin/index.php"><i class="bi bi-speedometer2 me-2"></i>Masuk Panel Admin</a></li>
                        <?php endif; ?>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</nav>