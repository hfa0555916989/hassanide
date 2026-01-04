/**
 * Hassan IDE - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }
    
    // Close mobile menu on link click
    document.querySelectorAll('.mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
        });
    });
    
    // Flash message auto-hide
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(el => el.remove());
            form.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
            
            // Check required fields
            form.querySelectorAll('[required]').forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    showError(input, 'هذا الحقل مطلوب');
                }
            });
            
            // Check email fields
            form.querySelectorAll('[type="email"]').forEach(input => {
                if (input.value && !isValidEmail(input.value)) {
                    valid = false;
                    showError(input, 'البريد الإلكتروني غير صالح');
                }
            });
            
            // Check password match
            const password = form.querySelector('[name="password"]');
            const confirmPassword = form.querySelector('[name="confirm_password"]');
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                valid = false;
                showError(confirmPassword, 'كلمة المرور غير متطابقة');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    });
    
    function showError(input, message) {
        input.classList.add('error');
        const error = document.createElement('div');
        error.className = 'form-error';
        error.textContent = message;
        input.parentNode.appendChild(error);
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // Password visibility toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
    
    // Copy to clipboard
    window.copyToClipboard = function(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> تم النسخ';
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
            }, 2000);
        });
    };
    
    // Pricing toggle (monthly/yearly)
    const billingToggle = document.getElementById('billingToggle');
    if (billingToggle) {
        billingToggle.addEventListener('change', function() {
            const isYearly = this.checked;
            
            document.querySelectorAll('[data-price-monthly]').forEach(el => {
                const monthly = el.dataset.priceMonthly;
                const yearly = el.dataset.priceYearly;
                el.textContent = isYearly ? yearly : monthly;
            });
            
            document.querySelectorAll('[data-billing]').forEach(el => {
                el.textContent = isYearly ? '/سنة' : '/شهر';
            });
            
            document.querySelectorAll('input[name="billing_cycle"]').forEach(input => {
                input.value = isYearly ? 'yearly' : 'monthly';
            });
        });
    }
    
});

// AJAX helper
async function apiRequest(url, data = {}, method = 'POST') {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: method !== 'GET' ? JSON.stringify(data) : undefined
        });
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: 'حدث خطأ في الاتصال' };
    }
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.remove();
}

// Initialize PayMob payment
async function initPayment(plan, billingCycle) {
    showLoading();
    
    const result = await apiRequest('/api/paymob.php', {
        action: 'create_payment',
        plan: plan,
        billing_cycle: billingCycle,
        name: document.getElementById('userName')?.value || '',
        email: document.getElementById('userEmail')?.value || '',
        phone: document.getElementById('userPhone')?.value || ''
    });
    
    hideLoading();
    
    if (result.success && result.iframe_url) {
        // Redirect to PayMob iframe or open in new window
        window.location.href = result.iframe_url;
    } else {
        alert(result.error || 'حدث خطأ في إنشاء عملية الدفع');
    }
}
