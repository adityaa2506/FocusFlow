<?php
// Ensure session is started, in case it wasn't already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
    <div class="nav-brand">FocusFlow</div>
    <div class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="reports.php">Reports</a>

            <?php if ($_SESSION['role'] === 'manager'): ?>
                <a href="admin.php">Manage Users</a>
                <a href="settings.php">Settings</a>
            <?php endif; ?>

            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</div>