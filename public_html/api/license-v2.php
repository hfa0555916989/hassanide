<?php
/**
 * Hassan IDE - License API v2
 * ============================
 * نظام الترخيص المتقدم مع دعم متعدد الأجهزة
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/config.php';

// CORS Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * مدير التراخيص المتقدم
 */
class LicenseManagerV2 {
    
    private $db;
    private $secret;
    private $maxDevices = [
        'starter' => 1,
        'pro' => 3,
        'teams' => 10
    ];
    
    public function __construct() {
        $this->db = getDB();
        $this->secret = LICENSE_SECRET;
    }
    
    /**
     * إنشاء JWT Token للرخصة
     */
    private function createToken($payload, $expiresAt = null) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $payload['iat'] = time();
        if ($expiresAt) {
            $payload['exp'] = strtotime($expiresAt);
        }
        
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payloadEncoded", $this->secret, true);
        $signatureEncoded = base64_encode($signature);
        
        return "$header.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * التحقق من JWT Token
     */
    private function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        $expectedSignature = base64_encode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        $payloadData = json_decode(base64_decode($payload), true);
        
        // التحقق من انتهاء الصلاحية
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }
    
    /**
     * إنشاء License Key جديد
     */
    public function generateLicense($userId, $subscriptionId, $plan) {
        // جلب معلومات الاشتراك
        $stmt = $this->db->prepare("
            SELECT s.*, u.email, u.name 
            FROM subscriptions s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();
        
        if (!$subscription) {
            throw new Exception('Subscription not found');
        }
        
        // إنشاء payload للـ Token
        $payload = [
            'uid' => $userId,
            'sid' => $subscriptionId,
            'plan' => $plan,
            'features' => $this->getPlanFeatures($plan),
            'email' => $subscription['email']
        ];
        
        // إنشاء Token
        $token = $this->createToken($payload, $subscription['expires_at']);
        
        // إنشاء مفتاح منسق
        $hash = strtoupper(substr(hash('sha256', $token), 0, 16));
        $parts = str_split($hash, 4);
        $prefix = strtoupper(substr($plan, 0, 4));
        $licenseKey = "{$prefix}-{$parts[0]}-{$parts[1]}-{$parts[2]}-{$parts[3]}";
        
        // حفظ في قاعدة البيانات
        $stmt = $this->db->prepare("
            INSERT INTO licenses (user_id, subscription_id, license_key, token, devices, created_at)
            VALUES (?, ?, ?, ?, '[]', NOW())
            ON DUPLICATE KEY UPDATE
                license_key = VALUES(license_key),
                token = VALUES(token),
                is_active = 1,
                updated_at = NOW()
        ");
        $stmt->execute([$userId, $subscriptionId, $licenseKey, $token]);
        
        return [
            'license_key' => $licenseKey,
            'token' => $token,
            'plan' => $plan,
            'features' => $payload['features'],
            'expires_at' => $subscription['expires_at']
        ];
    }
    
    /**
     * التحقق من صلاحية License مع دعم الأجهزة
     */
    public function validateLicense($licenseKey, $machineId = null, $machineName = null) {
        // البحث عن الترخيص
        $stmt = $this->db->prepare("
            SELECT l.*, s.plan, s.status as sub_status, s.expires_at, 
                   u.email, u.name, l.devices
            FROM licenses l
            JOIN subscriptions s ON l.subscription_id = s.id
            JOIN users u ON l.user_id = u.id
            WHERE l.license_key = ?
        ");
        $stmt->execute([$licenseKey]);
        $license = $stmt->fetch();
        
        if (!$license) {
            return $this->errorResponse('LICENSE_NOT_FOUND', 'License key not found');
        }
        
        // التحقق من أن الترخيص نشط
        if (!$license['is_active']) {
            return $this->errorResponse('LICENSE_INACTIVE', 'License has been deactivated');
        }
        
        // التحقق من حالة الاشتراك
        if (in_array($license['sub_status'], ['expired', 'cancelled'])) {
            return $this->errorResponse('SUBSCRIPTION_EXPIRED', 'Subscription has expired', [
                'expired_at' => $license['expires_at']
            ]);
        }
        
        // التحقق من تاريخ الانتهاء
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            $this->db->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?")
                     ->execute([$license['subscription_id']]);
            
            return $this->errorResponse('SUBSCRIPTION_EXPIRED', 'Subscription has expired');
        }
        
        // إدارة الأجهزة
        $devices = json_decode($license['devices'] ?: '[]', true);
        $maxDevices = $this->maxDevices[$license['plan']] ?? 1;
        
        if ($machineId) {
            $deviceExists = false;
            foreach ($devices as &$device) {
                if ($device['machine_id'] === $machineId) {
                    $device['last_seen'] = date('Y-m-d H:i:s');
                    $device['machine_name'] = $machineName ?: $device['machine_name'];
                    $deviceExists = true;
                    break;
                }
            }
            
            if (!$deviceExists) {
                // التحقق من عدد الأجهزة
                if (count($devices) >= $maxDevices) {
                    return $this->errorResponse('MAX_DEVICES_REACHED', 
                        "Maximum devices ({$maxDevices}) reached. Please deactivate another device.", [
                            'max_devices' => $maxDevices,
                            'current_devices' => count($devices)
                        ]);
                }
                
                // إضافة جهاز جديد
                $devices[] = [
                    'machine_id' => $machineId,
                    'machine_name' => $machineName ?: 'Unknown Device',
                    'added_at' => date('Y-m-d H:i:s'),
                    'last_seen' => date('Y-m-d H:i:s')
                ];
            }
            
            // تحديث الأجهزة
            $this->db->prepare("UPDATE licenses SET devices = ?, updated_at = NOW() WHERE id = ?")
                     ->execute([json_encode($devices), $license['id']]);
        }
        
        // تحديث آخر تحقق
        $this->db->prepare("UPDATE licenses SET last_validated = NOW() WHERE id = ?")
                 ->execute([$license['id']]);
        
        // حساب الأيام المتبقية
        $daysRemaining = null;
        if ($license['expires_at']) {
            $daysRemaining = max(0, ceil((strtotime($license['expires_at']) - time()) / 86400));
        }
        
        return [
            'valid' => true,
            'license_key' => $licenseKey,
            'token' => $license['token'] ?? null,
            'plan' => $license['plan'],
            'plan_name' => $this->getPlanName($license['plan']),
            'status' => $license['sub_status'],
            'expires_at' => $license['expires_at'],
            'days_remaining' => $daysRemaining,
            'features' => $this->getPlanFeatures($license['plan']),
            'max_devices' => $maxDevices,
            'active_devices' => count($devices),
            'devices' => $devices,
            'user' => [
                'email' => $license['email'],
                'name' => $license['name']
            ],
            'offline_grace_days' => 7
        ];
    }
    
    /**
     * إزالة جهاز من الرخصة
     */
    public function removeDevice($licenseKey, $machineId) {
        $stmt = $this->db->prepare("SELECT id, devices FROM licenses WHERE license_key = ?");
        $stmt->execute([$licenseKey]);
        $license = $stmt->fetch();
        
        if (!$license) {
            return $this->errorResponse('LICENSE_NOT_FOUND', 'License not found');
        }
        
        $devices = json_decode($license['devices'] ?: '[]', true);
        $devices = array_filter($devices, fn($d) => $d['machine_id'] !== $machineId);
        $devices = array_values($devices);
        
        $this->db->prepare("UPDATE licenses SET devices = ?, updated_at = NOW() WHERE id = ?")
                 ->execute([json_encode($devices), $license['id']]);
        
        return ['success' => true, 'devices' => $devices];
    }
    
    /**
     * الحصول على مميزات الباقة
     */
    public function getPlanFeatures($plan) {
        $features = [
            'starter' => [
                'basic_editor',
                'syntax_highlighting',
                'file_explorer',
                'terminal',
                'git_basic'
            ],
            'pro' => [
                'basic_editor',
                'syntax_highlighting', 
                'file_explorer',
                'terminal',
                'git_basic',
                'ai_assistant',
                'advanced_debugging',
                'templates',
                'cloud_sync',
                'extensions_unlimited',
                'hassan_panel',
                'code_snippets',
                'multi_cursor'
            ],
            'teams' => [
                'basic_editor',
                'syntax_highlighting',
                'file_explorer', 
                'terminal',
                'git_basic',
                'ai_assistant',
                'advanced_debugging',
                'templates',
                'cloud_sync',
                'extensions_unlimited',
                'hassan_panel',
                'code_snippets',
                'multi_cursor',
                'team_collaboration',
                'shared_workspaces',
                'team_analytics',
                'admin_dashboard',
                'sso_integration',
                'priority_support',
                'code_review'
            ]
        ];
        
        return $features[$plan] ?? $features['starter'];
    }
    
    /**
     * اسم الباقة
     */
    private function getPlanName($plan) {
        $names = [
            'starter' => 'Starter (مجاني)',
            'pro' => 'Pro (احترافي)',
            'teams' => 'Teams (فرق العمل)'
        ];
        return $names[$plan] ?? $plan;
    }
    
    /**
     * رد الخطأ
     */
    private function errorResponse($code, $message, $extra = []) {
        return array_merge([
            'valid' => false,
            'error' => $code,
            'message' => $message
        ], $extra);
    }
    
    /**
     * تراخيص المستخدم
     */
    public function getUserLicenses($userId) {
        $stmt = $this->db->prepare("
            SELECT l.*, s.plan, s.status as sub_status, s.expires_at
            FROM licenses l
            JOIN subscriptions s ON l.subscription_id = s.id
            WHERE l.user_id = ? AND l.is_active = 1
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$userId]);
        $licenses = $stmt->fetchAll();
        
        foreach ($licenses as &$license) {
            $license['features'] = $this->getPlanFeatures($license['plan']);
            $license['devices'] = json_decode($license['devices'] ?: '[]', true);
        }
        
        return $licenses;
    }
}

