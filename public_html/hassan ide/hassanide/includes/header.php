<?php
/**
 * Hassan IDE - Header
 */
if (!defined('HASSAN_IDE')) {
    define('HASSAN_IDE', true);
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../api/auth.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Hassan IDE' ?> - بيئة التطوير المتكاملة</title>
    <meta name="description" content="Hassan IDE - بيئة تطوير متكاملة احترافية للمطورين العرب">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?= SITE_URL ?>" class="logo">
                <i class="fas fa-code"></i>
                <span>Hassan IDE</span>
            </a>
            
            <div class="nav-links">
                <a href="<?= SITE_URL ?>/#features">المميزات</a>
                <a href="<?= SITE_URL ?>/pricing.php">الأسعار</a>
                <a href="<?= SITE_URL ?>/download.php">تحميل</a>
                
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/account.php">حسابي</a>
                    <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline">خروج</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php">دخول</a>
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary">إنشاء حساب</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="<?= SITE_URL ?>/#features">المميزات</a>
        <a href="<?= SITE_URL ?>/pricing.php">الأسعار</a>
        <a href="<?= SITE_URL ?>/download.php">تحميل</a>
        <?php if ($currentUser): ?>
            <a href="<?= SITE_URL ?>/account.php">حسابي</a>
            <a href="<?= SITE_URL ?>/logout.php">خروج</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php">دخول</a>
            <a href="<?= SITE_URL ?>/register.php">إنشاء حساب</a>
        <?php endif; ?>
    </div>
    
    <!-- Flash Messages -->
    <?php if ($flash): ?>
    <div class="flash-message flash-<?= $flash['type'] ?>">
        <div class="container">
            <?php if ($flash['type'] === 'success'): ?>
                <i class="fas fa-check-circle"></i>
            <?php elseif ($flash['type'] === 'error'): ?>
                <i class="fas fa-exclamation-circle"></i>
            <?php else: ?>
                <i class="fas fa-info-circle"></i>
            <?php endif; ?>
            <span><?= $flash['message'] ?></span>
            <button class="flash-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>
    
    <main>
