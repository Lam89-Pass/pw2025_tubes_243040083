</main>
</div>
</div>

<div class="modal fade" id="konfirmasiHapusModal" tabindex="-1" aria-labelledby="konfirmasiHapusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h1 class="modal-title fs-5" id="konfirmasiHapusLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Penghapusan</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="tombolHapusModal" class="btn btn-danger">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const konfirmasiHapusModal = document.getElementById('konfirmasiHapusModal');

        if (konfirmasiHapusModal) {
            // Event ini akan berjalan TEPAT SEBELUM modal ditampilkan
            konfirmasiHapusModal.addEventListener('show.bs.modal', function(event) {
                // Dapatkan tombol/link yang diklik oleh user
                const button = event.relatedTarget;

                // Ambil URL untuk proses hapus dari atribut 'href' tombol tersebut
                const urlHapus = button.getAttribute('href');

                // Cari tombol 'Ya, Hapus' di dalam modal
                const tombolHapusDiModal = konfirmasiHapusModal.querySelector('#tombolHapusModal');

                // Setel atribut 'href' pada tombol 'Ya, Hapus' dengan URL yang sudah didapat
                tombolHapusDiModal.setAttribute('href', urlHapus);
            });
        }
    });
</script>

<?php if (isset($custom_js)): ?>
    <?= $custom_js ?>
<?php endif; ?>
</body>

</html>