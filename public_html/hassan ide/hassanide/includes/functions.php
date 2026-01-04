<?php
/**
 * Hassan IDE - Helper Functions
 * ==============================
 * دوال مساعدة متنوعة
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/../api/config.php';

/**
 * تنظيف المدخلات
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * إعادة التوجيه
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * عرض رسالة Flash
 */
function setFlash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Token
 */
function generateCSRF() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * تنسيق التاريخ بالعربي
 */
function formatDateAr($date) {
    $months = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "{$day} {$month} {$year}";
}

/**
 * حساب الأيام المتبقية
 */
function daysRemaining($expiryDate) {
    if (!$expiryDate) return null;
    $diff = strtotime($expiryDate) - time();
    return max(0, ceil($diff / 86400));
}

/**
 * التحقق من صلاحية الاشتراك
 */
function hasActiveSubscription($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT * FROM subscriptions 
        WHERE user_id = ? AND status IN ('active', 'trial')
        AND (expires_at IS NULL OR expires_at > NOW())
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() !== false;
}

/**
 * الحصول على باقة المستخدم
 */
function getUserPlan($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT plan FROM subscriptions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? $result['plan'] : 'starter';
}

/**
 * التحقق من ميزة معينة
 */
function hasFeature($userId, $feature) {
    $plan = getUserPlan($userId);
    
    $features = [
        'starter' => ['basic_ide', 'limited_extensions'],
        'pro' => ['basic_ide', 'unlimited_extensions', 'all_packs', 'auto_updates', 'email_support', 'hassan_panel'],
        'teams' => ['basic_ide', 'unlimited_extensions', 'all_packs', 'auto_updates', 'priority_support', 'hassan_panel', 'team_management', 'policies']
    ];
    
    return in_array($feature, $features[$plan] ?? []);
}

/**
 * إرسال بريد إلكتروني
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    $headers = [
        'From' => SITE_NAME . ' <' . SITE_EMAIL . '>',
        'Reply-To' => SITE_EMAIL,
        'MIME-Version' => '1.0'
    ];
    
    if ($isHtml) {
        $headers['Content-Type'] = 'text/html; charset=UTF-8';
    } else {
        $headers['Content-Type'] = 'text/plain; charset=UTF-8';
    }
    
    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= "{$key}: {$value}\r\n";
    }
    
    return mail($to, $subject, $body, $headerString);
}

/**
 * إنشاء قالب بريد إلكتروني
 */
function emailTemplate($title, $content, $buttonText = null, $buttonUrl = null) {
    $button = '';
    if ($buttonText && $buttonUrl) {
        $button = "
        <div style='text-align: center; margin: 30px 0;'>
            <a href='{$buttonUrl}' style='background: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                {$buttonText}
            </a>
        </div>";
    }
    
    return "
    <!DOCTYPE html>
    <html dir='rtl' lang='ar'>
    <head>
        <meta charset='UTF-8'>
        <title>{$title}</title>
    </head>
    <body style='font-family: Tahoma, Arial, sans-serif; line-height: 1.8; color: #333; background: #f5f5f5; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #4F46E5, #7C3AED); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>Hassan IDE</h1>
            </div>
            <div style='padding: 30px;'>
                <h2 style='color: #4F46E5; margin-top: 0;'>{$title}</h2>
                <div style='color: #555;'>{$content}</div>
                {$button}
            </div>
            <div style='background: #f9fafb; padding: 20px; text-align: center; color: #888; font-size: 12px;'>
                <p style='margin: 0;'>© " . date('Y') . " Hassan IDE. جميع الحقوق محفوظة.</p>
                <p style='margin: 10px 0 0;'>
                    <a href='" . SITE_URL . "' style='color: #4F46E5;'>زيارة الموقع</a>
                </p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * تحويل الحجم لصيغة قابلة للقراءة
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