// ===========================================
// Router للـ API
// ===========================================
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

$manager = new LicenseManagerV2();

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $action = $input['action'] ?? $path;
            
            switch ($action) {
                case 'validate':
                    $result = $manager->validateLicense(
                        $input['license_key'] ?? '',
                        $input['machine_id'] ?? null,
                        $input['machine_name'] ?? null
                    );
                    break;
                    
                case 'activate':
                    // نفس validate لكن يسجل الجهاز
                    $result = $manager->validateLicense(
                        $input['license_key'] ?? '',
                        $input['machine_id'] ?? '',
                        $input['machine_name'] ?? 'HassanIDE Desktop'
                    );
                    break;
                    
                case 'remove_device':
                    $result = $manager->removeDevice(
                        $input['license_key'] ?? '',
                        $input['machine_id'] ?? ''
                    );
                    break;
                    
                case 'check_feature':
                    $validation = $manager->validateLicense($input['license_key'] ?? '');
                    if ($validation['valid']) {
                        $feature = $input['feature'] ?? '';
                        $hasFeature = in_array($feature, $validation['features']);
                        $result = [
                            'has_feature' => $hasFeature,
                            'feature' => $feature,
                            'plan' => $validation['plan'],
                            'required_plan' => $hasFeature ? null : $manager->getRequiredPlan($feature)
                        ];
                    } else {
                        $result = $validation;
                    }
                    break;
                    
                default:
                    $result = ['error' => 'INVALID_ACTION', 'message' => 'Invalid action'];
            }
            break;
            
        case 'GET':
            if ($path === 'status' || empty($path)) {
                $result = [
                    'service' => 'Hassan IDE License Server',
                    'version' => '2.0',
                    'status' => 'online',
                    'timestamp' => date('c')
                ];
            } else {
                $result = ['error' => 'INVALID_ENDPOINT'];
            }
            break;
            
        default:
            http_response_code(405);
            $result = ['error' => 'METHOD_NOT_ALLOWED'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $result = [
        'error' => 'SERVER_ERROR',
        'message' => isProduction() ? 'An error occurred' : $e->getMessage()
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
