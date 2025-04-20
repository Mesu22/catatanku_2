<!-- Sidebar -->
<div class="sidebar <?php echo isset($_SESSION['theme_preference']) ? $_SESSION['theme_preference'] : 'light'; ?>" id="mainSidebar">
    <div id="toggleSidebar" class="toggle-btn">☰</div>
    <div class="menu-container">
        <a href="beranda.php" class="menu-item">
            <span class="menu-icon">📝</span>
            <span class="menu-text">Tasks</span>
        </a>
        <a href="kalender.php" class="menu-item">
            <span class="menu-icon">📅</span>
            <span class="menu-text">Calendar</span>
        </a>
        <a href="settings.php" class="menu-item">
            <span class="menu-icon">⚙️</span>
            <span class="menu-text">Settings</span>
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="logout.php" class="menu-item logout-btn">
            <span class="menu-icon">🚪</span>
            <span class="menu-text">Logout</span>
        </a>
    </div>
</div>