<header>
    <nav class="navbar">
        <a href="/pages/home.php">Home</a> |
        <a href="/pages/profile.php">Profile</a> |
        <a href="/pages/messages.php">Messages</a> |
        <a href="/pages/report.php">Report</a> |
        <?php if (isset($_SESSION['admin_username'])): ?>
            <a href="/pages/admin.php">Admin Panel</a> |
        <?php endif; ?>
        <a href="/pages/login.html">Logout</a>
    </nav>
</header>