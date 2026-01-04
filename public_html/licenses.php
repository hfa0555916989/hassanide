<?php
/**
 * Hassan IDE - License Dashboard
 * ================================
 * صفحة إدارة التراخيص للمستخدم
 */

$pageTitle = 'إدارة التراخيص';
require_once __DIR__ . '/includes/header.php';

// التحقق من تسجيل الدخول
if (!$currentUser) {
    header('Location: ' . SITE_URL . '/login.php?redirect=licenses');
    exit;
}

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/config.php';

// جلب تراخيص المستخدم
$db = getDB();
$stmt = $db->prepare("
    SELECT l.*, s.plan, s.status as sub_status, s.expires_at, s.billing_cycle
    FROM licenses l
    JOIN subscriptions s ON l.subscription_id = s.id
    WHERE l.user_id = ? AND l.is_active = 1
    ORDER BY l.created_at DESC
");
$stmt->execute([$currentUser['id']]);
$licenses = $stmt->fetchAll();

// جلب الاشتراك النشط
$stmt = $db->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? AND status IN ('active', 'trial')
    ORDER BY created_at DESC LIMIT 1
");
$stmt->execute([$currentUser['id']]);
$subscription = $stmt->fetch();

$planNames = [
    'starter' => 'Starter',
    'pro' => 'Pro',
    'teams' => 'Teams'
];

$statusNames = [
    'active' => ['text' => 'نشط', 'class' => 'success'],
    'trial' => ['text' => 'تجريبي', 'class' => 'warning'],
    'expired' => ['text' => 'منتهي', 'class' => 'danger'],
    'cancelled' => ['text' => 'ملغي', 'class' => 'secondary']
];
?>

<section class="section" style="padding-top: 120px;">
    <div class="container">
        <div class="section-title">
            <h2>إدارة التراخيص</h2>
            <p>عرض وإدارة تراخيص HassanIDE الخاصة بك</p>
        </div>

        <!-- معلومات الاشتراك -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h3 style="margin: 0;">الاشتراك الحالي</h3>
            </div>
            <div class="card-body">
                <?php if ($subscription): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <label style="color: var(--gray-500); font-size: 0.875rem;">الباقة</label>
                            <p style="font-size: 1.25rem; font-weight: 600; margin: 5px 0;">
                                <?= $planNames[$subscription['plan']] ?? $subscription['plan'] ?>
                            </p>
                        </div>
                        <div>
                            <label style="color: var(--gray-500); font-size: 0.875rem;">الحالة</label>
                            <p style="margin: 5px 0;">
                                <span class="badge badge-<?= $statusNames[$subscription['status']]['class'] ?? 'secondary' ?>">
                                    <?= $statusNames[$subscription['status']]['text'] ?? $subscription['status'] ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <label style="color: var(--gray-500); font-size: 0.875rem;">تاريخ الانتهاء</label>
                            <p style="font-size: 1.1rem; margin: 5px 0;">
                                <?= $subscription['expires_at'] ? date('Y/m/d', strtotime($subscription['expires_at'])) : 'غير محدد' ?>
                            </p>
                        </div>
                        <div>
                            <label style="color: var(--gray-500); font-size: 0.875rem;">دورة الفوترة</label>
                            <p style="margin: 5px 0;">
                                <?= $subscription['billing_cycle'] === 'yearly' ? 'سنوي' : 'شهري' ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($subscription['status'] === 'active' || $subscription['status'] === 'trial'): ?>
                        <div style="margin-top: 20px; display: flex; gap: 10px;">
                            <?php if ($subscription['plan'] !== 'teams'): ?>
                                <a href="<?= SITE_URL ?>/checkout.php?plan=<?= $subscription['plan'] === 'starter' ? 'pro' : 'teams' ?>&upgrade=1" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-up"></i> ترقية الباقة
                                </a>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/account.php?tab=billing" class="btn btn-outline btn-sm">
                                <i class="fas fa-credit-card"></i> إدارة الفوترة
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 15px;"></i>
                        <p style="color: var(--gray-500); margin-bottom: 20px;">لا يوجد اشتراك نشط</p>
                        <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-primary">
                            عرض الباقات
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- قائمة التراخيص -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;">التراخيص</h3>
                <?php if ($subscription && $subscription['status'] === 'active'): ?>
                    <span style="color: var(--gray-500); font-size: 0.875rem;">
                        الحد الأقصى للأجهزة: <?= $subscription['plan'] === 'teams' ? '10' : ($subscription['plan'] === 'pro' ? '3' : '1') ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (count($licenses) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--gray-200);">
                                    <th style="text-align: right; padding: 12px;">مفتاح الترخيص</th>
                                    <th style="text-align: right; padding: 12px;">الباقة</th>
                                    <th style="text-align: right; padding: 12px;">الحالة</th>
                                    <th style="text-align: right; padding: 12px;">الأجهزة</th>
                                    <th style="text-align: right; padding: 12px;">آخر تحقق</th>
                                    <th style="text-align: center; padding: 12px;">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($licenses as $license): 
                                    $devices = json_decode($license['devices'] ?: '[]', true);
                                    $maxDevices = $license['plan'] === 'teams' ? 10 : ($license['plan'] === 'pro' ? 3 : 1);
                                ?>
                                    <tr style="border-bottom: 1px solid var(--gray-200);">
                                        <td style="padding: 15px;">
                                            <code style="background: var(--gray-100); padding: 5px 10px; border-radius: 5px; font-family: monospace;">
                                                <?= htmlspecialchars($license['license_key']) ?>
                                            </code>
                                            <button onclick="copyLicense('<?= htmlspecialchars($license['license_key']) ?>')" 
                                                    class="btn btn-sm" style="margin-right: 5px; padding: 3px 8px;">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                        <td style="padding: 15px;">
                                            <span class="badge badge-<?= $license['plan'] === 'teams' ? 'primary' : ($license['plan'] === 'pro' ? 'success' : 'secondary') ?>">
                                                <?= $planNames[$license['plan']] ?? $license['plan'] ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px;">
                                            <span class="badge badge-<?= $statusNames[$license['sub_status']]['class'] ?? 'secondary' ?>">
                                                <?= $statusNames[$license['sub_status']]['text'] ?? $license['sub_status'] ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px;">
                                            <span style="color: <?= count($devices) >= $maxDevices ? 'var(--danger)' : 'var(--gray-600)' ?>">
                                                <?= count($devices) ?>/<?= $maxDevices ?>
                                            </span>
                                            <?php if (count($devices) > 0): ?>
                                                <button onclick="showDevices(<?= htmlspecialchars(json_encode($devices)) ?>, '<?= $license['license_key'] ?>')" 
                                                        class="btn btn-sm" style="padding: 3px 8px;">
                                                    <i class="fas fa-laptop"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px; color: var(--gray-500);">
                                            <?= $license['last_validated'] ? date('Y/m/d H:i', strtotime($license['last_validated'])) : 'لم يتم بعد' ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <button onclick="resetLicense('<?= $license['license_key'] ?>')" 
                                                    class="btn btn-outline btn-sm" title="إعادة تعيين الأجهزة">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-key" style="font-size: 3rem; color: var(--gray-400); margin-bottom: 15px;"></i>
                        <p style="color: var(--gray-500);">لا توجد تراخيص بعد</p>
                        <?php if (!$subscription || $subscription['plan'] === 'starter'): ?>
                            <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-primary" style="margin-top: 15px;">
                                احصل على ترخيص
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- تعليمات التفعيل -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3 style="margin: 0;">كيفية تفعيل الترخيص</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 10px;">1</span>
                            <h4 style="margin: 0;">افتح HassanIDE</h4>
                        </div>
                        <p style="color: var(--gray-500);">قم بتشغيل HassanIDE على جهازك</p>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 10px;">2</span>
                            <h4 style="margin: 0;">افتح Command Palette</h4>
                        </div>
                        <p style="color: var(--gray-500);">اضغط <code>Ctrl+Shift+P</code> (أو <code>Cmd+Shift+P</code> على Mac)</p>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 10px;">3</span>
                            <h4 style="margin: 0;">فعّل الترخيص</h4>
                        </div>
                        <p style="color: var(--gray-500);">اكتب "Activate License" والصق مفتاح الترخيص</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal للأجهزة -->
<div id="devicesModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; max-width: 500px; width: 90%; max-height: 80vh; overflow: auto;">
        <div style="padding: 20px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">الأجهزة المفعّلة</h3>
            <button onclick="closeDevicesModal()" style="background: none; border: none; cursor: pointer; font-size: 1.5rem;">&times;</button>
        </div>
        <div id="devicesList" style="padding: 20px;"></div>
    </div>
</div>

<script>
function copyLicense(key) {
    navigator.clipboard.writeText(key).then(() => {
        alert('تم نسخ مفتاح الترخيص!');
    });
}

function showDevices(devices, licenseKey) {
    const modal = document.getElementById('devicesModal');
    const list = document.getElementById('devicesList');
    
    let html = '';
    devices.forEach((device, index) => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--gray-100); border-radius: 10px; margin-bottom: 10px;">
                <div>
                    <strong>${device.machine_name || 'جهاز غير معروف'}</strong>
                    <p style="color: var(--gray-500); font-size: 0.875rem; margin: 5px 0 0;">
                        آخر ظهور: ${device.last_seen || 'غير محدد'}
                    </p>
                </div>
                <button onclick="removeDevice('${licenseKey}', '${device.machine_id}')" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    if (devices.length === 0) {
        html = '<p style="text-align: center; color: var(--gray-500);">لا توجد أجهزة مفعّلة</p>';
    }
    
    list.innerHTML = html;
    modal.style.display = 'flex';
}

function closeDevicesModal() {
    document.getElementById('devicesModal').style.display = 'none';
}

async function removeDevice(licenseKey, machineId) {
    if (!confirm('هل تريد إزالة هذا الجهاز؟')) return;
    
    try {
        const response = await fetch('<?= SITE_URL ?>/api/license-v2.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'remove_device',
                license_key: licenseKey,
                machine_id: machineId
            })
        });
        
        const result = await response.json();
        if (result.success) {
            alert('تم إزالة الجهاز بنجاح');
            location.reload();
        } else {
            alert('فشل في إزالة الجهاز');
        }
    } catch (e) {
        alert('حدث خطأ');
    }
}

async function resetLicense(licenseKey) {
    if (!confirm('هل تريد إعادة تعيين جميع الأجهزة لهذا الترخيص؟\nسيتم إزالة جميع الأجهزة المفعّلة.')) return;
    
    try {
        const response = await fetch('<?= SITE_URL ?>/api/license.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'reset_machine',
                license_key: licenseKey
            })
        });
        
        const result = await response.json();
        if (result.success) {
            alert('تم إعادة تعيين الترخيص بنجاح');
            location.reload();
        } else {
            alert('فشل في إعادة التعيين');
        }
    } catch (e) {
        alert('حدث خطأ');
    }
}

// إغلاق Modal عند النقر خارجها
document.getElementById('devicesModal').addEventListener('click', function(e) {
    if (e.target === this) closeDevicesModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
