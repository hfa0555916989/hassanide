<?php
/**
 * Hassan IDE - Authentication System
 * ===================================
 * تسجيل الدخول والخروج وإدارة الجلسات
 */

define('HASSAN_IDE', true);
require_once __DIR__ . '/config.php';

class Auth {
    
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        
        // بدء الجلسة إذا لم تكن مبدوءة
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => isProduction(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
    
    /**
     * تسجيل مستخدم جديد
     */
    public function register($name, $email, $password, $phone = null) {
        // التحقق من البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'البريد الإلكتروني غير صالح'];
        }
        
        // التحقق من قوة كلمة المرور
        if (strlen($password) < 8) {
            return ['success' => false, 'error' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'];
        }
        
        // التحقق من عدم وجود المستخدم
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'البريد الإلكتروني مسجل مسبقاً'];
        }
        
        // تشفير كلمة المرور
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // إنشاء رمز التحقق
        $verificationToken = bin2hex(random_bytes(32));
        
        // إدراج المستخدم
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, phone, verification_token, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $verificationToken]);
        $userId = $this->db->lastInsertId();
        
        // إنشاء اشتراك Starter مجاني
        $stmt = $this->db->prepare("
            INSERT INTO subscriptions (user_id, plan, status, starts_at)
            VALUES (?, 'starter', 'active', NOW())
        ");
        $stmt->execute([$userId]);
        
        // تسجيل النشاط
        $this->logActivity($userId, 'register', 'New user registered');
        
        // إرسال بريد التحقق (اختياري)
        // $this->sendVerificationEmail($email, $verificationToken);
        
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'تم إنشاء الحساب بنجاح'
        ];
    }
    
    /**
     * تسجيل الدخول
     */
    public function login($email, $password, $remember = false) {
        $stmt = $this->db->prepare("
            SELECT u.*, s.plan, s.status as sub_status, s.expires_at
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id
            WHERE u.email = ?
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'البريد الإلكتروني غير مسجل'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'كلمة المرور غير صحيحة'];
        }
        
        // تحديث الجلسة
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_plan'] = $user['plan'] ?? 'starter';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // تسجيل النشاط
        $this->logActivity($user['id'], 'login', 'User logged in');
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'plan' => $user['plan'] ?? 'starter'
            ]
        ];
    }
    
    /**
     * تسجيل الخروج
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * التحقق من تسجيل الدخول
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * الحصول على المستخدم الحالي
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("
            SELECT u.*, s.plan, s.status as sub_status, s.expires_at, s.trial_ends_at
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id
            WHERE u.id = ?
            ORDER BY s.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    /**
     * تحديث كلمة المرور
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'كلمة المرور الحالية غير صحيحة'];
        }
        
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        $this->logActivity($userId, 'password_change', 'Password changed');
        
        return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];
    }
    
    /**
     * طلب استعادة كلمة المرور
     */
    public function requestPasswordReset($email) {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // لا نكشف أن البريد غير موجود لأسباب أمنية
            return ['success' => true, 'message' => 'إذا كان البريد مسجلاً، ستصلك رسالة'];
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->db->prepare("
            UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?
        ");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // إرسال البريد الإلكتروني
        // $this->sendPasswordResetEmail($email, $token);
        
        return ['success' => true, 'message' => 'تم إرسال رابط الاستعادة'];
    }
    
    /**
     * تسجيل النشاط
     */
    private function logActivity($userId, $action, $description) {
        $stmt = $this->db->prepare("
            INSERT INTO activity_log (user_id, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}

// ===========================================
// API Endpoints
// ===========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // إذا كانت البيانات من form
    if (empty($input)) {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? '';
    $auth = new Auth();
    
    switch ($action) {
        case 'register':
            $result = $auth->register(
                $input['name'] ?? '',
                $input['email'] ?? '',
                $input['password'] ?? '',
                $input['phone'] ?? null
            );
            echo json_encode($result);
            break;
            
        case 'login':
            $result = $auth->login(
                $input['email'] ?? '',
                $input['password'] ?? '',
                $input['remember'] ?? false
            );
            echo json_encode($result);
            break;
            
        case 'logout':
            $result = $auth->logout();
            echo json_encode($result);
            break;
            
        case 'check':
            echo json_encode([
                'logged_in' => $auth->isLoggedIn(),
                'user' => $auth->isLoggedIn() ? $auth->getCurrentUser() : null
            ]);
            break;
            
        case 'change_password':
            if (!$auth->isLoggedIn()) {
                echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول أولاً']);
                break;
            }
            $result = $auth->changePassword(
                $_SESSION['user_id'],
                $input['current_password'] ?? '',
                $input['new_password'] ?? ''
            );
            echo json_encode($result);
            break;
            
        case 'forgot_password':
            $result = $auth->requestPasswordReset($input['email'] ?? '');
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
