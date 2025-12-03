// دالة للتهيئة
document.addEventListener('DOMContentLoaded', function() {
    // تحسين النماذج
    enhanceForms();
    
    // تحسين التنقل
    enhanceNavigation();
    
    // تحسين البطاقات
    enhanceCards();
    
    // إضافة تأثيرات تفاعلية
    addInteractiveEffects();
});

// تحسين النماذج
function enhanceForms() {
    // إضافة التحقق من الصحة للنماذج
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
            }
        });
    });
    
    // تحسين تجربة المستخدم في الحقول
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
}

// تحسين التنقل
function enhanceNavigation() {
    // إضافة تأثيرات للروابط
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(-5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // التنقل السلس
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// تحسين البطاقات
function enhanceCards() {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// إضافة تأثيرات تفاعلية
function addInteractiveEffects() {
    // تأثيرات للصور
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.3s ease';
    });
    
    // تأثيرات للأزرار
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // تأثير النقر
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// دالة لعرض الإشعارات
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast alert alert-${type} position-fixed`;
    toast.style.cssText = `
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 300px;
        text-align: center;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// دالة للتحميل
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = `
        <span class="loading"></span>
        جاري المعالجة...
    `;
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// إضافة أنماط للريبل
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .toast {
        animation: fadeInOut 3s ease-in-out;
    }
    
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; transform: translate(-50%, -20px); }
        10%, 90% { opacity: 1; transform: translate(-50%, 0); }
    }
`;
document.head.appendChild(style);

// دالة لمشاركة المحتوى
function shareContent(title, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // نسخ الرابط إلى الحافظة
        navigator.clipboard.writeText(url).then(() => {
            showToast('تم نسخ الرابط إلى الحافظة', 'success');
        });
    }
}

// تهيئة مشاركة المحتوى
document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll('[data-share]');
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const title = this.dataset.shareTitle || document.title;
            const url = this.dataset.shareUrl || window.location.href;
            shareContent(title, url);
        });
    });
});