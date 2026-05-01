-- ============================================================
-- WVMS — Water Vendor Management System
-- Database Schema
-- Created: 2026-05-01
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

CREATE DATABASE IF NOT EXISTS `wvms_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wvms_db`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('customer','vendor','admin') NOT NULL DEFAULT 'customer',
    `location` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: vendors
-- ============================================================
CREATE TABLE IF NOT EXISTS `vendors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `business_name` VARCHAR(200) NOT NULL,
    `service_area` VARCHAR(255) NOT NULL,
    `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_vendors_user` (`user_id`),
    INDEX `idx_vendors_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: water_orders
-- ============================================================
CREATE TABLE IF NOT EXISTS `water_orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `quantity_litres` INT NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 5.00,
    `total_amount` DECIMAL(10,2) GENERATED ALWAYS AS (`quantity_litres` * `unit_price`) STORED,
    `delivery_address` VARCHAR(500) NOT NULL,
    `preferred_delivery_time` DATETIME DEFAULT NULL,
    `status` ENUM('pending','accepted','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_orders_customer` (`customer_id`),
    INDEX `idx_orders_vendor` (`vendor_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('cash','mpesa') NOT NULL DEFAULT 'cash',
    `mpesa_code` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('pending','confirmed') NOT NULL DEFAULT 'pending',
    `confirmed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `water_orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_payments_order` (`order_id`),
    INDEX `idx_payments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: deliveries
-- ============================================================
CREATE TABLE IF NOT EXISTS `deliveries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `vendor_id` INT UNSIGNED NOT NULL,
    `scheduled_time` DATETIME DEFAULT NULL,
    `actual_delivery_time` DATETIME DEFAULT NULL,
    `delivery_notes` TEXT DEFAULT NULL,
    `status` ENUM('pending','in_transit','delivered','failed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `water_orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_deliveries_order` (`order_id`),
    INDEX `idx_deliveries_vendor` (`vendor_id`),
    INDEX `idx_deliveries_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `message` VARCHAR(500) NOT NULL,
    `type` ENUM('order','payment','system','delivery') NOT NULL DEFAULT 'system',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`is_read`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: feedback
-- ============================================================
CREATE TABLE IF NOT EXISTS `feedback` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `water_orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_feedback_order` (`order_id`),
    INDEX `idx_feedback_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TRIGGER: Auto-create delivery record when order is placed
-- ============================================================
DELIMITER //

CREATE TRIGGER `trg_after_order_insert`
AFTER INSERT ON `water_orders`
FOR EACH ROW
BEGIN
    INSERT INTO `deliveries` (`order_id`, `vendor_id`, `scheduled_time`, `status`)
    VALUES (NEW.id, NEW.vendor_id, NEW.preferred_delivery_time, 'pending');

    -- Notify the vendor about new order
    INSERT INTO `notifications` (`user_id`, `message`, `type`)
    SELECT v.user_id, CONCAT('New order #', NEW.id, ' received for ', NEW.quantity_litres, ' litres.'), 'order'
    FROM `vendors` v WHERE v.id = NEW.vendor_id;

    -- Notify the customer that order was placed
    INSERT INTO `notifications` (`user_id`, `message`, `type`)
    VALUES (NEW.customer_id, CONCAT('Your order #', NEW.id, ' has been placed successfully.'), 'order');
END //

-- ============================================================
-- TRIGGER: Notify on order status change
-- ============================================================
CREATE TRIGGER `trg_after_order_status_update`
AFTER UPDATE ON `water_orders`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        -- Notify customer about status change
        INSERT INTO `notifications` (`user_id`, `message`, `type`)
        VALUES (NEW.customer_id, CONCAT('Order #', NEW.id, ' status updated to: ', UPPER(NEW.status), '.'), 'order');

        -- If delivered, notify vendor too
        IF NEW.status = 'delivered' THEN
            INSERT INTO `notifications` (`user_id`, `message`, `type`)
            SELECT v.user_id, CONCAT('Order #', NEW.id, ' has been marked as delivered.'), 'delivery'
            FROM `vendors` v WHERE v.id = NEW.vendor_id;
        END IF;

        -- Update delivery status to match order status
        IF NEW.status = 'dispatched' THEN
            UPDATE `deliveries` SET `status` = 'in_transit' WHERE `order_id` = NEW.id;
        ELSEIF NEW.status = 'delivered' THEN
            UPDATE `deliveries` SET `status` = 'delivered', `actual_delivery_time` = NOW() WHERE `order_id` = NEW.id;
        ELSEIF NEW.status = 'cancelled' THEN
            UPDATE `deliveries` SET `status` = 'failed' WHERE `order_id` = NEW.id;
        END IF;
    END IF;
END //

-- ============================================================
-- TRIGGER: Notify on payment confirmation
-- ============================================================
CREATE TRIGGER `trg_after_payment_confirmed`
AFTER UPDATE ON `payments`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status AND NEW.status = 'confirmed' THEN
        INSERT INTO `notifications` (`user_id`, `message`, `type`)
        SELECT wo.customer_id, CONCAT('Payment of KES ', NEW.amount, ' for order #', NEW.order_id, ' has been confirmed.'), 'payment'
        FROM `water_orders` wo WHERE wo.id = NEW.order_id;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- SEED DATA: Default admin account
-- Password: Admin@2026 (bcrypt hashed)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `location`, `status`) VALUES
('System Admin', 'admin@wvms.co.ke', '0700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Nairobi CBD', 'active');

-- Sample vendor user
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `location`, `status`) VALUES
('John Mwangi', 'vendor@wvms.co.ke', '0712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendor', 'Eastlands, Nairobi', 'active');

INSERT INTO `vendors` (`user_id`, `business_name`, `service_area`, `status`) VALUES
(2, 'Maji Fresh Suppliers', 'Eastlands, Nairobi', 'active');

-- Sample customer
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `location`, `status`) VALUES
('Mary Wanjiku', 'customer@wvms.co.ke', '0723456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Umoja Estate, Nairobi', 'active');
