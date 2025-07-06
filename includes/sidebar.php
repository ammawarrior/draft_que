<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? 0; // Get user role from session
?>
<style>
    .main-sidebar {
        background: linear-gradient(-45deg, #1c2c4a, #25376d, #3b4e8c);
        background-size: 400% 400%;
        animation: sidebarGradient 20s ease infinite;
        color: #ffffff; /* ensure text is visible */
    }

    .main-sidebar .sidebar-brand,
    .main-sidebar .sidebar-brand-sm,
    .main-sidebar .sidebar-menu {
        background: transparent; /* inherit animated background */
    }

    .main-sidebar a,
    .main-sidebar .menu-header,
    .main-sidebar i {
        color: #ffffff !important;
    }

    .main-sidebar .sidebar-menu li a:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }

    @keyframes sidebarGradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>

<!-- Start main left sidebar menu -->
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="index-2.html">RSTL - X</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="index-2.html">RSTL</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            <li class="dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-fire"></i><span>Dashboard</span></a>
                <ul class="dropdown-menu">
            
                <?php if (in_array($role, [6, 7])): // Show only for Sample Receiving/Releasing and Inquiry Counter ?>

    <li><a class="nav-link" href="queue_dashboard.php">Queue</a></li>
    <li><a class="nav-link" href="display.php" target="_blank">Display</a></li>
    <li><a class="nav-link" href="client.php" target="_blank">Client Queue</a></li>

<?php endif; ?>


                </ul>
            </li>
        </ul>
    </aside>
</div>
