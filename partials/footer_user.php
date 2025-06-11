<?php
// File: partials/footer_user.php
?>
<!-- PERBAIKAN: Menambahkan style khusus untuk ikon sosial media -->
<style>
    .footer .social-icon {
        border-radius: 50%;
        /* Membuat tombol menjadi bulat */
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease-in-out;
        /* Transisi halus */
    }

    .footer .social-icon:hover {
        background-color: var(--bs-success) !important;
        border-color: var(--bs-success) !important;
        color: #fff !important;
        transform: translateY(-2px);
    }
</style>

<footer class="footer bg-dark text-white pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row">
            <!-- Kolom 1 Tentang Perusahaan -->
            <div class="col-md-5 col-lg-5 col-xl-5 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-2">
                    <img src="img/logoasli.png" alt="BengkelinAja Logo" style="max-height: 90px; filter: brightness(0) invert(1);">
                </h6>
                <p class="text-white-50">
                    Destinasi utama Anda untuk mendapatkan beragam kebutuhan sparepart motor roda dua dan peralatan bengkel modern dari berbagai merek ternama.
                </p>
            </div>

            <!-- Kolom 2 Link Cepat -->
            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-4">
                    Produk
                </h6>
                <p><a href="allproduct.php?kategori=Aksesoris" class="text-white-50 text-decoration-none">Aksesoris</a></p>
                <p><a href="allproduct.php?kategori=Oli" class="text-white-50 text-decoration-none">Oli</a></p>
                <p><a href="allproduct.php?kategori=Helm" class="text-white-50 text-decoration-none">Helm</a></p>
                <p><a href="allproduct.php?kategori=Suku Cadang" class="text-white-50 text-decoration-none">Suku Cadang</a></p>
            </div>

            <!-- Kolom 3 Kontak & Sosial Media -->
            <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mb-md-0 mb-4">
                <h6 class="text-uppercase fw-bold mb-4">Kontak</h6>
                <p class="text-white-50"><i class="bi bi-geo-alt-fill me-3"></i>Jl. Ciwaruga No. 26, Bandung</p>
                <p class="text-white-50"><i class="bi bi-envelope-fill me-3"></i>info@bengkelinaja.com</p>
                <p class="text-white-50"><i class="bi bi-whatsapp me-3"></i>+62 852 2156 0909</p>
                <div class="mt-4">
                    <a href="#" class="btn btn-outline-light btn-floating m-1 social-icon" role="button"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
        &copy; <?= date("Y"); ?> BengkelinAja. Seluruh Hak Cipta Dilindungi.
        <span class="text-white-50">| Dikembangkan oleh <a href="#" class="text-decoration-none fw-semibold" style="color: var(--bs-success);">Muhamad Nur Salam</a></span>
    </div>
</footer>