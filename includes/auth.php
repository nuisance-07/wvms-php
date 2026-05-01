<?php
/**
 * WVMS — Authentication & Session Management
 * Handles login, logout, RBAC, and CSRF protection
 */

require_once __DIR__ . '/db.php';

/**
 * Initialize secure session
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path'     => '/',
            'secure'   => false, // Set to true in production with HTTPS
            'httponly'  => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

initSession();

/**
 * Login user with email and password
 */
function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        session_regenerate_id(true);
        return $user;
    }
    return false;
}

/**
 * Logout and destroy session
 */
function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'role'  => $_SESSION['user_role'],
        'email' => $_SESSION['user_email']
    ];
}

/**
 * Get full user profile from DB
 */
function getCurrentUserFull() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, location, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Require user to be logged in — redirect if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
}

/**
 * Require specific role — redirect if unauthorized
 */
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        // Redirect to appropriate dashboard
        redirectToDashboard();
        exit;
    }
}

/**
 * Redirect user to their role-based dashboard
 */
function redirectToDashboard() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit;
    }
    switch ($_SESSION['user_role']) {
        case 'admin':
            header("Location: /admin/index.php");
            break;
        case 'vendor':
            header("Location: /vendor/index.php");
            break;
        case 'customer':
            header("Location: /customer/index.php");
            break;
        default:
            header("Location: /login.php");
    }
    exit;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden input field
 */
function csrfField() {
    $token = generateCSRFToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Check CSRF token from POST request
 */
function checkCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            die("Security validation failed. Please refresh and try again.");
        }
    }
}

/**
 * Register a new customer
 */
function registerCustomer($name, $email, $phone, $password, $location) {
    $db = getDB();

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    // Check if phone already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Phone number already registered.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, role, location) VALUES (?, ?, ?, ?, 'customer', ?)");
    $stmt->execute([$name, $email, $phone, $hashedPassword, $location]);

    $userId = $db->lastInsertId();

    // Auto-login
    $_SESSION['user_id']    = $userId;
    $_SESSION['user_name']  = $name;
    $_SESSION['user_role']  = 'customer';
    $_SESSION['user_email'] = $email;
    session_regenerate_id(true);

    return ['success' => true, 'user_id' => $userId];
}
