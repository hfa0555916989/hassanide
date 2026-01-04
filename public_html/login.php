<?php
/**
 * Hassan IDE - Login Page
 */
$pageTitle = 'تسجيل الدخول';

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';

$auth = new Auth();

// If already logged in, redirect
if ($auth->isLoggedIn()) {
    header('Location: ' . SITE_URL . '/account.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } else {
        $result = $auth->login($email, $password, $remember);
        
        if ($result['success']) {
            if ($redirect) {
                header('Location: ' . SITE_URL . '/' . $redirect);
            } else {
                header('Location: ' . SITE_URL . '/account.php');
            }
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="logo">
            <i class="fas fa-code"></i>
        </div>
        <h1>تسجيل الدخول</h1>
        <p class="subtitle">مرحباً بعودتك</p>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="position: relative; top: 0; margin-bottom: 20px; border-radius: var(--radius);">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" data-validate>
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
                    <button type="button" class="toggle-password" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 400;">
                    <input type="checkbox" name="remember">
                    <span>تذكرني</span>
                </label>
                <a href="<?= SITE_URL ?>/forgot-password.php" style="color: var(--primary); font-size: 0.9rem;">نسيت كلمة المرور؟</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                تسجيل الدخول
            </button>
        </form>
        
        <p class="auth-footer">
            ليس لديك حساب؟ <a href="<?= SITE_URL ?>/register.php">إنشاء حساب جديد</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
