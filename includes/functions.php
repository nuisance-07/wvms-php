<?php
/**
 * WVMS — Utility Functions
 * Common helpers for sanitization, formatting, notifications, and UI components
 */

require_once __DIR__ . '/db.php';

/**
 * Sanitize user input for XSS prevention
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency in KES
 */
function formatCurrency($amount) {
    return 'KES ' . number_format((float)$amount, 2);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '—';
    return date($format, strtotime($date));
}

/**
 * Format date and time
 */
function formatDateTime($date) {
    if (empty($date)) return '—';
    return date('M d, Y h:i A', strtotime($date));
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $classes = [
        'pending'    => 'badge-warning',
        'accepted'   => 'badge-info',
        'dispatched' => 'badge-orange',
        'delivered'  => 'badge-success',
        'cancelled'  => 'badge-danger',
        'confirmed'  => 'badge-success',
        'active'     => 'badge-success',
        'inactive'   => 'badge-danger',
        'suspended'  => 'badge-orange',
        'in_transit' => 'badge-orange',
        'failed'     => 'badge-danger'
    ];
    $class = $classes[$status] ?? 'badge-secondary';
    $label = ucfirst(str_replace('_', ' ', $status));
    return '<span class="status-badge ' . $class . '">' . $label . '</span>';
}

/**
 * Create a notification for a user
 */
function createNotification($userId, $message, $type = 'system') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $message, $type]);
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get recent notifications for a user
 */
function getNotifications($userId, $limit = 10) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Mark notification as read
 */
function markNotificationRead($notifId, $userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notifId, $userId]);
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsRead($userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    return $stmt->execute([$userId]);
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $icon = match($flash['type']) {
            'success' => '✓',
            'error'   => '✕',
            'warning' => '⚠',
            'info'    => 'ℹ',
            default   => 'ℹ'
        };
        echo '<div class="alert alert-' . $flash['type'] . '" id="flash-alert">';
        echo '<span class="alert-icon">' . $icon . '</span>';
        echo '<span>' . sanitize($flash['message']) . '</span>';
        echo '<button class="alert-close" onclick="this.parentElement.remove()">×</button>';
        echo '</div>';
    }
}

/**
 * Get vendor info by vendor table ID
 */
function getVendorById($vendorId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT v.*, u.name, u.email, u.phone FROM vendors v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
    $stmt->execute([$vendorId]);
    return $stmt->fetch();
}

/**
 * Get vendor info by user ID
 */
function getVendorByUserId($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT v.*, u.name, u.email, u.phone FROM vendors v JOIN users u ON v.user_id = u.id WHERE v.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Get all active vendors
 */
function getActiveVendors() {
    $db = getDB();
    $stmt = $db->query("SELECT v.*, u.name, u.phone FROM vendors v JOIN users u ON v.user_id = u.id WHERE v.status = 'active' AND u.status = 'active'");
    return $stmt->fetchAll();
}

/**
 * Get all active vendors with rating for marketplace
 */
function getActiveVendorsWithRating() {
    $db = getDB();
    $stmt = $db->query("
        SELECT v.*, u.name, u.phone, 
               COALESCE(AVG(f.rating), 0) as avg_rating,
               COUNT(f.id) as total_reviews
        FROM vendors v 
        JOIN users u ON v.user_id = u.id 
        LEFT JOIN water_orders wo ON wo.vendor_id = v.id
        LEFT JOIN feedback f ON f.order_id = wo.id
        WHERE v.status = 'active' AND u.status = 'active'
        GROUP BY v.id
        ORDER BY avg_rating DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Get average vendor rating
 */
function getVendorRating($vendorId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT AVG(f.rating) as avg_rating, COUNT(f.id) as total_reviews 
                          FROM feedback f 
                          JOIN water_orders wo ON f.order_id = wo.id 
                          WHERE wo.vendor_id = ?");
    $stmt->execute([$vendorId]);
    $result = $stmt->fetch();
    return [
        'avg_rating' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'total_reviews' => (int)$result['total_reviews']
    ];
}

/**
 * Render star rating HTML
 */
function renderStars($rating, $size = '') {
    $sizeClass = $size ? 'stars-' . $size : '';
    $html = '<div class="star-display ' . $sizeClass . '">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<span class="star filled">★</span>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<span class="star half">★</span>';
        } else {
            $html .= '<span class="star empty">☆</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Simple pagination
 */
function paginate($totalItems, $currentPage, $perPage = 10) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'       => $totalItems,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset
    ];
}

/**
 * Render pagination HTML
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';

    $html = '<div class="pagination">';
    
    // Previous button
    if ($pagination['current'] > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current'] - 1) . '" class="page-btn">‹ Prev</a>';
    }

    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i == $pagination['current']) {
            $html .= '<span class="page-btn active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="page-btn">' . $i . '</a>';
        }
    }

    // Next button
    if ($pagination['current'] < $pagination['total_pages']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current'] + 1) . '" class="page-btn">Next ›</a>';
    }

    $html .= '</div>';
    return $html;
}

/* ============================================================
 * FUTURE SCOPE — placeholder functions
 * ============================================================ */

// TODO: GPS real-time delivery tracking
// function getDeliveryLocation($deliveryId) { }

// SMS notifications via Africa's Talking API
require_once __DIR__ . '/sms.php';

// TODO: Full M-Pesa Daraja API integration
// function initiateMpesaPayment($phone, $amount, $orderId) { }

// TODO: Multi-vendor marketplace mode
// function searchVendorsByLocation($lat, $lng, $radius) { }
