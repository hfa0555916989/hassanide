<?php
/**
 * Hassan IDE - Account Dashboard
 */
$pageTitle = 'حسابي';

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/license.php';

$auth = new Auth();

// Must be logged in
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$licenseManager = new LicenseManager();
$licenses = $licenseManager->getUserLicenses($currentUser['id']);

// Get subscription info
$db = getDB();
$stmt = $db->prepare("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$currentUser['id']]);
$subscription = $stmt->fetch();

$plan = getPlan($subscription['plan'] ?? 'starter');
$daysRemaining = $subscription['expires_at'] ? daysRemaining($subscription['expires_at']) : null;

// Welcome message for new users
$isWelcome = isset($_GET['welcome']);

require_once __DIR__ . '/includes/header.php';
?>

<section class="dashboard">
    <div class="container">
        <?php if ($isWelcome): ?>
            <div class="flash-message flash-success" style="position: relative; top: 0; margin-bottom: 30px; border-radius: var(--radius);">
                <div class="container">
                    <i class="fas fa-check-circle"></i>
                    <span>مرحباً بك في Hassan IDE! حسابك جاهز للاستخدام.</span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <!-- Sidebar -->
            <aside class="dashboard-sidebar">
                <div class="user-info">
                    <div class="avatar">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </div>
                    <div class="user-name"><?= htmlspecialchars($currentUser['name']) ?></div>
                    <div class="user-plan"><?= $plan['name_ar'] ?? 'المبتدئ' ?></div>
                </div>
                
                <nav class="sidebar-nav">
                    <a href="<?= SITE_URL ?>/account.php" class="active">
                        <i class="fas fa-home"></i>
                        لوحة التحكم
                    </a>
                    <a href="<?= SITE_URL ?>/account.php?tab=license">
                        <i class="fas fa-key"></i>
                        التراخيص
                    </a>
                    <a href="<?= SITE_URL ?>/account.php?tab=billing">
                        <i class="fas fa-credit-card"></i>
                        الفواتير
                    </a>
                    <a href="<?= SITE_URL ?>/account.php?tab=settings">
                        <i class="fas fa-cog"></i>
                        الإعدادات
                    </a>
                    <a href="<?= SITE_URL ?>/download.php">
                        <i class="fas fa-download"></i>
                        تحميل البرنامج
                    </a>
                </nav>
            </aside>
            
            <!-- Main Content -->
            <div class="dashboard-content">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="value"><?= $plan['name_ar'] ?? 'المبتدئ' ?></div>
                        <div class="label">الباقة الحالية</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="icon" style="background: <?= $subscription['status'] === 'active' ? 'var(--success)' : 'var(--warning)' ?>;">
                            <i class="fas fa-<?= $subscription['status'] === 'active' ? 'check' : 'clock' ?>"></i>
                        </div>
                        <div class="value"><?= $subscription['status'] === 'active' ? 'نشط' : ($subscription['status'] === 'trial' ? 'تجريبي' : 'منتهي') ?></div>
                        <div class="label">حالة الاشتراك</div>
                    </div>
                    
                    <?php if ($daysRemaining !== null): ?>
                    <div class="stat-card">
                        <div class="icon" style="background: <?= $daysRemaining > 7 ? 'var(--primary)' : 'var(--danger)' ?>;">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="value"><?= $daysRemaining ?></div>
                        <div class="label">يوم متبقي</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="value"><?= count($licenses) ?></div>
                        <div class="label">التراخيص</div>
                    </div>
                </div>
                
                <!-- License Section -->
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>مفتاح الترخيص</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($licenses)): ?>
                            <?php $license = $licenses[0]; ?>
                            <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <div style="font-family: monospace; font-size: 1.2rem; font-weight: 700; color: var(--gray-900);">
                                        <?= htmlspecialchars($license['license_key']) ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-500); margin-top: 5px;">
                                        أنشئ في: <?= formatDateAr($license['created_at']) ?>
                                    </div>
                                </div>
                                <button onclick="copyToClipboard('<?= $license['license_key'] ?>', this)" class="btn btn-outline">
                                    <i class="fas fa-copy"></i>
                                    نسخ
                                </button>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <h4 style="margin-bottom: 10px;">كيفية التفعيل:</h4>
                                <ol style="color: var(--gray-600); padding-right: 20px;">
                                    <li>افتح Hassan IDE</li>
                                    <li>اذهب إلى Hassan Panel من الشريط الجانبي</li>
                                    <li>اضغط على "تفعيل الترخيص"</li>
                                    <li>الصق مفتاح الترخيص أعلاه</li>
                                </ol>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px;">
                                <i class="fas fa-key" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 15px;"></i>
                                <p style="color: var(--gray-500); margin-bottom: 20px;">لا يوجد ترخيص نشط</p>
                                <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-primary">
                                    ترقية الباقة
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3>إجراءات سريعة</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <a href="<?= SITE_URL ?>/download.php" class="btn btn-outline" style="justify-content: flex-start;">
                                <i class="fas fa-download"></i>
                                تحميل البرنامج
                            </a>
                            
                            <?php if ($subscription['plan'] === 'starter'): ?>
                                <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-primary" style="justify-content: flex-start;">
                                    <i class="fas fa-arrow-up"></i>
                                    ترقية الباقة
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?= SITE_URL ?>/faq.php" class="btn btn-outline" style="justify-content: flex-start;">
                                <i class="fas fa-question-circle"></i>
                                الدعم والمساعدة
                            </a>
                            
                            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline" style="justify-content: flex-start; color: var(--danger); border-color: var(--danger);">
                                <i class="fas fa-sign-out-alt"></i>
                                تسجيل الخروج
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
