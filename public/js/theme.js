function applyTheme(theme) {
    // Update classes for all themed elements
    document.body.className = theme;
    const navbar = document.querySelector('.navbar');
    const sidebar = document.querySelector('.sidebar');
    
    if (navbar) navbar.className = `navbar ${theme}`;
    if (sidebar) sidebar.className = `sidebar ${theme}`;
    
    // Save to localStorage
    localStorage.setItem('theme', theme);
}

// Function to load theme
function loadTheme() {
    // Check server-side preference first (from body class)
    const serverTheme = document.body.className;
    // Then check localStorage
    const savedTheme = localStorage.getItem('theme');
    // Finally fallback to 'light'
    const themeToApply = serverTheme || savedTheme || 'light';
    
    applyTheme(themeToApply);
}

// Initialize theme when DOM is ready
document.addEventListener('DOMContentLoaded', loadTheme);

// Listen for theme changes
window.addEventListener('storage', (e) => {
    if (e.key === 'theme') {
        applyTheme(e.newValue);
    }
});
