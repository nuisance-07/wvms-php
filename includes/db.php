<?php
/**
 * WVMS — Database Connection
 * PDO connection with error handling and UTF-8 support
 */

define('DB_HOST', 'sql303.infinityfree.com');
define('DB_NAME', 'if0_41804517_wvms');
define('DB_USER', 'if0_41804517');
define('DB_PASS', 'XdJ4ATrKVYXf9');
define('DB_CHARSET', 'utf8mb4');

// Base URL - adjust for your environment
define('BASE_URL', '/');
define('SITE_NAME', 'WVMS - Water Vendor Management System');

/**
 * Get PDO database connection (singleton)
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    return $pdo;
}
