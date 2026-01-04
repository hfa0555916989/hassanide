<?php
/**
 * Hassan IDE - License Manager
 * =============================
 * إدارة وإنشاء والتحقق من التراخيص
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/config.php';

class LicenseManager {
    
    private $db;
    private $secret;
    
    public function __construct() {
        $this->db = getDB();
        $this->secret = LICENSE_SECRET;
    }
    
    /**
     * إنشاء License Key جديد
     */
    public function generateLicense($userId, $subscriptionId) {
        // إنشاء مفتاح فريد
        $uniqueId = bin2hex(random_bytes(16));
        $timestamp = time();
        $data = "{$userId}-{$subscriptionId}-{$timestamp}";
        $signature = hash_hmac('sha256', $data, $this->secret);
        
        // تنسيق المفتاح: HASS-XXXX-XXXX-XXXX-XXXX
        $licenseKey = 'HASS-' . strtoupper(substr($uniqueId, 0, 4)) . '-' 
                    . strtoupper(substr($uniqueId, 4, 4)) . '-' 
                    . strtoupper(substr($uniqueId, 8, 4)) . '-' 
                    . strtoupper(substr($signature, 0, 4));
        
        // حفظ في قاعدة البيانات
        $stmt = $this->db->prepare("
            INSERT INTO licenses (user_id, subscription_id, license_key, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                license_key = VALUES(license_key),
                is_active = 1,
                updated_at = NOW()
        ");
        $stmt->execute([$userId, $subscriptionId, $licenseKey]);
        
        return $licenseKey;
    }
    
    /**
     * التحقق من صلاحية License
     */
    public function validateLicense($licenseKey, $machineId = null) {
        // البحث عن الترخيص
        $stmt = $this->db->prepare("
            SELECT l.*, s.plan, s.status as sub_status, s.expires_at, u.email, u.name
            FROM licenses l
            JOIN subscriptions s ON l.subscription_id = s.id
            JOIN users u ON l.user_id = u.id
            WHERE l.license_key = ?
        ");
        $stmt->execute([$licenseKey]);
        $license = $stmt->fetch();
        
        if (!$license) {
            return [
                'valid' => false,
                'error' => 'LICENSE_NOT_FOUND',
                'message' => 'License key not found'
            ];
        }
        
        // التحقق من أن الترخيص نشط
        if (!$license['is_active']) {
            return [
                'valid' => false,
                'error' => 'LICENSE_INACTIVE',
                'message' => 'License has been deactivated'
            ];
        }
        
        // التحقق من حالة الاشتراك
        if ($license['sub_status'] === 'expired' || $license['sub_status'] === 'cancelled') {
            return [
                'valid' => false,
                'error' => 'SUBSCRIPTION_EXPIRED',
                'message' => 'Subscription has expired',
                'expired_at' => $license['expires_at']
            ];
        }
        
        // التحقق من تاريخ الانتهاء
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            // تحديث حالة الاشتراك
            $updateStmt = $this->db->prepare("
                UPDATE subscriptions SET status = 'expired' WHERE id = ?
            ");
            $updateStmt->execute([$license['subscription_id']]);
            
            return [
                'valid' => false,
                'error' => 'SUBSCRIPTION_EXPIRED',
                'message' => 'Subscription has expired',
                'expired_at' => $license['expires_at']
            ];
        }
        
        // التحقق من Machine ID إذا موجود
        if ($machineId && $license['machine_id'] && $license['machine_id'] !== $machineId) {
            return [
                'valid' => false,
                'error' => 'MACHINE_MISMATCH',
                'message' => 'License is registered to another device'
            ];
        }
        
        // تحديث Machine ID إذا كان جديد
        if ($machineId && !$license['machine_id']) {
            $activateStmt = $this->db->prepare("
                UPDATE licenses SET machine_id = ?, activated_at = NOW() WHERE id = ?
            ");
            $activateStmt->execute([$machineId, $license['id']]);
        }
        
        // تحديث آخر تحقق
        $lastValidateStmt = $this->db->prepare("
            UPDATE licenses SET last_validated = NOW() WHERE id = ?
        ");
        $lastValidateStmt->execute([$license['id']]);
        
        // حساب الأيام المتبقية
        $daysRemaining = null;
        if ($license['expires_at']) {
            $daysRemaining = max(0, ceil((strtotime($license['expires_at']) - time()) / 86400));
        }
        
        // جلب مميزات الباقة
        $plan = getPlan($license['plan']);
        
        return [
            'valid' => true,
            'license_key' => $licenseKey,
            'plan' => $license['plan'],
            'plan_name' => $plan['name'] ?? $license['plan'],
            'status' => $license['sub_status'],
            'expires_at' => $license['expires_at'],
            'days_remaining' => $daysRemaining,
            'features' => $plan['features'] ?? [],
            'user' => [
                'email' => $license['email'],
                'name' => $license['name']
            ]
        ];
    }
    
    /**
     * إلغاء تفعيل License
     */
    public function deactivateLicense($licenseKey) {
        $stmt = $this->db->prepare("
            UPDATE licenses SET is_active = 0, machine_id = NULL WHERE license_key = ?
        ");
        return $stmt->execute([$licenseKey]);
    }
    
    /**
     * إعادة تعيين Machine ID
     */
    public function resetMachine($licenseKey) {
        $stmt = $this->db->prepare("
            UPDATE licenses SET machine_id = NULL WHERE license_key = ?
        ");
        return $stmt->execute([$licenseKey]);
    }
    
    /**
     * الحصول على جميع تراخيص المستخدم
     */
    public function getUserLicenses($userId) {
        $stmt = $this->db->prepare("
            SELECT l.*, s.plan, s.status as sub_status, s.expires_at
            FROM licenses l
            JOIN subscriptions s ON l.subscription_id = s.id
            WHERE l.user_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}

// ===========================================
// API Endpoint للتحقق من التراخيص
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    $licenseManager = new LicenseManager();
    
    switch ($action) {
        case 'validate':
            $licenseKey = $input['license_key'] ?? '';
            $machineId = $input['machine_id'] ?? null;
            
            if (empty($licenseKey)) {
                echo json_encode(['valid' => false, 'error' => 'MISSING_LICENSE']);
                break;
            }
            
            $result = $licenseManager->validateLicense($licenseKey, $machineId);
            echo json_encode($result);
            break;
            
        case 'deactivate':
            $licenseKey = $input['license_key'] ?? '';
            $success = $licenseManager->deactivateLicense($licenseKey);
            echo json_encode(['success' => $success]);
            break;
            
        case 'reset_machine':
            $licenseKey = $input['license_key'] ?? '';
            $success = $licenseManager->resetMachine($licenseKey);
            echo json_encode(['success' => $success]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// للطلبات GET - عرض معلومات بسيطة
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode([
        'service' => 'Hassan IDE License Server',
        'version' => '1.0',
        'status' => 'online'
    ]);
    exit;
}
