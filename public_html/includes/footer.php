    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="<?= SITE_URL ?>" class="logo">
                        <i class="fas fa-code"></i>
                        <span>Hassan IDE</span>
                    </a>
                    <p>بيئة تطوير متكاملة احترافية مصممة خصيصاً للمطورين العرب.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>المنتج</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/#features">المميزات</a></li>
                        <li><a href="<?= SITE_URL ?>/pricing.php">الأسعار</a></li>
                        <li><a href="<?= SITE_URL ?>/download.php">تحميل</a></li>
                        <li><a href="<?= SITE_URL ?>/changelog.php">سجل التحديثات</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>الدعم</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/faq.php">الأسئلة الشائعة</a></li>
                        <li><a href="<?= SITE_URL ?>/docs.php">التوثيق</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">تواصل معنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>قانوني</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/privacy.php">سياسة الخصوصية</a></li>
                        <li><a href="<?= SITE_URL ?>/terms.php">شروط الاستخدام</a></li>
                        <li><a href="<?= SITE_URL ?>/refund.php">سياسة الاسترداد</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© <?= date('Y') ?> Hassan IDE. جميع الحقوق محفوظة.</p>
                <p>
                    <i class="fas fa-lock"></i>
                    دفع آمن عبر 
                    <img src="<?= SITE_URL ?>/assets/images/paymob-logo.svg" alt="PayMob" class="payment-logo">
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
