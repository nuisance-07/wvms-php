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
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="<?php echo $currentUser ? 'dashboard-body role-' . $currentUser['role'] : 'public-body'; ?>">

<?php if ($currentUser): ?>
<!-- DASHBOARD LAYOUT -->
<div class="dashboard-wrapper">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path>
                </svg>
                <span>WVMS</span>
            </div>
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
                        <span class="badge badge-info" style="margin-left:auto"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>

            <?php elseif ($currentUser['role'] === 'vendor'): ?>
                <a href="/vendor/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="/vendor/orders.php" class="nav-item <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                    <span class="nav-icon">📥</span> Incoming
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
                        <span class="badge badge-info" style="margin-left:auto"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
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
            <div class="user-profile-card">
                <div class="avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></div>
                <div class="user-details">
                    <span class="user-name"><?php echo sanitize($currentUser['name']); ?></span>
                    <span class="user-role"><?php echo sanitize($currentUser['role']); ?></span>
                </div>
                <a href="/logout.php" class="logout-btn" aria-label="Logout" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <div class="flex items-center gap-4">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <h1 class="page-title"><?php echo isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard'; ?></h1>
            </div>
            
            <div class="topbar-right">
                <!-- Notification Bell -->
                <div class="notif-wrapper" id="notifWrapper">
                    <button class="notif-btn" onclick="toggleNotifications()" aria-label="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>Notifications</h4>
                            <?php if ($unreadCount > 0): ?>
                                <button onclick="markAllRead()" class="notif-mark-read">Mark all read</button>
                            <?php endif; ?>
                        </div>
                        <div class="notif-body" id="notifList">
                            <div class="text-center" style="padding:24px;color:var(--text-muted)">Loading...</div>
                        </div>
                        <div class="notif-footer">
                            <a href="/<?php echo $currentUser['role']; ?>/notifications.php">View all notifications</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-container">
            <?php displayFlash(); ?>

<?php else: ?>
<!-- Public layout wrapper -->
<div class="public-wrapper">
<?php endif; ?>
