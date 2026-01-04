<?php
/**
 * Hassan IDE - Header
 */
if (!defined('HASSAN_IDE')) {
    define('HASSAN_IDE', true);
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/../api/auth.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();
$flash = getFlash();
$lang = Language::getInstance();
$currentLang = $lang->getCurrentLang();
$isRTL = $lang->isRTL();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Hassan IDE' ?> - <?= $isRTL ? 'بيئة التطوير المتكاملة' : 'Integrated Development Environment' ?></title>
    <meta name="description" content="<?= $isRTL ? 'Hassan IDE - بيئة تطوير متكاملة احترافية للمطورين العرب' : 'Hassan IDE - Professional IDE for Arab Developers' ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/images/favicon.svg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: <?= $isRTL ? "'Tajawal'" : "'Inter'" ?>, sans-serif; }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="lang-switcher">
        <a href="?lang=ar" class="<?= $currentLang === 'ar' ? 'active' : '' ?>">العربية</a>
        <a href="?lang=en" class="<?= $currentLang === 'en' ? 'active' : '' ?>">English</a>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?= SITE_URL ?>" class="logo">
                <i class="fas fa-code"></i>
                <span>Hassan IDE</span>
            </a>
            
            <div class="nav-links">
                <a href="<?= SITE_URL ?>/#features"><?= __('nav_features') ?></a>
                <a href="<?= SITE_URL ?>/pricing.php"><?= __('nav_pricing') ?></a>
                <a href="<?= SITE_URL ?>/download.php"><?= __('nav_download') ?></a>
                
                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/account.php"><?= __('nav_my_account') ?></a>
                    <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline"><?= __('nav_logout') ?></a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php"><?= __('nav_login') ?></a>
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary"><?= __('nav_register') ?></a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="<?= SITE_URL ?>/#features"><?= __('nav_features') ?></a>
        <a href="<?= SITE_URL ?>/pricing.php"><?= __('nav_pricing') ?></a>
        <a href="<?= SITE_URL ?>/download.php"><?= __('nav_download') ?></a>
        <?php if ($currentUser): ?>
            <a href="<?= SITE_URL ?>/account.php"><?= __('nav_my_account') ?></a>
            <a href="<?= SITE_URL ?>/logout.php"><?= __('nav_logout') ?></a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php"><?= __('nav_login') ?></a>
            <a href="<?= SITE_URL ?>/register.php"><?= __('nav_register') ?></a>
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
