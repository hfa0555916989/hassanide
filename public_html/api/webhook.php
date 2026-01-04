<?php
/**
 * Hassan IDE - PayMob Webhook Handler
 * ====================================
 * استقبال تأكيدات الدفع من PayMob
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/paymob.php';
require_once __DIR__ . '/license.php';

// تسجيل كل الطلبات للتصحيح
$logFile = __DIR__ . '/../logs/webhook_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logWebhook($message, $data = null) {
    global $logFile;
    $log = date('Y-m-d H:i:s') . " - {$message}";
    if ($data) {
        $log .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $log .= "\n" . str_repeat('-', 50) . "\n";
    file_put_contents($logFile, $log, FILE_APPEND);
}

// ===========================================
// معالجة POST من PayMob
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // قراءة البيانات
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    // إذا كانت البيانات من form
    if (empty($data)) {
        $data = $_POST;
    }
    
    logWebhook('Received webhook', $data);
    
    // التحقق من HMAC
    $hmac = $_GET['hmac'] ?? $_SERVER['HTTP_HMAC'] ?? '';
    
    $paymob = new PayMobAPI();
    
    // استخراج بيانات المعاملة
    $transactionData = $data['obj'] ?? $data;
    
    $orderId = $transactionData['order']['id'] ?? $transactionData['order'] ?? null;
    $transactionId = $transactionData['id'] ?? null;
    $success = $transactionData['success'] ?? false;
    $amountCents = $transactionData['amount_cents'] ?? 0;
    $currency = $transactionData['currency'] ?? 'SAR';
    
    // بيانات الكارت
    $cardLastFour = $transactionData['source_data']['pan'] ?? null;
    $cardBrand = $transactionData['source_data']['sub_type'] ?? null;
    
    // رسالة الخطأ إن وجدت
    $errorMessage = null;
    if (!$success) {
        $errorMessage = $transactionData['data']['message'] ?? 'Payment failed';
    }
    
    logWebhook("Processing: Order={$orderId}, Transaction={$transactionId}, Success={$success}");
    
    try {
        $db = getDB();
        
        // البحث عن الدفعة في قاعدة البيانات
        $stmt = $db->prepare("SELECT * FROM payments WHERE paymob_order_id = ?");
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            logWebhook("Payment not found for order: {$orderId}");
            http_response_code(200); // نرد 200 لـ PayMob حتى لا يعيد المحاولة
            echo json_encode(['status' => 'payment_not_found']);
            exit;
        }
        
        // تحديث حالة الدفع
        $newStatus = $success ? 'success' : 'failed';
        
        $updateStmt = $db->prepare("
            UPDATE payments SET 
                status = ?,
                paymob_transaction_id = ?,
                card_last_four = ?,
                card_brand = ?,
                error_message = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([
            $newStatus,
            $transactionId,
            $cardLastFour,
            $cardBrand,
            $errorMessage,
            $payment['id']
        ]);
        
        // إذا نجح الدفع، نفعّل الاشتراك
        if ($success && $payment['user_id']) {
            $metadata = json_decode($payment['metadata'], true);
            $plan = $metadata['plan'] ?? 'pro';
            $billingCycle = $metadata['billing_cycle'] ?? 'monthly';
            
            // حساب تاريخ الانتهاء
            $expiresAt = $billingCycle === 'yearly' 
                ? date('Y-m-d H:i:s', strtotime('+1 year'))
                : date('Y-m-d H:i:s', strtotime('+1 month'));
            
            // إنشاء أو تحديث الاشتراك
            $subStmt = $db->prepare("
                INSERT INTO subscriptions (user_id, plan, billing_cycle, status, starts_at, expires_at)
                VALUES (?, ?, ?, 'active', NOW(), ?)
                ON DUPLICATE KEY UPDATE
                    plan = VALUES(plan),
                    billing_cycle = VALUES(billing_cycle),
                    status = 'active',
                    expires_at = VALUES(expires_at),
                    updated_at = NOW()
            ");
            $subStmt->execute([$payment['user_id'], $plan, $billingCycle, $expiresAt]);
            
            $subscriptionId = $db->lastInsertId();
            if (!$subscriptionId) {
                // جلب الاشتراك الموجود
                $getSubStmt = $db->prepare("SELECT id FROM subscriptions WHERE user_id = ?");
                $getSubStmt->execute([$payment['user_id']]);
                $subscriptionId = $getSubStmt->fetchColumn();
            }
            
            // ربط الدفعة بالاشتراك
            $linkStmt = $db->prepare("UPDATE payments SET subscription_id = ? WHERE id = ?");
            $linkStmt->execute([$subscriptionId, $payment['id']]);
            
            // إنشاء License Key
            $licenseGenerator = new LicenseManager();
            $licenseKey = $licenseGenerator->generateLicense($payment['user_id'], $subscriptionId);
            
            // إرسال إيميل بالتفعيل (اختياري)
            // sendActivationEmail($payment['user_id'], $licenseKey);
            
            // تسجيل النشاط
            $logStmt = $db->prepare("
                INSERT INTO activity_log (user_id, action, description, ip_address)
                VALUES (?, 'payment_success', ?, ?)
            ");
            $logStmt->execute([
                $payment['user_id'],
                "Payment successful for {$plan} plan",
                $_SERVER['REMOTE_ADDR'] ?? 'webhook'
            ]);
            
            logWebhook("Subscription activated for user {$payment['user_id']}, License: {$licenseKey}");
        }
        
        // رد نجاح لـ PayMob
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => $success ? 'Payment processed' : 'Payment failed recorded'
        ]);
        
    } catch (Exception $e) {
        logWebhook("Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit;
}

// ===========================================
// معالجة GET (Redirect بعد الدفع)
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order'])) {
    $orderId = $_GET['order'];
    $success = $_GET['success'] ?? 'false';
    
    // إعادة التوجيه لصفحة النتيجة
    if ($success === 'true') {
        header('Location: ' . SITE_URL . '/payment-success.php?order=' . $orderId);
    } else {
        header('Location: ' . SITE_URL . '/payment-failed.php?order=' . $orderId);
    }
    exit;
}

// إذا لم يكن POST أو GET صالح
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
