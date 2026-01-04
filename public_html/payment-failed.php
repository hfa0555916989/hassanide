<?php
/**
 * Hassan IDE - Payment Failed Page
 */
$pageTitle = 'فشل الدفع';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 120px; min-height: 70vh;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div style="width: 100px; height: 100px; background: #FEE2E2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                <i class="fas fa-times" style="font-size: 3rem; color: #991B1B;"></i>
            </div>
            
            <h1 style="font-size: 2.5rem; color: var(--gray-900); margin-bottom: 15px;">فشلت عملية الدفع</h1>
            <p style="font-size: 1.1rem; color: var(--gray-500); margin-bottom: 30px;">
                لم تتم عملية الدفع. لم يتم خصم أي مبلغ من حسابك.
            </p>
            
            <div class="card" style="text-align: right; margin-bottom: 30px;">
                <div class="card-body">
                    <h3 style="margin-bottom: 15px;">الأسباب المحتملة</h3>
                    <ul style="color: var(--gray-600); padding-right: 20px;">
                        <li style="margin-bottom: 10px;">رصيد غير كافي في البطاقة</li>
                        <li style="margin-bottom: 10px;">بيانات البطاقة غير صحيحة</li>
                        <li style="margin-bottom: 10px;">البطاقة مرفوضة من البنك</li>
                        <li style="margin-bottom: 10px;">انتهاء صلاحية البطاقة</li>
                        <li>مشكلة في الاتصال</li>
                    </ul>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-redo"></i>
                    حاول مرة أخرى
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-headset"></i>
                    تواصل معنا
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
