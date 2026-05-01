<?php
/**
 * WVMS — Dynamic Header
 * Includes navigation based on user role, notification bell, and mobile menu
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$unreadCount = $currentUser ? getUnreadNotificationCount($currentUser['id']) : 0;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Water Vendor Management System - Digitizing water vendor operations in urban Kenya">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — WVMS' : 'WVMS — Water Vendor Management System'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="<?php echo $currentUser ? 'dashboard-body role-' . $currentUser['role'] : 'public-body'; ?>">

<?php if ($currentUser): ?>
<!-- ============================================================ -->
<!-- DASHBOARD LAYOUT -->
<!-- ============================================================ -->

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" fill="url(#logoGrad)" opacity="0.15"/>
                    <path d="M20 8C20 8 12 18 12 24C12 28.4183 15.5817 32 20 32C24.4183 32 28 28.4183 28 24C28 18 20 8 20 8Z" fill="url(#logoGrad)" stroke="white" stroke-width="1.5"/>
                    <defs>
                        <linearGradient id="logoGrad" x1="12" y1="8" x2="28" y2="32" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#4FC3F7"/>
                            <stop offset="1" stop-color="#1565C0"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <span class="logo-text">WVMS</span>
        </div>
        <button class="sidebar-close" onclick="toggleSidebar()" aria-label="Close menu">×</button>
    </div>

    <nav class="sidebar-nav">
        <?php if ($currentUser['role'] === 'customer'): ?>
            <a href="/customer/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="/customer/place_order.php" class="nav-item <?php echo $currentPage === 'place_order' ? 'active' : ''; ?>">
                <span class="nav-icon">🛒</span> Place Order
            </a>
            <a href="/customer/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> My Orders
            </a>
            <a href="/customer/track_order.php" class="nav-item <?php echo $currentPage === 'track_order' ? 'active' : ''; ?>">
                <span class="nav-icon">📍</span> Track Order
            </a>
            <a href="/customer/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <span class="nav-icon">💳</span> Payments
            </a>
            <a href="/customer/feedback.php" class="nav-item <?php echo $currentPage === 'feedback' ? 'active' : ''; ?>">
                <span class="nav-icon">⭐</span> Feedback
            </a>
            <a href="/customer/notifications.php" class="nav-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                <span class="nav-icon">🔔</span> Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="/customer/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Profile
            </a>

        <?php elseif ($currentUser['role'] === 'vendor'): ?>
            <a href="/vendor/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="/vendor/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📥</span> Incoming Orders
            </a>
            <a href="/vendor/deliveries.php" class="nav-item <?php echo $currentPage === 'deliveries' ? 'active' : ''; ?>">
                <span class="nav-icon">🚚</span> Deliveries
            </a>
            <a href="/vendor/payments.php" class="nav-item <?php echo $currentPage === 'payments' ? 'active' : ''; ?>">
                <span class="nav-icon">💰</span> Payments
            </a>
            <a href="/vendor/reports.php" class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <span class="nav-icon">📈</span> Reports
            </a>
            <a href="/vendor/notifications.php" class="nav-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                <span class="nav-icon">🔔</span> Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="/vendor/profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span> Settings
            </a>

        <?php elseif ($currentUser['role'] === 'admin'): ?>
            <a href="/admin/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="/admin/users.php" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Users
            </a>
            <a href="/admin/vendors.php" class="nav-item <?php echo $currentPage === 'vendors' ? 'active' : ''; ?>">
                <span class="nav-icon">🏪</span> Vendors
            </a>
            <a href="/admin/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span> All Orders
            </a>
            <a href="/admin/reports.php" class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                <span class="nav-icon">📈</span> Reports
            </a>
            <a href="/admin/notifications.php" class="nav-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                <span class="nav-icon">📢</span> Announcements
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="/logout.php" class="nav-item nav-logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
</aside>

<!-- Main Content Area -->
<div class="main-wrapper">
    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <h1 class="page-title"><?php echo isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard'; ?></h1>
        </div>
        <div class="topbar-right">
            <!-- Notification Bell -->
            <div class="notification-wrapper" id="notifWrapper">
                <button class="notif-bell" onclick="toggleNotifications()" aria-label="Notifications">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notif-count"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                    <?php endif; ?>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-dropdown-header">
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <a href="#" onclick="markAllRead()" class="mark-all-read">Mark all read</a>
                        <?php endif; ?>
                    </div>
                    <div class="notif-dropdown-body" id="notifList">
                        <div class="notif-loading">Loading...</div>
                    </div>
                    <div class="notif-dropdown-footer">
                        <a href="/<?php echo $currentUser['role']; ?>/notifications.php">View All</a>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="user-menu">
                <div class="user-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></div>
                <span class="user-name"><?php echo sanitize($currentUser['name']); ?></span>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="content-area">
        <?php displayFlash(); ?>

<?php else: ?>
<!-- Public pages (login, register, landing) have no sidebar -->
<div class="public-wrapper">
<?php endif; ?>
