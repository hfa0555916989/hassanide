<?php
/**
 * Hassan IDE - Checkout Page
 */
$pageTitle = 'الدفع';

define('HASSAN_IDE', true);
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/api/config.php';

$auth = new Auth();

// Must be logged in
if (!$auth->isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php?redirect=checkout.php?plan=' . ($_GET['plan'] ?? 'pro'));
    exit;
}

$currentUser = $auth->getCurrentUser();
$selectedPlan = $_GET['plan'] ?? 'pro';
$billingCycle = $_GET['billing'] ?? 'monthly';

// Get plan details
$plan = getPlan($selectedPlan);
if (!$plan || $plan['is_free']) {
    header('Location: ' . SITE_URL . '/pricing.php');
    exit;
}

$price = $billingCycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="payment-container">
    <div class="card">
        <div class="card-header">
            <h3>إتمام الاشتراك</h3>
        </div>
        <div class="card-body">
            <!-- Order Summary -->
            <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px;">ملخص الطلب</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>باقة <?= $plan['name_ar'] ?></span>
                    <span><?= formatPrice($price) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; color: var(--gray-500); font-size: 0.9rem;">
                    <span>الدورة</span>
                    <span><?= $billingCycle === 'yearly' ? 'سنوية' : 'شهرية' ?></span>
                </div>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid var(--gray-200);">
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem;">
                    <span>الإجمالي</span>
                    <span style="color: var(--primary);"><?= formatPrice($price) ?></span>
                </div>
            </div>
            
            <!-- Billing Cycle Toggle -->
            <div style="margin-bottom: 25px;">
                <label style="font-weight: 500; margin-bottom: 10px; display: block;">اختر الدورة:</label>
                <div style="display: flex; gap: 10px;">
                    <a href="?plan=<?= $selectedPlan ?>&billing=monthly" 
                       class="btn <?= $billingCycle === 'monthly' ? 'btn-primary' : 'btn-outline' ?>" 
                       style="flex: 1;">
                        شهري - <?= formatPrice($plan['price_monthly']) ?>
                    </a>
                    <a href="?plan=<?= $selectedPlan ?>&billing=yearly" 
                       class="btn <?= $billingCycle === 'yearly' ? 'btn-primary' : 'btn-outline' ?>" 
                       style="flex: 1;">
                        سنوي - <?= formatPrice($plan['price_yearly']) ?>
                        <span style="background: var(--success); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-right: 5px;">وفّر 20%</span>
                    </a>
                </div>
            </div>
            
            <!-- User Info -->
            <div style="margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px;">معلومات الحساب</h4>
                <div class="form-group">
                    <label>الاسم</label>
                    <input type="text" id="userName" class="form-control" value="<?= htmlspecialchars($currentUser['name']) ?>" readonly style="background: var(--gray-50);">
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" id="userEmail" class="form-control" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly style="background: var(--gray-50);">
                </div>
                <div class="form-group">
                    <label>رقم الجوال (اختياري)</label>
                    <input type="tel" id="userPhone" class="form-control" placeholder="+966500000000" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                </div>
            </div>
            
            <!-- Payment Button -->
            <button onclick="initiatePayment()" class="btn btn-primary btn-block btn-lg" id="payButton">
                <i class="fas fa-lock"></i>
                ادفع <?= formatPrice($price) ?>
            </button>
            
            <p style="text-align: center; margin-top: 15px; color: var(--gray-500); font-size: 0.85rem;">
                <i class="fas fa-shield-alt"></i>
                دفع آمن عبر PayMob. بياناتك محمية بتشفير SSL.
            </p>
            
            <!-- Payment Methods -->
            <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; opacity: 0.6;">
                <i class="fab fa-cc-visa fa-2x"></i>
                <i class="fab fa-cc-mastercard fa-2x"></i>
                <i class="fab fa-apple-pay fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<script>
async function initiatePayment() {
    const btn = document.getElementById('payButton');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></div> جاري التحويل...';
    
    try {
        const response = await fetch('<?= SITE_URL ?>/api/paymob.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_payment',
                plan: '<?= $selectedPlan ?>',
                billing_cycle: '<?= $billingCycle ?>',
                user_id: <?= $currentUser['id'] ?>,
                name: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value,
                phone: document.getElementById('userPhone').value
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.iframe_url) {
            window.location.href = result.iframe_url;
        } else {
            alert(result.error || 'حدث خطأ في إنشاء عملية الدفع');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> ادفع <?= formatPrice($price) ?>';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> ادفع <?= formatPrice($price) ?>';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
