// Laravel Web App JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token setup for AJAX if needed
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.Laravel = {
            csrfToken: token.getAttribute('content')
        };
    }

    // Add loading state to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Завантаження...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 3000);
            }
        });
    });

    // Smooth scroll for anchor links
    const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
    smoothScrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm dialogs for dangerous actions
    const deleteButtons = document.querySelectorAll('button[onclick*="confirm"], form[action*="delete"] button[type="submit"]');
    deleteButtons.forEach(button => {
        if (!button.hasAttribute('onclick')) {
            button.addEventListener('click', function(e) {
                if (!confirm('Ви впевнені, що хочете видалити цей елемент?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // Image loading error handling
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Зображення+недоступне';
        });
    });

    // Product card animations
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

// Authentication modal functions (if modals are present)
function showAuthModal(type) {
    console.log(type);
    const modal = document.getElementById('authModal');
    const modalTitle = document.getElementById('authModalTitle');
    const loginForm = document.getElementById('login-form');
    
    modalTitle.textContent = 'Авторизація';
    loginForm.style.display = 'block';
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Add to cart with visual feedback
function addToCartWithFeedback(productId) {
    const button = event.target.closest('button');
    if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Додавання...';
        button.disabled = true;
        
        // The form will handle the actual submission
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-check me-2"></i>Додано!';
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1500);
        }, 500);
    }
}

// Quantity input validation
document.addEventListener('input', function(e) {
    if (e.target.type === 'number' && e.target.name === 'quantity') {
        const min = parseInt(e.target.min) || 1;
        const max = parseInt(e.target.max) || 100;
        let value = parseInt(e.target.value);
        
        if (value < min) {
            e.target.value = min;
        } else if (value > max) {
            e.target.value = max;
        }
    }
});

// Bootstrap tooltips initialization
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Utils
function formatPrice(price) {
    return new Intl.NumberFormat('uk-UA', {
        style: 'currency',
        currency: 'UAH',
        minimumFractionDigits: 0
    }).format(price);
}