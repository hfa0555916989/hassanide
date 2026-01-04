<?php
/**
 * Hassan IDE - Language System
 * نظام اللغات - العربية والإنجليزية
 */

class Language {
    private static $instance = null;
    private $currentLang = 'en';  // Default to English
    private $translations = [];
    
    private function __construct() {
        $this->loadLanguage();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadLanguage() {
        // Check URL parameter first
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
            $this->currentLang = $_GET['lang'];
            setcookie('lang', $this->currentLang, time() + (365 * 24 * 60 * 60), '/');
        }
        // Then check cookie
        elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['ar', 'en'])) {
            $this->currentLang = $_COOKIE['lang'];
        }
        // Default to English (no browser detection override)
        // Arabic speakers can switch using the language selector
        
        $this->translations = $this->getTranslations();
    }
    
    public function getCurrentLang() {
        return $this->currentLang;
    }
    
    public function isRTL() {
        return $this->currentLang === 'ar';
    }
    
    public function get($key, $replacements = []) {
        $text = $this->translations[$this->currentLang][$key] ?? $key;
        
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
        
        return $text;
    }
    
    public function __($key, $replacements = []) {
        return $this->get($key, $replacements);
    }
    
    private function getTranslations() {
        return [
            'ar' => [
                // Navigation
                'nav_features' => 'المميزات',
                'nav_pricing' => 'الأسعار',
                'nav_download' => 'تحميل',
                'nav_my_account' => 'حسابي',
                'nav_logout' => 'خروج',
                'nav_login' => 'دخول',
                'nav_register' => 'إنشاء حساب',
                
                // Hero
                'hero_title' => 'بيئة التطوير المتكاملة للمطورين العرب',
                'hero_subtitle' => 'محرر أكواد احترافي مع دعم كامل للغة العربية ومميزات متقدمة للتطوير',
                'hero_download' => 'تحميل مجاني',
                'hero_pricing' => 'الباقات والأسعار',
                
                // Features
                'features_title' => 'مميزات Hassan IDE',
                'features_subtitle' => 'كل ما تحتاجه لتطوير تطبيقات احترافية',
                'feature_arabic' => 'دعم كامل للعربية',
                'feature_arabic_desc' => 'واجهة مستخدم عربية بالكامل مع دعم RTL',
                'feature_intellisense' => 'IntelliSense ذكي',
                'feature_intellisense_desc' => 'اقتراحات كود ذكية لجميع اللغات',
                'feature_git' => 'تكامل Git',
                'feature_git_desc' => 'إدارة المستودعات مباشرة من المحرر',
                'feature_terminal' => 'Terminal مدمج',
                'feature_terminal_desc' => 'سطر أوامر قوي داخل المحرر',
                'feature_extensions' => 'إضافات متنوعة',
                'feature_extensions_desc' => 'آلاف الإضافات لتوسيع الإمكانيات',
                'feature_themes' => 'ثيمات متعددة',
                'feature_themes_desc' => 'خصص مظهر المحرر كما تريد',
                
                // Pricing
                'pricing_title' => 'اختر الباقة المناسبة لك',
                'pricing_subtitle' => 'ابدأ مجاناً أو احصل على المميزات الكاملة',
                'pricing_monthly' => 'شهري',
                'pricing_yearly' => 'سنوي',
                'pricing_save' => 'وفّر {percent}%',
                'pricing_free' => 'مجاني',
                'pricing_forever' => 'للأبد',
                'pricing_per_month' => '/شهر',
                'pricing_per_year' => '/سنة',
                'pricing_subscribe' => 'اشترك الآن',
                'pricing_free_trial' => 'ابدأ تجربة 7 أيام مجاناً',
                'pricing_free_download' => 'تحميل مجاني',
                'pricing_most_popular' => 'الأكثر طلباً',
                'pricing_for_beginners' => 'للمبتدئين',
                'pricing_for_professionals' => 'للمحترفين',
                'pricing_for_teams' => 'للشركات',
                'pricing_users' => 'مستخدمين',
                
                // Plans
                'plan_starter' => 'Starter',
                'plan_pro' => 'Pro',
                'plan_teams' => 'Teams',
                
                // Features list
                'feature_basic_editor' => 'Hassan IDE الأساسي',
                'feature_extensions_limit' => '{count} إضافات فقط',
                'feature_one_pack' => 'Pack واحد (Web أو Python)',
                'feature_auto_updates' => 'تحديثات تلقائية',
                'feature_support' => 'دعم فني',
                'feature_all_starter' => 'كل مميزات Starter',
                'feature_all_packs' => 'جميع الـ Packs',
                'feature_unlimited_extensions' => 'إضافات غير محدودة',
                'feature_hassan_panel' => 'Hassan Panel كامل',
                'feature_email_support' => 'دعم بالإيميل ({hours} ساعة)',
                'feature_all_pro' => 'كل مميزات Pro',
                'feature_team_dashboard' => 'لوحة تحكم الفريق',
                'feature_permissions' => 'سياسات وصلاحيات',
                'feature_priority_support' => 'دعم أولوية ({hours} ساعة)',
                'feature_invoice' => 'فاتورة رسمية',
                
                // Download
                'download_title' => 'تحميل Hassan IDE',
                'download_current_version' => 'الإصدار الحالي',
                'download_installer' => 'مثبّت',
                'download_portable' => 'محمول',
                'download_coming_soon' => 'قريباً',
                
                // System Requirements
                'requirements_title' => 'متطلبات النظام',
                'requirements_processor' => 'المعالج',
                'requirements_processor_desc' => '1.6 GHz أو أسرع',
                'requirements_memory' => 'الذاكرة',
                'requirements_memory_desc' => '4 GB RAM (8 GB مستحسن)',
                'requirements_storage' => 'المساحة',
                'requirements_storage_desc' => '500 MB مساحة فارغة',
                'requirements_display' => 'الشاشة',
                'requirements_display_desc' => '1024 x 768 أو أعلى',
                
                // Installation
                'installation_title' => 'طريقة التثبيت',
                'installation_step1' => 'حمّل الملف المناسب لنظام تشغيلك',
                'installation_step2' => 'شغّل ملف التثبيت واتبع التعليمات',
                'installation_step3' => 'افتح Hassan IDE من قائمة البرامج',
                'installation_step4' => 'اختر الباقة المناسبة',
                'installation_step5' => 'ابدأ البرمجة!',
                
                // Account
                'account_title' => 'حسابي',
                'account_subscription' => 'الاشتراك',
                'account_plan' => 'الباقة',
                'account_status' => 'الحالة',
                'account_active' => 'نشط',
                'account_expired' => 'منتهي',
                'account_expires' => 'ينتهي في',
                'account_license_key' => 'مفتاح الترخيص',
                'account_devices' => 'الأجهزة',
                'account_upgrade' => 'ترقية الباقة',
                'account_cancel' => 'إلغاء الاشتراك',
                
                // Auth
                'auth_login' => 'تسجيل الدخول',
                'auth_register' => 'إنشاء حساب',
                'auth_email' => 'البريد الإلكتروني',
                'auth_password' => 'كلمة المرور',
                'auth_confirm_password' => 'تأكيد كلمة المرور',
                'auth_name' => 'الاسم',
                'auth_forgot_password' => 'نسيت كلمة المرور؟',
                'auth_no_account' => 'ليس لديك حساب؟',
                'auth_have_account' => 'لديك حساب بالفعل؟',
                
                // Footer
                'footer_rights' => 'جميع الحقوق محفوظة',
                'footer_company' => 'Hassan Tech',
                'footer_privacy' => 'سياسة الخصوصية',
                'footer_terms' => 'الشروط والأحكام',
                'footer_contact' => 'تواصل معنا',
                
                // Common
                'loading' => 'جاري التحميل...',
                'error' => 'حدث خطأ',
                'success' => 'تمت العملية بنجاح',
                'cancel' => 'إلغاء',
                'save' => 'حفظ',
                'delete' => 'حذف',
                'edit' => 'تعديل',
                'copy' => 'نسخ',
                'copied' => 'تم النسخ',
                
                // Currency & Enterprise
                'select_currency' => 'العملة',
                'enterprise_title' => 'تحتاج باقة Enterprise؟',
                'enterprise_desc' => 'حلول مخصصة للمؤسسات الكبيرة مع دعم مخصص وتسجيل دخول موحد ومستخدمين غير محدودين.',
                'enterprise_contact' => 'تواصل مع المبيعات',
            ],
            
            'en' => [
                // Navigation
                'nav_features' => 'Features',
                'nav_pricing' => 'Pricing',
                'nav_download' => 'Download',
                'nav_my_account' => 'My Account',
                'nav_logout' => 'Logout',
                'nav_login' => 'Login',
                'nav_register' => 'Sign Up',
                
                // Hero
                'hero_title' => 'The IDE for Arab Developers',
                'hero_subtitle' => 'Professional code editor with full Arabic support and advanced development features',
                'hero_download' => 'Free Download',
                'hero_pricing' => 'View Plans',
                
                // Features
                'features_title' => 'Hassan IDE Features',
                'features_subtitle' => 'Everything you need to build professional applications',
                'feature_arabic' => 'Full Arabic Support',
                'feature_arabic_desc' => 'Complete Arabic UI with RTL support',
                'feature_intellisense' => 'Smart IntelliSense',
                'feature_intellisense_desc' => 'Intelligent code suggestions for all languages',
                'feature_git' => 'Git Integration',
                'feature_git_desc' => 'Manage repositories directly from the editor',
                'feature_terminal' => 'Built-in Terminal',
                'feature_terminal_desc' => 'Powerful command line inside the editor',
                'feature_extensions' => 'Rich Extensions',
                'feature_extensions_desc' => 'Thousands of extensions to expand capabilities',
                'feature_themes' => 'Multiple Themes',
                'feature_themes_desc' => 'Customize the editor appearance as you like',
                
                // Pricing
                'pricing_title' => 'Choose Your Plan',
                'pricing_subtitle' => 'Start free or get full features',
                'pricing_monthly' => 'Monthly',
                'pricing_yearly' => 'Yearly',
                'pricing_save' => 'Save {percent}%',
                'pricing_free' => 'Free',
                'pricing_forever' => 'Forever',
                'pricing_per_month' => '/month',
                'pricing_per_year' => '/year',
                'pricing_subscribe' => 'Subscribe Now',
                'pricing_free_trial' => 'Start 7-Day Free Trial',
                'pricing_free_download' => 'Free Download',
                'pricing_most_popular' => 'Most Popular',
                'pricing_for_beginners' => 'For Beginners',
                'pricing_for_professionals' => 'For Professionals',
                'pricing_for_teams' => 'For Teams',
                'pricing_users' => 'users',
                
                // Plans
                'plan_starter' => 'Starter',
                'plan_pro' => 'Pro',
                'plan_teams' => 'Teams',
                
                // Features list
                'feature_basic_editor' => 'Basic Hassan IDE',
                'feature_extensions_limit' => '{count} extensions only',
                'feature_one_pack' => 'One Pack (Web or Python)',
                'feature_auto_updates' => 'Automatic updates',
                'feature_support' => 'Technical support',
                'feature_all_starter' => 'All Starter features',
                'feature_all_packs' => 'All Packs',
                'feature_unlimited_extensions' => 'Unlimited extensions',
                'feature_hassan_panel' => 'Full Hassan Panel',
                'feature_email_support' => 'Email support ({hours}h)',
                'feature_all_pro' => 'All Pro features',
                'feature_team_dashboard' => 'Team Dashboard',
                'feature_permissions' => 'Policies & Permissions',
                'feature_priority_support' => 'Priority support ({hours}h)',
                'feature_invoice' => 'Official invoice',
                
                // Download
                'download_title' => 'Download Hassan IDE',
                'download_current_version' => 'Current Version',
                'download_installer' => 'Installer',
                'download_portable' => 'Portable',
                'download_coming_soon' => 'Coming Soon',
                
                // System Requirements
                'requirements_title' => 'System Requirements',
                'requirements_processor' => 'Processor',
                'requirements_processor_desc' => '1.6 GHz or faster',
                'requirements_memory' => 'Memory',
                'requirements_memory_desc' => '4 GB RAM (8 GB recommended)',
                'requirements_storage' => 'Storage',
                'requirements_storage_desc' => '500 MB free space',
                'requirements_display' => 'Display',
                'requirements_display_desc' => '1024 x 768 or higher',
                
                // Installation
                'installation_title' => 'Installation Guide',
                'installation_step1' => 'Download the appropriate file for your OS',
                'installation_step2' => 'Run the installer and follow instructions',
                'installation_step3' => 'Open Hassan IDE from the Start menu',
                'installation_step4' => 'Choose your preferred pack',
                'installation_step5' => 'Start coding!',
                
                // Account
                'account_title' => 'My Account',
                'account_subscription' => 'Subscription',
                'account_plan' => 'Plan',
                'account_status' => 'Status',
                'account_active' => 'Active',
                'account_expired' => 'Expired',
                'account_expires' => 'Expires on',
                'account_license_key' => 'License Key',
                'account_devices' => 'Devices',
                'account_upgrade' => 'Upgrade Plan',
                'account_cancel' => 'Cancel Subscription',
                
                // Auth
                'auth_login' => 'Login',
                'auth_register' => 'Sign Up',
                'auth_email' => 'Email',
                'auth_password' => 'Password',
                'auth_confirm_password' => 'Confirm Password',
                'auth_name' => 'Name',
                'auth_forgot_password' => 'Forgot password?',
                'auth_no_account' => "Don't have an account?",
                'auth_have_account' => 'Already have an account?',
                
                // Footer
                'footer_rights' => 'All rights reserved',
                'footer_company' => 'Hassan Tech',
                'footer_privacy' => 'Privacy Policy',
                'footer_terms' => 'Terms of Service',
                'footer_contact' => 'Contact Us',
                
                // Common
                'loading' => 'Loading...',
                'error' => 'An error occurred',
                'success' => 'Operation successful',
                'cancel' => 'Cancel',
                'save' => 'Save',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'copy' => 'Copy',
                'copied' => 'Copied',
                
                // Currency & Enterprise
                'select_currency' => 'Currency',
                'enterprise_title' => 'Need Enterprise?',
                'enterprise_desc' => 'Custom solutions for large organizations with dedicated support, SSO, and unlimited seats.',
                'enterprise_contact' => 'Contact Sales',
            ]
        ];
    }
}

// Helper function for easy translation access
function __($key, $replacements = []) {
    return Language::getInstance()->get($key, $replacements);
}

function lang() {
    return Language::getInstance()->getCurrentLang();
}

function isRTL() {
    return Language::getInstance()->isRTL();
}
