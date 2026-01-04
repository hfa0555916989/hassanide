<?php
/**
 * Hassan IDE - Payment Success Page
 */
$pageTitle = 'تم الدفع بنجاح';

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/license.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();

$orderId = $_GET['order'] ?? '';

// Get license if exists
$license = null;
if ($currentUser) {
    $licenseManager = new LicenseManager();
    $licenses = $licenseManager->getUserLicenses($currentUser['id']);
    if (!empty($licenses)) {
        $license = $licenses[0];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 120px; min-height: 70vh;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div style="width: 100px; height: 100px; background: #D1FAE5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                <i class="fas fa-check" style="font-size: 3rem; color: #065F46;"></i>
            </div>
            
            <h1 style="font-size: 2.5rem; color: var(--gray-900); margin-bottom: 15px;">تم الدفع بنجاح! 🎉</h1>
            <p style="font-size: 1.1rem; color: var(--gray-500); margin-bottom: 30px;">
                شكراً لاشتراكك في Hassan IDE. تم تفعيل حسابك بنجاح.
            </p>
            
            <?php if ($license): ?>
                <div class="card" style="text-align: right; margin-bottom: 30px;">
                    <div class="card-body">
                        <h3 style="margin-bottom: 15px;">مفتاح الترخيص الخاص بك</h3>
                        <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                            <code style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($license['license_key']) ?></code>
                            <button onclick="copyToClipboard('<?= $license['license_key'] ?>', this)" class="btn btn-primary">
                                <i class="fas fa-copy"></i>
                                نسخ
                            </button>
                        </div>
                        <p style="color: var(--gray-500); font-size: 0.9rem; margin-top: 15px;">
                            <i class="fas fa-info-circle"></i>
                            احتفظ بهذا المفتاح. ستحتاجه لتفعيل البرنامج.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card" style="text-align: right; margin-bottom: 30px;">
                <div class="card-body">
                    <h3 style="margin-bottom: 15px;">الخطوات التالية</h3>
                    <ol style="color: var(--gray-600); padding-right: 20px; text-align: right;">
                        <li style="margin-bottom: 10px;">
                            <strong>حمّل Hassan IDE</strong> - إذا لم تكن قد حملته بعد
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>افتح البرنامج</strong> واذهب إلى Hassan Panel
                        </li>
                        <li style="margin-bottom: 10px;">
                            <strong>أدخل مفتاح الترخيص</strong> لتفعيل كل المميزات
                        </li>
                        <li>
                            <strong>ابدأ البرمجة!</strong> 🚀
                        </li>
                    </ol>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= SITE_URL ?>/download.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-download"></i>
                    تحميل البرنامج
                </a>
                <a href="<?= SITE_URL ?>/account.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-user"></i>
                    الذهاب لحسابي
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
