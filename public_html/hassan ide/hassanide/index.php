<?php
/**
 * Hassan IDE - Home Page
 */
$pageTitle = 'الرئيسية';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>بيئة تطوير متكاملة<br>للمطورين المحترفين</h1>
            <p>Hassan IDE يوفر لك كل ما تحتاجه للبرمجة باحترافية. جاهز للعمل من أول تشغيل مع باقات مخصصة لكل نوع مشروع.</p>
            <div class="hero-buttons">
                <a href="<?= SITE_URL ?>/download.php" class="btn btn-white btn-lg">
                    <i class="fas fa-download"></i>
                    تحميل مجاني
                </a>
                <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                    <i class="fas fa-tag"></i>
                    الأسعار
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://via.placeholder.com/600x400/4F46E5/FFFFFF?text=Hassan+IDE" alt="Hassan IDE Screenshot">
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
    <div class="container">
        <div class="section-title">
            <h2>مميزات Hassan IDE</h2>
            <p>كل ما تحتاجه للبرمجة باحترافية في مكان واحد</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>جاهز من أول تشغيل</h3>
                <p>لا تضيع وقتك في الإعدادات. اختر نوع مشروعك وابدأ البرمجة فوراً مع إعدادات وإضافات جاهزة.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <h3>باقات مخصصة</h3>
                <p>باقات جاهزة لـ Web, Python, DevOps, Backend وأكثر. كل باقة تحتوي على أفضل الإضافات والإعدادات.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <h3>إضافات غير محدودة</h3>
                <p>وصول كامل لمتجر Open VSX مع آلاف الإضافات المجانية. ثبّت ما تحتاجه بضغطة واحدة.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>خصوصية تامة</h3>
                <p>لا نجمع بياناتك. Telemetry معطل افتراضياً. كودك يبقى عندك فقط.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h3>تحديثات مستمرة</h3>
                <p>احصل على أحدث المميزات والتحسينات تلقائياً مع باقة Pro.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>دعم فني</h3>
                <p>فريق دعم جاهز لمساعدتك. دعم بالإيميل لباقة Pro ودعم أولوية لباقة Teams.</p>
            </div>
        </div>
    </div>
</section>

<!-- Packs Section -->
<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>الباقات المتوفرة</h2>
            <p>اختر الباقة المناسبة لنوع مشروعك</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon" style="background: #3B82F6;">
                    <i class="fab fa-html5"></i>
                </div>
                <h3>Web / Frontend</h3>
                <p>HTML, CSS, JavaScript, React, Vue, Angular مع أدوات التصميم والتطوير.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon" style="background: #10B981;">
                    <i class="fab fa-python"></i>
                </div>
                <h3>Python / Data</h3>
                <p>Python, Jupyter, Pandas, NumPy مع أدوات تحليل البيانات والذكاء الاصطناعي.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon" style="background: #F59E0B;">
                    <i class="fab fa-docker"></i>
                </div>
                <h3>DevOps</h3>
                <p>Docker, Kubernetes, Terraform, CI/CD مع أدوات إدارة البنية التحتية.</p>
            </div>
            
            <div class="feature-card">
                <div class="icon" style="background: #8B5CF6;">
                    <i class="fas fa-server"></i>
                </div>
                <h3>Backend</h3>
                <p>Node.js, Go, Java, PHP مع أدوات بناء APIs وقواعد البيانات.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: var(--gradient); color: white; text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">جاهز للبدء؟</h2>
        <p style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 30px;">
            حمّل Hassan IDE مجاناً وجرّب الفرق بنفسك
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/download.php" class="btn btn-white btn-lg">
                <i class="fas fa-download"></i>
                تحميل الآن
            </a>
            <a href="<?= SITE_URL ?>/pricing.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                عرض الأسعار
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
