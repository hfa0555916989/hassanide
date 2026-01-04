<?php
/**
 * Hassan IDE - Download Page
 * صفحة تحميل Hassan IDE لجميع الأنظمة
 */
$pageTitle = 'تحميل';
require_once __DIR__ . '/includes/header.php';

// قراءة معلومات الإصدارات
$versionsFile = __DIR__ . '/downloads/versions.json';
$versions = file_exists($versionsFile) ? json_decode(file_get_contents($versionsFile), true) : [];
$currentVersion = $versions['latest'] ?? '1.108.0';
$releaseDate = $versions['releaseDate'] ?? date('Y-m-d');

// روابط التحميل
$downloadBase = 'downloads/';

// Windows
$winX64Installer = $versions['windows']['x64']['installer'] ?? 'HassanIDESetup-x64.exe';
$winX64Portable = $versions['windows']['x64']['portable'] ?? 'HassanIDE-win32-x64.zip';
$winArm64Installer = $versions['windows']['arm64']['installer'] ?? 'HassanIDESetup-arm64.exe';
$winArm64Portable = $versions['windows']['arm64']['portable'] ?? 'HassanIDE-win32-arm64.zip';

// macOS
$macX64Dmg = $versions['darwin']['x64']['dmg'] ?? 'HassanIDE-darwin-x64.dmg';
$macX64Zip = $versions['darwin']['x64']['zip'] ?? 'HassanIDE-darwin-x64.zip';
$macArm64Dmg = $versions['darwin']['arm64']['dmg'] ?? 'HassanIDE-darwin-arm64.dmg';
$macArm64Zip = $versions['darwin']['arm64']['zip'] ?? 'HassanIDE-darwin-arm64.zip';

// Linux
$linuxDeb = $versions['linux']['x64']['deb'] ?? 'hassanide_amd64.deb';
$linuxRpm = $versions['linux']['x64']['rpm'] ?? 'hassanide_x86_64.rpm';
$linuxTar = $versions['linux']['x64']['tar'] ?? 'hassanide-linux-x64.tar.gz';

// التحقق من توفر الملفات
function fileAvailable($path) {
    return file_exists(__DIR__ . '/downloads/' . $path);
}
?>

