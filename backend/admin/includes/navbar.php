<div class="bottom-nav">
    <a href="dashboard.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Ana Sayfa</span>
    </a>
    <a href="apps.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'apps.php' ? 'active' : ''; ?>">
        <i class="fas fa-mobile-alt"></i>
        <span>Uygulamalar</span>
    </a>
    <a href="projects.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>">
        <i class="fas fa-code-branch"></i>
        <span>Projeler</span>
    </a>
    <a href="skills.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'skills.php' ? 'active' : ''; ?>">
        <i class="fas fa-star"></i>
        <span>Yetenekler</span>
    </a>
    <a href="timeline.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'timeline.php' ? 'active' : ''; ?>">
        <i class="fas fa-clock"></i>
        <span>Timeline</span>
    </a>
    <a href="settings.php"
        class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i>
        <span>Ayarlar</span>
    </a>
</div>