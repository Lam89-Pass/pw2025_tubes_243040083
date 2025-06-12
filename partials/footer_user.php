<link rel="stylesheet" href="css/dashboard.css">

<div class="modal fade" id="notifikasiModal" tabindex="-1" aria-labelledby="notifikasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center border-0 shadow-lg">
            <div class="modal-body p-4">
                <div id="notifikasi-icon-container" class="mb-3">
                </div>
                <h5 class="modal-title mb-2" id="notifikasiModalLabel"></h5>
                <p id="notifikasi-body" class="text-muted mb-0"></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="konfirmasiAksiModal" tabindex="-1" aria-labelledby="konfirmasiAksiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="konfirmasiAksiLabel">Konfirmasi Tindakan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="konfirmasiAksiBody">
                Apakah Anda yakin ingin melakukan tindakan ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="tombolAksiModal" class="btn btn-danger">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<footer class="footer bg-dark text-white pt-5 pb-4">
    <div class="container text-center text-md-start">
        <div class="row">
            <div class="col-md-5 col-lg-5 col-xl-5 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-2">
                    <img src="img/logoasli.png" alt="BengkelinAja Logo" style="max-height: 90px; filter: brightness(0) invert(1);">
                </h6>
                <p class="text-white-50">
                    Destinasi utama Anda untuk mendapatkan beragam kebutuhan sparepart motor roda dua dan peralatan bengkel modern dari berbagai merek ternama.
                </p>
            </div>

            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                <h6 class="text-uppercase fw-bold mb-4">
                    Produk
                </h6>
                <p><a href="allproduct.php?kategori=Aksesoris" class="text-white-50 text-decoration-none">Aksesoris</a></p>
                <p><a href="allproduct.php?kategori=Oli" class="text-white-50 text-decoration-none">Oli</a></p>
                <p><a href="allproduct.php?kategori=Helm" class="text-white-50 text-decoration-none">Helm</a></p>
                <p><a href="allproduct.php?kategori=Suku Cadang" class="text-white-50 text-decoration-none">Suku Cadang</a></p>
            </div>

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
    <div class="chatbot-toggler">
        <i class="bi bi-chat-dots-fill"></i>
    </div>

    <div class="chatbot-window">
        <div class="chatbot-header">
            <div class="chatbot-info">
                <img src="img/logoputih.png" alt="logo" height="30">
                <h5 class="ms-2 mb-0">Chat Bot</h5>
            </div>
            <button class="chatbot-close-btn">&times;</button>
        </div>
        <ul class="chatbot-conversation">
            <li class="chatbot-message bot">
                <p>Halo! 👋<br>Ada yang bisa saya bantu? Coba ketik "lacak pesanan", "metode pembayaran", atau "lokasi toko".</p>
            </li>
        </ul>
        <div class="chatbot-input">
            <textarea placeholder="Ketik pesan Anda..." required></textarea>
            <button class="chatbot-send-btn"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
        &copy; <?= date("Y"); ?> BengkelinAja. Seluruh Hak Cipta Dilindungi.
        <span class="text-white-50">| Dikembangkan oleh <a href="#" class="text-decoration-none fw-semibold" style="color: var(--bs-success);">Muhamad Nur Salam</a></span>
    </div>
</footer>