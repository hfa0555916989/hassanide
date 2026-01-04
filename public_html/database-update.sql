-- =====================================================
-- Hassan IDE - تحديث جدول التراخيص
-- =====================================================
-- أضف هذه التعديلات لدعم نظام الترخيص المتقدم
-- =====================================================

-- إضافة أعمدة جديدة لجدول licenses
ALTER TABLE `licenses` 
    ADD COLUMN IF NOT EXISTS `token` TEXT DEFAULT NULL AFTER `license_key`,
    ADD COLUMN IF NOT EXISTS `devices` JSON DEFAULT '[]' AFTER `machine_id`,
    ADD COLUMN IF NOT EXISTS `max_devices` INT UNSIGNED DEFAULT 3 AFTER `devices`;

-- تحديث الأعمدة الموجودة
ALTER TABLE `licenses` 
    MODIFY COLUMN `machine_id` VARCHAR(255) DEFAULT NULL;

-- إنشاء جدول سجل التفعيلات
CREATE TABLE IF NOT EXISTS `license_activations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_id` INT UNSIGNED NOT NULL,
    `machine_id` VARCHAR(255) NOT NULL,
    `machine_name` VARCHAR(255) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `os_info` VARCHAR(100) DEFAULT NULL,
    `app_version` VARCHAR(20) DEFAULT NULL,
    `action` ENUM('activate', 'validate', 'deactivate') NOT NULL,
    `success` TINYINT(1) DEFAULT 1,
    `error_code` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`license_id`) REFERENCES `licenses`(`id`) ON DELETE CASCADE,
    INDEX `idx_license` (`license_id`),
    INDEX `idx_machine` (`machine_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إنشاء جدول الميزات
CREATE TABLE IF NOT EXISTS `features` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `name_ar` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `min_plan` ENUM('starter', 'pro', 'teams') NOT NULL DEFAULT 'pro',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج الميزات الافتراضية
INSERT INTO `features` (`code`, `name`, `name_ar`, `min_plan`) VALUES
('basic_editor', 'Basic Editor', 'المحرر الأساسي', 'starter'),
('syntax_highlighting', 'Syntax Highlighting', 'تلوين الكود', 'starter'),
('file_explorer', 'File Explorer', 'مستكشف الملفات', 'starter'),
('terminal', 'Integrated Terminal', 'الطرفية المدمجة', 'starter'),
('git_basic', 'Git Basic', 'Git الأساسي', 'starter'),
('ai_assistant', 'AI Assistant', 'مساعد الذكاء الاصطناعي', 'pro'),
('advanced_debugging', 'Advanced Debugging', 'التصحيح المتقدم', 'pro'),
('templates', 'Project Templates', 'قوالب المشاريع', 'pro'),
('cloud_sync', 'Cloud Sync', 'المزامنة السحابية', 'pro'),
('extensions_unlimited', 'Unlimited Extensions', 'إضافات غير محدودة', 'pro'),
('hassan_panel', 'Hassan Panel', 'لوحة حسن', 'pro'),
('code_snippets', 'Code Snippets', 'مقاطع الكود', 'pro'),
('multi_cursor', 'Multi Cursor', 'المؤشرات المتعددة', 'pro'),
('team_collaboration', 'Team Collaboration', 'تعاون الفريق', 'teams'),
('shared_workspaces', 'Shared Workspaces', 'مساحات العمل المشتركة', 'teams'),
('team_analytics', 'Team Analytics', 'تحليلات الفريق', 'teams'),
('admin_dashboard', 'Admin Dashboard', 'لوحة تحكم المدير', 'teams'),
('sso_integration', 'SSO Integration', 'تكامل SSO', 'teams'),
('priority_support', 'Priority Support', 'دعم أولوية', 'teams'),
('code_review', 'Code Review', 'مراجعة الكود', 'teams')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- إنشاء جدول إحصائيات الاستخدام
CREATE TABLE IF NOT EXISTS `usage_stats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `license_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `active_minutes` INT UNSIGNED DEFAULT 0,
    `features_used` JSON DEFAULT '[]',
    `extensions_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`license_id`) REFERENCES `licenses`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_license_date` (`license_id`, `date`),
    INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

