<?php
// Pastikan session dimulai di setiap file yang membutuhkan
session_start();
?>
<div class="navbar <?php echo isset($_SESSION['theme_preference']) ? $_SESSION['theme_preference'] : 'light'; ?>" id="mainNavbar">
    <img src="img/logo/logo mesa.png" alt="CatatanKu Logo">
    <div class="search-wrapper">
        <input type="text" class="search-bar" placeholder="Cari tugas...">
        <button class="search-clear" style="display: none;">×</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.querySelector('.search-bar');
    const clearButton = document.querySelector('.search-clear');
    const searchWrapper = document.querySelector('.search-wrapper');

    if (searchBar && clearButton && searchWrapper) {
        searchBar.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            clearButton.style.display = searchTerm.length > 0 ? 'flex' : 'none';
            
            // Pastikan filterTasks tersedia sebelum memanggilnya
            if (typeof filterTasks === 'function') {
                console.log('Triggering search for:', searchTerm);
                filterTasks(searchTerm);
            } else {
                console.log('filterTasks function not available');
            }
        });

        // Fungsi untuk menghapus isi pencarian
        clearButton.addEventListener('click', function() {
            searchBar.value = '';
            clearButton.style.display = 'none';
            if (typeof filterTasks === 'function') {
                filterTasks('');
            }
            searchBar.focus();
        });

        // Tambahkan animasi ketika search bar difokuskan
        searchBar.addEventListener('focus', function() {
            searchWrapper.classList.add('active');
        });

        searchBar.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                searchWrapper.classList.remove('active');
            }
        });

        // Tambahkan styles untuk search functionality
        const searchStyles = document.createElement('style');
        searchStyles.textContent = `
            .no-results {
                text-align: center;
                padding: 20px;
                margin: 20px 0;
                background: var(--component-bg);
                border-radius: 8px;
                color: var(--text-color);
            }

            .no-results p {
                margin: 0;
                font-size: 0.95rem;
                color: #666;
            }

            .task {
                transition: all 0.3s ease;
            }

            .search-match {
                background-color: rgba(99, 102, 241, 0.1);
            }
        `;
        document.head.appendChild(searchStyles);
    }
});
</script>
