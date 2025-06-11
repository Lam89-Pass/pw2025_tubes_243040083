
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- JAVASCRIPT UNTUK LIVE SEARCH -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchContainer = document.querySelector('.search-container');

        if (searchContainer) {
            const searchInput = searchContainer.querySelector('input[name="search"]');
            let searchResultsContainer = document.getElementById('live-search-results');

            // Buat container hasil jika belum ada
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
                    fetch(`ajax/live_search.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.text())
                        .then(data => {
                            if (data.trim() !== '') {
                                searchResultsContainer.innerHTML = data;
                                searchResultsContainer.style.display = 'block';
                            } else {
                                searchResultsContainer.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Error Live Search:', error));
                } else {
                    searchResultsContainer.style.display = 'none';
                }
            });

            // Sembunyikan hasil jika klik di luar area pencarian
            document.addEventListener('click', function(event) {
                if (!searchContainer.contains(event.target)) {
                    searchResultsContainer.style.display = 'none';
                }
            });
        }
    });
</script>