<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // BAGIAN 1: FUNGSI LIVE SEARCH (TIDAK DIUBAH)
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer) {
            const searchInput = searchContainer.querySelector('input[name="search"]');
            let searchResultsContainer = document.getElementById('live-search-results');
            if (!searchResultsContainer) {
                searchResultsContainer = document.createElement('div');
                searchResultsContainer.id = 'live-search-results';
                searchResultsContainer.className = 'list-group position-absolute w-100';
                searchResultsContainer.style.display = 'none';
                searchContainer.appendChild(searchResultsContainer);
            }
            searchInput.addEventListener('input', function() {
                const query = this.value;
                if (query.length > 2) {
                    fetch(`ajax/live_search.php?q=${encodeURIComponent(query)}`).then(response => response.text()).then(data => {
                        if (data.trim() !== '') {
                            searchResultsContainer.innerHTML = data;
                            searchResultsContainer.style.display = 'block';
                        } else {
                            searchResultsContainer.style.display = 'none';
                        }
                    }).catch(error => console.error('Error Live Search:', error));
                } else {
                    searchResultsContainer.style.display = 'none';
                }
            });
            document.addEventListener('click', function(event) {
                if (searchResultsContainer && !searchContainer.contains(event.target)) {
                    searchResultsContainer.style.display = 'none';
                }
            });
        }

        // BAGIAN 2: FUNGSI TAMBAH KERANJANG AJAX (SUDAH BENAR)
        const formTambahKeranjang = document.getElementById('form-tambah-keranjang');
        if (formTambahKeranjang) {
            formTambahKeranjang.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambahkan...';

                fetch('ajax/tambah_keranjang_ajax.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json()).then(data => {
                    const modalElement = document.getElementById('notifikasiModal');
                    const modalTitle = document.getElementById('notifikasiModalLabel');
                    const modalBody = document.getElementById('notifikasi-body');
                    const modalIconContainer = document.getElementById('notifikasi-icon-container');

                    if (modalElement) {
                        if (data.success) {
                            modalIconContainer.innerHTML = '<i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>';
                            modalTitle.innerText = 'Berhasil!';
                        } else {
                            modalIconContainer.innerHTML = '<i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>';
                            modalTitle.innerText = 'Gagal!';
                        }
                        modalBody.innerText = data.message;
                        const notifModal = new bootstrap.Modal(modalElement);
                        notifModal.show();
                        setTimeout(() => {
                            notifModal.hide();
                        }, 2500);
                    } else {
                        alert(data.message);
                    }

                    if (data.success) {
                        const cartCountElement = document.getElementById('cart-item-count');
                        const cartBadgeContainer = document.querySelector('.nav-icon-link[href="keranjang.php"]');
                        if (cartCountElement) {
                            if (data.cart_count > 0) {
                                cartCountElement.innerText = data.cart_count;
                                cartCountElement.style.display = 'inline-block';
                            } else {
                                cartCountElement.style.display = 'none';
                            }
                        } else if (data.cart_count > 0 && cartBadgeContainer) {
                            const newBadge = document.createElement('span');
                            newBadge.id = 'cart-item-count';
                            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                            newBadge.style.fontSize = '0.65em';
                            newBadge.innerText = data.cart_count;
                            cartBadgeContainer.appendChild(newBadge);
                        }
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan koneksi.');
                }).finally(() => {
                    button.disabled = false;
                    button.innerHTML = '+ Keranjang';
                });
            });
        }

        // BAGIAN 3: FUNGSI MODAL KONFIRMASI (KOSONGKAN KERANJANG) - PERBAIKAN DI SINI
        const konfirmasiAksiModal = document.getElementById('konfirmasiAksiModal');
        if (konfirmasiAksiModal) {
            // Fungsi untuk menangani klik pada tombol "Ya, Lanjutkan" di dalam modal
            const handleAksiKonfirmasi = function() {
                const formToSubmit = document.getElementById('form-kosongkan-keranjang');
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            };

            const tombolAksi = konfirmasiAksiModal.querySelector('#tombolAksiModal');

            // Hapus event listener lama jika ada, untuk mencegah duplikasi
            // Ini adalah trik untuk memastikan event listener tidak menumpuk setiap kali kode dijalankan
            const newTombolAksi = tombolAksi.cloneNode(true);
            tombolAksi.parentNode.replaceChild(newTombolAksi, tombolAksi);
            newTombolAksi.addEventListener('click', handleAksiKonfirmasi);

            // Event listener saat modal akan ditampilkan
            konfirmasiAksiModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget; // Tombol yang memicu modal
                const pesan = button.getAttribute('data-pesan-konfirmasi');
                const modalBody = konfirmasiAksiModal.querySelector('#konfirmasiAksiBody');

                if (pesan) {
                    modalBody.textContent = pesan;
                } else {
                    modalBody.textContent = "Apakah Anda yakin ingin melanjutkan?"; // Pesan default
                }
            });
        }

        // BAGIAN 4: FUNGSI UNTUK METODE PEMBAYARAN DI CHECKOUT
        const collapseBank = document.getElementById('collapseBank');
        const collapseEwallet = document.getElementById('collapseEwallet');

        if (collapseBank && collapseEwallet) {
            const bsCollapseBank = new bootstrap.Collapse(collapseBank, {
                toggle: false
            });
            const bsCollapseEwallet = new bootstrap.Collapse(collapseEwallet, {
                toggle: false
            });

            // Saat pilihan bank akan ditampilkan
            collapseBank.addEventListener('show.bs.collapse', () => {
                bsCollapseEwallet.hide();
                // Otomatis pilih radio button pertama di dalam grup bank
                document.querySelector('#collapseBank input[type="radio"]').checked = true;
            });

            // Saat pilihan e-wallet akan ditampilkan
            collapseEwallet.addEventListener('show.bs.collapse', () => {
                bsCollapseBank.hide();
                // Otomatis pilih radio button pertama di dalam grup e-wallet
                document.querySelector('#collapseEwallet input[type="radio"]').checked = true;
            });
        }

        // BAGIAN 5: FUNGSI UNTUK ANIMASI FADE-IN ON SCROLL
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    // Opsional: berhenti mengamati setelah animasi berjalan sekali
                    observer.unobserve(entry.target);
                }
            });
        });

        // Pilih semua elemen yang ingin dianimasikan
        const elementsToFadeIn = document.querySelectorAll('.fade-in-element');
        elementsToFadeIn.forEach((el) => observer.observe(el));



        // BAGIAN 6: FUNGSI UNTUK CHATBOT 
        const chatbotToggler = document.querySelector(".chatbot-toggler");
        const chatbotWindow = document.querySelector(".chatbot-window");
        const closeBtn = document.querySelector(".chatbot-close-btn");
        const chatInput = document.querySelector(".chatbot-input textarea");
        const sendChatBtn = document.querySelector(".chatbot-send-btn");
        const conversation = document.querySelector(".chatbot-conversation");

        if (chatbotToggler) {
            // Fungsi untuk mendapatkan respon dari bot
            const getBotResponse = (userInput) => {
                userInput = userInput.toLowerCase();

                // Urutan pengecekan dari yang paling spesifik
                if (userInput.includes("termurah")) {
                    return 'Anda bisa menemukan produk termurah dengan mudah! Buka halaman <a href="allproduct.php?sort=termurah" target="_blank">Semua Produk</a> lalu urutkan berdasarkan "Harga: Termurah".';
                } else if (userInput.includes("termahal")) {
                    return 'Untuk melihat produk termahal, silakan kunjungi halaman <a href="allproduct.php?sort=termahal" target="_blank">Semua Produk</a> dan urutkan berdasarkan "Harga: Termahal".';
                } else if (userInput.includes("pesan") || userInput.includes("lacak") || userInput.includes("order")) {
                    return 'Anda bisa melacak pesanan Anda di halaman <a href="pesanan_saya.php" target="_blank">Riwayat Pesanan</a>.';
                } else if (userInput.includes("bayar") || userInput.includes("pembayaran") || userInput.includes("transfer")) {
                    return 'Kami menerima metode pembayaran melalui Transfer Bank (BCA, BRI, Mandiri) dan E-Wallet (DANA, OVO, GoPay).';
                } else if (userInput.includes("lokasi") || userInput.includes("alamat") || userInput.includes("toko") || userInput.includes("dimana")) {
                    return 'Toko fisik kami beralamat di Jl. Ciwaruga No. 26, Bandung. Kami tunggu kedatangannya!';
                } else if (userInput.includes("perkenalkan") || userInput.includes("kamu")) {
                    return 'KeOyo Malam - Malam, Hallo! Aku Alam';
                } else if (userInput.includes("whatsapp") || userInput.includes("wa") || userInput.includes("kontak") || userInput.includes("telepon")) {
                    return 'Anda bisa menghubungi kami melalui WhatsApp di nomor <a href="https://wa.me/6285221560909" target="_blank">+62 852 2156 0909</a>.';
                } else if (userInput.includes("halo") || userInput.includes("hai") || userInput.includes("hi")) {
                    return 'Halo juga! Ada yang bisa dibantu?';
                } else if (userInput.includes("produk") || userInput.includes("barang")) {
                    return 'Anda bisa melihat semua produk kami di halaman <a href="allproduct.php" target="_blank">Semua Produk</a>.';
                } else {
                    return 'Maaf, saya tidak mengerti pertanyaan Anda. Anda bisa coba tanyakan tentang "lacak pesanan", "pembayaran", atau "lokasi toko".';
                }
            }

            // Fungsi untuk menampilkan pesan di chat
            const displayMessage = (message, sender) => {
                const messageLi = document.createElement("li");
                messageLi.classList.add("chatbot-message", sender);
                const messageP = document.createElement("p");
                messageP.innerHTML = message;
                messageLi.appendChild(messageP);
                conversation.appendChild(messageLi);
                conversation.scrollTo(0, conversation.scrollHeight);
            }

            // Fungsi untuk menangani input dari pengguna
            const handleChat = () => {
                const userMessage = chatInput.value.trim();
                if (!userMessage) return;

                displayMessage(userMessage, "user");
                chatInput.value = "";

                setTimeout(() => {
                    const botMessage = getBotResponse(userMessage);
                    displayMessage(botMessage, "bot");
                }, 600);
            }

            // Event Listeners
            chatbotToggler.addEventListener("click", () => chatbotWindow.classList.toggle("show"));
            closeBtn.addEventListener("click", () => chatbotWindow.classList.remove("show"));
            sendChatBtn.addEventListener("click", handleChat);
            chatInput.addEventListener("keydown", (e) => {
                if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    handleChat();
                }
            });
        }

        // BAGIAN 6: FUNGSI UNTUK LIVE FILTER PRODUK
        const filterForm = document.getElementById('filter-form');
        const productContainer = document.getElementById('product-list-container');
        const sortOption = document.getElementById('sort-option');
        const searchInputNavbar = document.querySelector('.navbar input[name="search"]');

        function fetchProducts() {
            if (!productContainer || !filterForm) return;

            // Tampilkan loading
            productContainer.innerHTML = '';
            productContainer.classList.add('loading');

            const formData = new FormData(filterForm);

            // Tambahkan keyword dari navbar ke form data
            if (searchInputNavbar) {
                formData.append('keyword', searchInputNavbar.value);
            }

            // Tambahkan opsi sorting ke form data
            if (sortOption) {
                formData.append('sort', sortOption.value);
            }

            // Kirim data menggunakan fetch API
            fetch('ajax/filter_produk.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    productContainer.classList.remove('loading');
                    productContainer.innerHTML = html;

                    // Re-inisialisasi observer untuk animasi fade-in pada elemen baru
                    const newElementsToFadeIn = productContainer.querySelectorAll('.fade-in-element');
                    newElementsToFadeIn.forEach(el => observer.observe(el));
                })
                .catch(error => {
                    productContainer.classList.remove('loading');
                    productContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat produk.</div>';
                    console.error('Error:', error);
                });
        }

        // Panggil fetchProducts saat halaman pertama kali dimuat
        if (document.getElementById('filter-form')) {
            fetchProducts();
        }

        // Tambahkan event listener ke setiap elemen filter
        if (filterForm) {
            filterForm.addEventListener('change', fetchProducts);
        }
        if (sortOption) {
            sortOption.addEventListener('change', fetchProducts);
        }
        if (searchInputNavbar) {
            // Tambahkan debounce agar tidak terlalu sering fetch saat mengetik
            let debounceTimer;
            searchInputNavbar.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchProducts, 500); 
            });
        }
    });
</script>