<section class="section" style="padding-top: 120px;">
    <div class="container">
        <div class="section-title">
            <h2>تحميل Hassan IDE</h2>
            <p>الإصدار الحالي: <strong><?= htmlspecialchars($currentVersion) ?></strong> - تاريخ الإصدار: <?= htmlspecialchars($releaseDate) ?></p>
        </div>
        
        <div style="max-width: 1000px; margin: 0 auto;">
            <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <!-- Windows -->
                <div class="card" style="border: 2px solid #0078D4;">
                    <div class="card-header" style="background: linear-gradient(135deg, #0078D4 0%, #00BCF2 100%); color: white; text-align: center;">
                        <i class="fab fa-windows" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                        <h3 style="margin: 0; color: white;">Windows</h3>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <!-- Windows x64 -->
                        <div style="margin-bottom: 25px;">
                            <h4 style="margin-bottom: 15px; color: var(--gray-700);">
                                <i class="fas fa-microchip"></i> Windows x64
                                <span style="font-size: 0.8rem; color: var(--gray-500);">(Intel/AMD)</span>
                            </h4>
                            <?php if (fileAvailable($winX64Installer)): ?>
                            <a href="<?= $downloadBase . $winX64Installer ?>" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                                <i class="fas fa-download"></i> Installer (.exe)
                                <span style="font-size: 0.75rem; opacity: 0.8;">- <?= $versions['windows']['x64']['size'] ?? '~85 MB' ?></span>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-primary btn-block" style="margin-bottom: 8px; opacity: 0.6;" disabled>
                                <i class="fas fa-clock"></i> Installer - قيد التجهيز
                            </button>
                            <?php endif; ?>
                            
                            <?php if (fileAvailable($winX64Portable)): ?>
                            <a href="<?= $downloadBase . $winX64Portable ?>" class="btn btn-outline btn-block">
                                <i class="fas fa-file-archive"></i> Portable (.zip)
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fas fa-file-archive"></i> Portable - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Windows ARM64 -->
                        <div style="padding-top: 20px; border-top: 1px solid var(--gray-200);">
                            <h4 style="margin-bottom: 15px; color: var(--gray-700);">
                                <i class="fas fa-microchip"></i> Windows ARM64
                                <span style="font-size: 0.8rem; color: var(--gray-500);">(Snapdragon)</span>
                            </h4>
                            <?php if (fileAvailable($winArm64Installer)): ?>
                            <a href="<?= $downloadBase . $winArm64Installer ?>" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                                <i class="fas fa-download"></i> Installer (.exe)
                            </a>
                            <?php else: ?>
                            <button class="btn btn-primary btn-block" style="margin-bottom: 8px; opacity: 0.6;" disabled>
                                <i class="fas fa-clock"></i> Installer - قيد التجهيز
                            </button>
                            <?php endif; ?>
                            
                            <?php if (fileAvailable($winArm64Portable)): ?>
                            <a href="<?= $downloadBase . $winArm64Portable ?>" class="btn btn-outline btn-block">
                                <i class="fas fa-file-archive"></i> Portable (.zip)
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fas fa-file-archive"></i> Portable - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- macOS -->
                <div class="card" style="border: 2px solid #333;">
                    <div class="card-header" style="background: linear-gradient(135deg, #333 0%, #555 100%); color: white; text-align: center;">
                        <i class="fab fa-apple" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                        <h3 style="margin: 0; color: white;">macOS</h3>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <!-- macOS Intel -->
                        <div style="margin-bottom: 25px;">
                            <h4 style="margin-bottom: 15px; color: var(--gray-700);">
                                <i class="fas fa-microchip"></i> macOS Intel
                                <span style="font-size: 0.8rem; color: var(--gray-500);">(x64)</span>
                            </h4>
                            <?php if (fileAvailable($macX64Dmg)): ?>
                            <a href="<?= $downloadBase . $macX64Dmg ?>" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                                <i class="fas fa-download"></i> DMG Installer
                                <span style="font-size: 0.75rem; opacity: 0.8;">- <?= $versions['darwin']['x64']['size'] ?? '~95 MB' ?></span>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-primary btn-block" style="margin-bottom: 8px; opacity: 0.6;" disabled>
                                <i class="fas fa-clock"></i> DMG - قيد التجهيز
                            </button>
                            <?php endif; ?>
                            
                            <?php if (fileAvailable($macX64Zip)): ?>
                            <a href="<?= $downloadBase . $macX64Zip ?>" class="btn btn-outline btn-block">
                                <i class="fas fa-file-archive"></i> ZIP Archive
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fas fa-file-archive"></i> ZIP - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- macOS Apple Silicon -->
                        <div style="padding-top: 20px; border-top: 1px solid var(--gray-200);">
                            <h4 style="margin-bottom: 15px; color: var(--gray-700);">
                                <i class="fas fa-microchip"></i> macOS Apple Silicon
                                <span style="font-size: 0.8rem; color: var(--gray-500);">(M1/M2/M3)</span>
                            </h4>
                            <?php if (fileAvailable($macArm64Dmg)): ?>
                            <a href="<?= $downloadBase . $macArm64Dmg ?>" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                                <i class="fas fa-download"></i> DMG Installer
                            </a>
                            <?php else: ?>
                            <button class="btn btn-primary btn-block" style="margin-bottom: 8px; opacity: 0.6;" disabled>
                                <i class="fas fa-clock"></i> DMG - قيد التجهيز
                            </button>
                            <?php endif; ?>
                            
                            <?php if (fileAvailable($macArm64Zip)): ?>
                            <a href="<?= $downloadBase . $macArm64Zip ?>" class="btn btn-outline btn-block">
                                <i class="fas fa-file-archive"></i> ZIP Archive
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fas fa-file-archive"></i> ZIP - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Linux -->
                <div class="card" style="border: 2px solid #FCC624;">
                    <div class="card-header" style="background: linear-gradient(135deg, #333 0%, #FCC624 100%); color: white; text-align: center;">
                        <i class="fab fa-linux" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                        <h3 style="margin: 0; color: white;">Linux</h3>
                    </div>
                    <div class="card-body" style="padding: 30px;">
                        <h4 style="margin-bottom: 15px; color: var(--gray-700);">
                            <i class="fas fa-microchip"></i> Linux x64
                            <span style="font-size: 0.8rem; color: var(--gray-500);">(amd64)</span>
                        </h4>
                        
                        <!-- DEB -->
                        <div style="margin-bottom: 15px;">
                            <?php if (fileAvailable($linuxDeb)): ?>
                            <a href="<?= $downloadBase . $linuxDeb ?>" class="btn btn-primary btn-block" style="margin-bottom: 8px;">
                                <i class="fas fa-download"></i> .deb (Ubuntu/Debian)
                                <span style="font-size: 0.75rem; opacity: 0.8;">- <?= $versions['linux']['x64']['size'] ?? '~80 MB' ?></span>
                            </a>
                            <?php else: ?>
                            <button class="btn btn-primary btn-block" style="margin-bottom: 8px; opacity: 0.6;" disabled>
                                <i class="fas fa-clock"></i> .deb - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- RPM -->
                        <div style="margin-bottom: 15px;">
                            <?php if (fileAvailable($linuxRpm)): ?>
                            <a href="<?= $downloadBase . $linuxRpm ?>" class="btn btn-outline btn-block">
                                <i class="fab fa-redhat"></i> .rpm (Fedora/RHEL)
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fab fa-redhat"></i> .rpm - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- TAR.GZ -->
                        <div>
                            <?php if (fileAvailable($linuxTar)): ?>
                            <a href="<?= $downloadBase . $linuxTar ?>" class="btn btn-outline btn-block">
                                <i class="fas fa-file-archive"></i> .tar.gz (Universal)
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline btn-block" style="opacity: 0.6;" disabled>
                                <i class="fas fa-file-archive"></i> .tar.gz - قيد التجهيز
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Requirements -->
            <div class="card" style="margin-top: 40px;">
                <div class="card-header">
                    <h3><i class="fas fa-cog"></i> متطلبات النظام</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-microchip" style="color: var(--primary);"></i> المعالج</h4>
                            <p style="color: var(--gray-500);">1.6 GHz أو أسرع (ثنائي النواة)</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-memory" style="color: var(--primary);"></i> الذاكرة</h4>
                            <p style="color: var(--gray-500);">4 GB RAM (8 GB مستحسن)</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-hdd" style="color: var(--primary);"></i> المساحة</h4>
                            <p style="color: var(--gray-500);">500 MB مساحة فارغة (1 GB مستحسن)</p>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 10px;"><i class="fas fa-desktop" style="color: var(--primary);"></i> الشاشة</h4>
                            <p style="color: var(--gray-500);">1366 x 768 أو أعلى</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                        <h4 style="margin-bottom: 15px;"><i class="fas fa-check-circle" style="color: var(--success);"></i> الأنظمة المدعومة</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <strong style="color: #0078D4;"><i class="fab fa-windows"></i> Windows</strong>
                                <ul style="margin: 5px 0 0 0; padding-right: 20px; color: var(--gray-600); font-size: 0.9rem;">
                                    <li>Windows 10 (1903+)</li>
                                    <li>Windows 11</li>
                                    <li>Windows Server 2019+</li>
                                </ul>
                            </div>
                            <div>
                                <strong style="color: #333;"><i class="fab fa-apple"></i> macOS</strong>
                                <ul style="margin: 5px 0 0 0; padding-right: 20px; color: var(--gray-600); font-size: 0.9rem;">
                                    <li>macOS 10.15 (Catalina)</li>
                                    <li>macOS 11+ (Big Sur+)</li>
                                    <li>Apple Silicon (M1/M2/M3)</li>
                                </ul>
                            </div>
                            <div>
                                <strong style="color: #FCC624;"><i class="fab fa-linux"></i> Linux</strong>
                                <ul style="margin: 5px 0 0 0; padding-right: 20px; color: var(--gray-600); font-size: 0.9rem;">
                                    <li>Ubuntu 20.04+</li>
                                    <li>Debian 11+</li>
                                    <li>Fedora 36+</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Installation Guide -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3><i class="fas fa-book"></i> طريقة التثبيت</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
                        <!-- Windows Installation -->
                        <div>
                            <h4 style="color: #0078D4; margin-bottom: 15px;"><i class="fab fa-windows"></i> Windows</h4>
                            <ol style="color: var(--gray-600); padding-right: 20px; margin: 0;">
                                <li style="margin-bottom: 10px;">حمّل ملف <code>.exe</code> المناسب</li>
                                <li style="margin-bottom: 10px;">شغّل ملف التثبيت واقبل الشروط</li>
                                <li style="margin-bottom: 10px;">اختر مسار التثبيت</li>
                                <li>افتح Hassan IDE من قائمة Start</li>
                            </ol>
                        </div>
                        
                        <!-- macOS Installation -->
                        <div>
                            <h4 style="color: #333; margin-bottom: 15px;"><i class="fab fa-apple"></i> macOS</h4>
                            <ol style="color: var(--gray-600); padding-right: 20px; margin: 0;">
                                <li style="margin-bottom: 10px;">حمّل ملف <code>.dmg</code></li>
                                <li style="margin-bottom: 10px;">افتح الملف واسحب التطبيق إلى Applications</li>
                                <li style="margin-bottom: 10px;">اضغط بالزر الأيمن → فتح (أول مرة)</li>
                                <li>شغّل من Launchpad</li>
                            </ol>
                        </div>
                        
                        <!-- Linux Installation -->
                        <div>
                            <h4 style="color: #FCC624; margin-bottom: 15px;"><i class="fab fa-linux"></i> Linux</h4>
                            <ol style="color: var(--gray-600); padding-right: 20px; margin: 0;">
                                <li style="margin-bottom: 10px;">
                                    <strong>Ubuntu/Debian:</strong><br>
                                    <code style="font-size: 0.8rem;">sudo dpkg -i hassanide_*.deb</code>
                                </li>
                                <li style="margin-bottom: 10px;">
                                    <strong>Fedora/RHEL:</strong><br>
                                    <code style="font-size: 0.8rem;">sudo rpm -i hassanide_*.rpm</code>
                                </li>
                                <li>شغّل من قائمة التطبيقات</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Changelog -->
            <?php if (!empty($versions['changelog'])): ?>
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> سجل التحديثات</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($versions['changelog'] as $ver => $info): ?>
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--gray-200);">
                        <h4 style="margin-bottom: 10px;">
                            <span style="background: var(--primary); color: white; padding: 2px 10px; border-radius: 4px; font-size: 0.9rem;">v<?= htmlspecialchars($ver) ?></span>
                            <span style="color: var(--gray-500); font-size: 0.85rem; margin-right: 10px;"><?= htmlspecialchars($info['date'] ?? '') ?></span>
                        </h4>
                        <ul style="margin: 0; padding-right: 20px; color: var(--gray-600);">
                            <?php foreach ($info['changes'] ?? [] as $change): ?>
                            <li style="margin-bottom: 5px;"><?= htmlspecialchars($change) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Support -->
            <div class="card" style="margin-top: 20px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white;">
                <div class="card-body" style="text-align: center; padding: 40px;">
                    <i class="fas fa-headset" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <h3 style="color: white; margin-bottom: 15px;">تحتاج مساعدة؟</h3>
                    <p style="opacity: 0.9; margin-bottom: 20px;">فريق الدعم الفني متاح على مدار الساعة لمساعدتك</p>
                    <a href="mailto:support@hassanide.com" class="btn" style="background: white; color: var(--primary);">
                        <i class="fas fa-envelope"></i> تواصل مع الدعم
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
