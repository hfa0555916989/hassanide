<?php
/**
 * Hassan IDE - Configuration File
 * ================================
 * إعدادات الموقع و PayMob و قاعدة البيانات
 */

// منع الوصول المباشر
if (!defined('HASSAN_IDE')) {
    die('Direct access not allowed');
}

// ===========================================
// إعدادات الموقع
// ===========================================
define('SITE_NAME', 'Hassan IDE');
define('SITE_URL', 'https://hassanide.com');
define('SITE_EMAIL', 'support@hassanide.com');

// ===========================================
// إعدادات قاعدة البيانات
// ===========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'hassanide_db');      // غيّرها حسب اسم قاعدة بياناتك
define('DB_USER', 'hassanide_user');    // غيّرها حسب اسم المستخدم
define('DB_PASS', 'YOUR_DB_PASSWORD');  // غيّرها لكلمة مرور قوية

// ===========================================
// إعدادات PayMob
// ===========================================
define('PAYMOB_API_KEY', getenv('PAYMOB_API_KEY') ?: 'your_paymob_api_key');
define('PAYMOB_SECRET_KEY', getenv('PAYMOB_SECRET_KEY') ?: 'your_paymob_secret_key');
define('PAYMOB_PUBLIC_KEY', getenv('PAYMOB_PUBLIC_KEY') ?: 'your_paymob_public_key');
define('PAYMOB_MERCHANT_ID', '14187');

// Integration IDs لطرق الدفع المختلفة
define('PAYMOB_INTEGRATION_CARD', '19892');        // MIGS-online Payment link
define('PAYMOB_INTEGRATION_APPLE_PAY', '19889');   // MIGS-online (APPLE PAY)

// ===========================================
// إعدادات الباقات والأسعار (بالريال السعودي)
// ===========================================
$PLANS = [
    'starter' => [
        'name' => 'Starter',
        'name_ar' => 'المبتدئ',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'features' => [
            'Hassan IDE الأساسي',
            '5 إضافات فقط',
            'Pack واحد (Web أو Python)',
            'بدون تحديثات تلقائية',
            'بدون دعم فني'
        ],
        'trial_days' => 0,
        'is_free' => true
    ],
    'pro' => [
        'name' => 'Pro',
        'name_ar' => 'احترافي',
        'price_monthly' => 29,
        'price_yearly' => 290,
        'features' => [
            'كل مميزات Starter',
            'جميع الـ Packs',
            'إضافات غير محدودة',
            'تحديثات تلقائية',
            'Hassan Panel كامل',
            'دعم بالإيميل (48 ساعة)'
        ],
        'trial_days' => 7,
        'is_free' => false
    ],
    'teams' => [
        'name' => 'Teams',
        'name_ar' => 'فرق العمل',
        'price_monthly' => 99,
        'price_yearly' => 990,
        'features' => [
            'كل مميزات Pro',
            '5 مستخدمين',
            'لوحة تحكم الفريق',
            'سياسات وصلاحيات',
            'دعم أولوية (24 ساعة)',
            'فاتورة رسمية'
        ],
        'trial_days' => 7,
        'is_free' => false
    ]
];

// ===========================================
// إعدادات الأمان
// ===========================================
define('LICENSE_SECRET', 'hassan_ide_license_secret_2024_change_this');
define('SESSION_LIFETIME', 86400 * 30); // 30 يوم

// ===========================================
// إعدادات البريد الإلكتروني
// ===========================================
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'support@hassanide.com');
define('SMTP_PASS', 'YOUR_EMAIL_PASSWORD');

// ===========================================
// URLs
// ===========================================
define('PAYMOB_CALLBACK_URL', SITE_URL . '/api/webhook.php');
define('PAYMOB_REDIRECT_URL', SITE_URL . '/payment-success.php');

// ===========================================
// اتصال قاعدة البيانات
// ===========================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// ===========================================
// دوال مساعدة
// ===========================================
function getPlan($planId) {
    global $PLANS;
    return $PLANS[$planId] ?? null;
}

function formatPrice($price) {
    return number_format($price, 0) . ' ريال';
}

function isProduction() {
    return strpos(SITE_URL, 'localhost') === false;
}
