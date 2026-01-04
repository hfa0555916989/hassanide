-- =====================================================
-- Hassan IDE Database Schema
-- =====================================================
-- قم بتنفيذ هذا الملف في phpMyAdmin أو أي أداة MySQL
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+03:00";

-- إنشاء قاعدة البيانات (اختياري - قد تحتاج إنشاءها من cPanel)
-- CREATE DATABASE IF NOT EXISTS hassanide_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE hassanide_db;

-- =====================================================
-- جدول المستخدمين
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `country` VARCHAR(50) DEFAULT 'SA',
    `email_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(100) DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_verification` (`verification_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول الاشتراكات
-- =====================================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `plan` ENUM('starter', 'pro', 'teams') NOT NULL DEFAULT 'starter',
    `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
    `status` ENUM('active', 'trial', 'expired', 'cancelled') NOT NULL DEFAULT 'trial',
    `trial_ends_at` DATETIME DEFAULT NULL,
    `starts_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME DEFAULT NULL,
    `auto_renew` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول المدفوعات
-- =====================================================
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED DEFAULT NULL,
    `paymob_order_id` VARCHAR(100) DEFAULT NULL,
    `paymob_transaction_id` VARCHAR(100) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'SAR',
    `status` ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `payment_method` VARCHAR(50) DEFAULT 'card',
    `card_last_four` VARCHAR(4) DEFAULT NULL,
    `card_brand` VARCHAR(20) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_paymob_order` (`paymob_order_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول التراخيص (License Keys)
-- =====================================================
CREATE TABLE IF NOT EXISTS `licenses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NOT NULL,
    `license_key` VARCHAR(100) NOT NULL UNIQUE,
    `machine_id` VARCHAR(255) DEFAULT NULL,
    `activated_at` DATETIME DEFAULT NULL,
    `last_validated` DATETIME DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE CASCADE,
    INDEX `idx_license_key` (`license_key`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول سجل الأحداث (Audit Log)
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول التحميلات
-- =====================================================
CREATE TABLE IF NOT EXISTS `downloads` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `version` VARCHAR(20) NOT NULL,
    `platform` ENUM('windows', 'mac', 'linux') NOT NULL,
    `download_type` ENUM('installer', 'portable') DEFAULT 'installer',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_version` (`version`),
    INDEX `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول إعدادات النظام
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- إدراج إعدادات افتراضية
-- =====================================================
INSERT INTO `settings` (`key_name`, `value`) VALUES
('site_name', 'Hassan IDE'),
('current_version', '1.0.0'),
('maintenance_mode', '0'),
('registration_enabled', '1'),
('trial_days', '7');

COMMIT;
