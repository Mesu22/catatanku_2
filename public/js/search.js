function filterTasks(searchTerm) {
    searchTerm = searchTerm.toLowerCase();
    const taskItems = document.querySelectorAll('.task-list-item');
    
    taskItems.forEach(item => {
        const title = item.querySelector('h3').textContent.toLowerCase();
        const description = item.querySelector('p').textContent.toLowerCase();
        const shouldShow = title.includes(searchTerm) || description.includes(searchTerm);
        item.style.display = shouldShow ? 'block' : 'none';
    });
    
    // Tampilkan pesan jika tidak ada hasil
    const noResults = document.querySelector('.no-results');
    if (taskItems.length === 0 || ![...taskItems].some(item => item.style.display !== 'none')) {
        if (!noResults) {
            const noResultsMsg = document.createElement('p');
            noResultsMsg.className = 'no-results';
            noResultsMsg.innerHTML = `Tidak ada tugas yang cocok dengan pencarian: <strong>"${searchTerm}"</strong>`;
            document.querySelector('.task-list').appendChild(noResultsMsg);
        }
    } else if (noResults) {
        noResults.remove();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.querySelector('.search-bar');
    const clearButton = document.querySelector('.search-clear');

    if (!searchBar || !clearButton) {
        console.error('Search elements not found');
        return;
    }

    // Event listener untuk input pencarian
    searchBar.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        clearButton.style.display = searchTerm.length > 0 ? 'block' : 'none';
        
        // Trigger custom event untuk pencarian
        const searchEvent = new CustomEvent('search', { 
            detail: { searchTerm: searchTerm.toLowerCase() } 
        });
        document.dispatchEvent(searchEvent);
    });

    // Event listener untuk tombol clear
    clearButton.addEventListener('click', function() {
        searchBar.value = '';
        this.style.display = 'none';
        // Trigger custom event untuk reset pencarian
        const searchEvent = new CustomEvent('search', { 
            detail: { searchTerm: '' } 
        });
        document.dispatchEvent(searchEvent);
        searchBar.focus();
    });

    // Event listener untuk tombol Enter
    searchBar.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const searchEvent = new CustomEvent('search', { 
                detail: { searchTerm: this.value.toLowerCase().trim() } 
            });
            document.dispatchEvent(searchEvent);
        }
    });
});
