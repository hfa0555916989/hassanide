<?php
/**
 * Hassan IDE - Pricing Page
 */
$pageTitle = 'الأسعار';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 120px;">
    <div class="container">
        <div class="section-title">
            <h2>اختر الباقة المناسبة لك</h2>
            <p>ابدأ مجاناً أو احصل على المميزات الكاملة مع باقة Pro</p>
            
            <!-- Billing Toggle -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 30px;">
                <span>شهري</span>
                <label style="position: relative; display: inline-block; width: 60px; height: 30px;">
                    <input type="checkbox" id="billingToggle" style="display: none;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--gray-300); border-radius: 30px; transition: 0.3s;">
                        <span style="position: absolute; content: ''; height: 24px; width: 24px; right: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s;" id="toggleDot"></span>
                    </span>
                </label>
                <span>سنوي</span>
                <span style="background: var(--success); color: white; padding: 3px 10px; border-radius: 20px; font-size: 0.85rem;">وفّر 20%</span>
            </div>
        </div>
        
        <div class="pricing-grid">
            <!-- Starter Plan -->
            <div class="pricing-card">
                <h3>Starter</h3>
                <p style="color: var(--gray-500); margin-bottom: 20px;">للمبتدئين</p>
                <div class="price" data-price-monthly="مجاني" data-price-yearly="مجاني">مجاني</div>
                <p class="billing" data-billing="">للأبد</p>
                
                <ul>
                    <li><i class="fas fa-check"></i> Hassan IDE الأساسي</li>
                    <li><i class="fas fa-check"></i> 5 إضافات فقط</li>
                    <li><i class="fas fa-check"></i> Pack واحد (Web أو Python)</li>
                    <li class="disabled"><i class="fas fa-times"></i> تحديثات تلقائية</li>
                    <li class="disabled"><i class="fas fa-times"></i> دعم فني</li>
                </ul>
                
                <a href="<?= SITE_URL ?>/download.php" class="btn btn-outline btn-block">
                    تحميل مجاني
                </a>
            </div>
            
            <!-- Pro Plan -->
            <div class="pricing-card featured">
                <span class="badge">الأكثر طلباً</span>
                <h3>Pro</h3>
                <p style="color: var(--gray-500); margin-bottom: 20px;">للمحترفين</p>
                <div class="price">
                    <span data-price-monthly="29" data-price-yearly="290">29</span>
                    <span data-billing="/شهر">/شهر</span>
                </div>
                <p class="billing">أو 290 ريال/سنة (وفّر 20%)</p>
                
                <ul>
                    <li><i class="fas fa-check"></i> كل مميزات Starter</li>
                    <li><i class="fas fa-check"></i> جميع الـ Packs</li>
                    <li><i class="fas fa-check"></i> إضافات غير محدودة</li>
                    <li><i class="fas fa-check"></i> تحديثات تلقائية</li>
                    <li><i class="fas fa-check"></i> Hassan Panel كامل</li>
                    <li><i class="fas fa-check"></i> دعم بالإيميل (48 ساعة)</li>
                </ul>
                
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/checkout.php?plan=pro" class="btn btn-primary btn-block">
                        اشترك الآن
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/register.php?plan=pro" class="btn btn-primary btn-block">
                        ابدأ تجربة 7 أيام مجاناً
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Teams Plan -->
            <div class="pricing-card">
                <h3>Teams</h3>
                <p style="color: var(--gray-500); margin-bottom: 20px;">للشركات</p>
                <div class="price">
                    <span data-price-monthly="99" data-price-yearly="990">99</span>
                    <span data-billing="/شهر">/شهر</span>
                </div>
                <p class="billing">لـ 5 مستخدمين</p>
                
                <ul>
                    <li><i class="fas fa-check"></i> كل مميزات Pro</li>
                    <li><i class="fas fa-check"></i> 5 مستخدمين</li>
                    <li><i class="fas fa-check"></i> لوحة تحكم الفريق</li>
                    <li><i class="fas fa-check"></i> سياسات وصلاحيات</li>
                    <li><i class="fas fa-check"></i> دعم أولوية (24 ساعة)</li>
                    <li><i class="fas fa-check"></i> فاتورة رسمية</li>
                </ul>
                
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/checkout.php?plan=teams" class="btn btn-outline btn-block">
                        اشترك الآن
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/register.php?plan=teams" class="btn btn-outline btn-block">
                        ابدأ تجربة مجانية
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- FAQ -->
        <div style="max-width: 700px; margin: 60px auto 0;">
            <h3 style="text-align: center; margin-bottom: 30px;">أسئلة شائعة</h3>
            
            <div class="card" style="margin-bottom: 15px;">
                <div class="card-body">
                    <h4 style="margin-bottom: 10px;">هل يمكنني الإلغاء في أي وقت؟</h4>
                    <p style="color: var(--gray-500);">نعم، يمكنك إلغاء اشتراكك في أي وقت من لوحة التحكم. ستستمر في استخدام الخدمة حتى نهاية الفترة المدفوعة.</p>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 15px;">
                <div class="card-body">
                    <h4 style="margin-bottom: 10px;">ما هي طرق الدفع المتاحة؟</h4>
                    <p style="color: var(--gray-500);">نقبل جميع البطاقات الائتمانية (Visa, Mastercard) ومدى وApple Pay عبر بوابة PayMob الآمنة.</p>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 15px;">
                <div class="card-body">
                    <h4 style="margin-bottom: 10px;">هل التجربة المجانية تحتاج بطاقة؟</h4>
                    <p style="color: var(--gray-500);">لا، التجربة المجانية لـ 7 أيام لا تحتاج إدخال بيانات بطاقة. فقط سجّل حساب وابدأ الاستخدام.</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h4 style="margin-bottom: 10px;">هل يمكنني الترقية لاحقاً؟</h4>
                    <p style="color: var(--gray-500);">نعم، يمكنك الترقية من Starter إلى Pro أو Teams في أي وقت من لوحة التحكم.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Toggle billing display
document.getElementById('billingToggle').addEventListener('change', function() {
    const dot = document.getElementById('toggleDot');
    if (this.checked) {
        dot.style.right = 'auto';
        dot.style.left = '3px';
        dot.parentElement.style.background = 'var(--primary)';
    } else {
        dot.style.left = 'auto';
        dot.style.right = '3px';
        dot.parentElement.style.background = 'var(--gray-300)';
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
