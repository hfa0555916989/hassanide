<?php
/**
 * Hassan IDE - Download Page
 */
$pageTitle = 'تحميل';
require_once __DIR__ . '/includes/header.php';

$currentVersion = '1.0.0';
?>

<section class="section" style="padding-top: 120px;">
    <div class="container">
        <div class="section-title">
            <h2>تحميل Hassan IDE</h2>
            <p>الإصدار الحالي: <?= $currentVersion ?></p>
        </div>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <!-- Windows -->
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 40px;">
                        <i class="fab fa-windows" style="font-size: 4rem; color: #0078D4; margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px;">Windows</h3>
                        <p style="color: var(--gray-500); margin-bottom: 20px;">Windows 10/11 (64-bit)</p>
                        <a href="#" class="btn btn-primary btn-block" style="margin-bottom: 10px;">
                            <i class="fas fa-download"></i>
                            Installer (.exe)
                        </a>
                        <a href="#" class="btn btn-outline btn-block">
                            Portable (.zip)
                        </a>
                    </div>
                </div>
                
                <!-- macOS -->
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 40px;">
                        <i class="fab fa-apple" style="font-size: 4rem; color: #333; margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px;">macOS</h3>
                        <p style="color: var(--gray-500); margin-bottom: 20px;">macOS 10.15+</p>
                        <a href="#" class="btn btn-primary btn-block" style="margin-bottom: 10px; opacity: 0.5;" disabled>
                            <i class="fas fa-clock"></i>
                            قريباً
                        </a>
                    </div>
                </div>
                
                <!-- Linux -->
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 40px;">
                        <i class="fab fa-linux" style="font-size: 4rem; color: #FCC624; margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px;">Linux</h3>
                        <p style="color: var(--gray-500); margin-bottom: 20px;">Ubuntu, Debian, Fedora</p>
                        <a href="#" class="btn btn-primary btn-block" style="margin-bottom: 10px; opacity: 0.5;" disabled>
                            <i class="fas fa-clock"></i>
                            قريباً
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Requirements -->
            <div class="card" style="margin-top: 40px;">
                <div class="card-header">
                    <h3>متطلبات النظام</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-microchip"></i> المعالج</h4>
                            <p style="color: var(--gray-500);">1.6 GHz أو أسرع</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-memory"></i> الذاكرة</h4>
                            <p style="color: var(--gray-500);">4 GB RAM (8 GB مستحسن)</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-hdd"></i> المساحة</h4>
                            <p style="color: var(--gray-500);">500 MB مساحة فارغة</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-desktop"></i> الشاشة</h4>
                            <p style="color: var(--gray-500);">1024 x 768 أو أعلى</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Installation Guide -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3>طريقة التثبيت</h3>
                </div>
                <div class="card-body">
                    <ol style="color: var(--gray-600); padding-right: 20px;">
                        <li style="margin-bottom: 15px;">
                            <strong>حمّل الملف المناسب</strong> لنظام تشغيلك
                        </li>
                        <li style="margin-bottom: 15px;">
                            <strong>شغّل ملف التثبيت</strong> واتبع التعليمات
                        </li>
                        <li style="margin-bottom: 15px;">
                            <strong>افتح Hassan IDE</strong> من قائمة البرامج
                        </li>
                        <li style="margin-bottom: 15px;">
                            <strong>اختر الباقة المناسبة</strong> (Web, Python, DevOps, etc.)
                        </li>
                        <li>
                            <strong>ابدأ البرمجة!</strong> 🚀
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
