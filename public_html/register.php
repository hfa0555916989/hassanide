<?php
/**
 * Hassan IDE - Register Page
 */
$pageTitle = 'إنشاء حساب';

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';

$auth = new Auth();

// If already logged in, redirect
if ($auth->isLoggedIn()) {
    header('Location: ' . SITE_URL . '/account.php');
    exit;
}

$error = '';
$selectedPlan = $_GET['plan'] ?? 'starter';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $plan = $_POST['plan'] ?? 'starter';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif ($password !== $confirmPassword) {
        $error = 'كلمة المرور غير متطابقة';
    } elseif (strlen($password) < 8) {
        $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } else {
        $result = $auth->register($name, $email, $password);
        
        if ($result['success']) {
            // Auto login
            $auth->login($email, $password);
            
            // Redirect based on plan
            if ($plan !== 'starter') {
                header('Location: ' . SITE_URL . '/checkout.php?plan=' . $plan);
            } else {
                header('Location: ' . SITE_URL . '/account.php?welcome=1');
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
        <h1>إنشاء حساب جديد</h1>
        <p class="subtitle">انضم إلى مجتمع Hassan IDE</p>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="position: relative; top: 0; margin-bottom: 20px; border-radius: var(--radius);">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($selectedPlan !== 'starter'): ?>
            <div style="background: rgba(79,70,229,0.1); padding: 15px; border-radius: var(--radius); margin-bottom: 20px; text-align: center;">
                <i class="fas fa-gift" style="color: var(--primary);"></i>
                <span>سجّل الآن واحصل على <strong>7 أيام مجاناً</strong> من باقة <?= $selectedPlan === 'pro' ? 'Pro' : 'Teams' ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" data-validate>
            <input type="hidden" name="plan" value="<?= htmlspecialchars($selectedPlan) ?>">
            
            <div class="form-group">
                <label for="name">الاسم</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="أدخل اسمك" required>
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="8 أحرف على الأقل" required minlength="8">
                    <button type="button" class="toggle-password" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-400);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="أعد كتابة كلمة المرور" required>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="terms" required style="margin-top: 5px;">
                    <span style="font-weight: 400; color: var(--gray-500);">
                        أوافق على <a href="<?= SITE_URL ?>/terms.php" style="color: var(--primary);">شروط الاستخدام</a> و<a href="<?= SITE_URL ?>/privacy.php" style="color: var(--primary);">سياسة الخصوصية</a>
                    </span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">
                إنشاء الحساب
            </button>
        </form>
        
        <p class="auth-footer">
            لديك حساب؟ <a href="<?= SITE_URL ?>/login.php">تسجيل الدخول</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
