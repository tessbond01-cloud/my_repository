/**
 * Header and Footer JavaScript
 * 
 * Interactive functionality for header and footer elements
 * 
 * Version: 1.0
 * Author: Custom WooCommerce Implementation
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================================================
    // MOBILE MENU TOGGLE
    // ==========================================================================
    
    const menuToggle = document.querySelector('.menu-toggle');
    const mainMenuContainer = document.querySelector('.main-menu-container');
    
    if (menuToggle && mainMenuContainer) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle aria-expanded
            this.setAttribute('aria-expanded', !isExpanded);
            
            // Toggle active class
            mainMenuContainer.classList.toggle('active');
            
            // Toggle menu icon
            const icon = this.querySelector('i');
            if (icon) {
                if (isExpanded) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                } else {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                }
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !mainMenuContainer.contains(e.target)) {
                mainMenuContainer.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mainMenuContainer.classList.contains('active')) {
                mainMenuContainer.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.focus();
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }
    
    // ==========================================================================
    // HEADER SCROLL EFFECT
    // ==========================================================================
    
    const header = document.querySelector('.site-header');
    let lastScrollTop = 0;
    
    if (header) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add scrolled class when scrolling down
            if (scrollTop > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    // ==========================================================================
    // SMOOTH SCROLLING FOR ANCHOR LINKS
    // ==========================================================================
    
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Skip if it's just "#" or empty
            if (href === '#' || href === '') return;
            
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (mainMenuContainer && mainMenuContainer.classList.contains('active')) {
                    mainMenuContainer.classList.remove('active');
                    if (menuToggle) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                        const icon = menuToggle.querySelector('i');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    }
                }
            }
        });
    });
    
    // ==========================================================================
    // NEWSLETTER FORM HANDLING
    // ==========================================================================
    
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (emailInput && submitButton) {
                const email = emailInput.value.trim();
                
                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    showNotification('Please enter a valid email address.', 'error');
                    return;
                }
                
                // Disable button and show loading state
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                submitButton.disabled = true;
                
                // Simulate form submission (replace with actual AJAX call)
                setTimeout(() => {
                    // Reset form
                    emailInput.value = '';
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                    
                    // Show success message
                    showNotification('Thank you for subscribing to our newsletter!', 'success');
                }, 1500);
            }
        });
    }
    
    // ==========================================================================
    // NOTIFICATION SYSTEM
    // ==========================================================================
    
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" aria-label="Close notification">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#00d4aa' : type === 'error' ? '#e74c3c' : '#3498db'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            font-size: 0.9rem;
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Close button functionality
        const closeButton = notification.querySelector('.notification-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            });
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
    
    // ==========================================================================
    // ACCESSIBILITY IMPROVEMENTS
    // ==========================================================================
    
    // Keyboard navigation for dropdowns
    const dropdownItems = document.querySelectorAll('.menu-item-has-children');
    
    dropdownItems.forEach(item => {
        const link = item.querySelector('a');
        const submenu = item.querySelector('.sub-menu');
        
        if (link && submenu) {
            // Handle keyboard events
            link.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    
                    // Toggle submenu visibility
                    const isVisible = submenu.style.opacity === '1';
                    submenu.style.opacity = isVisible ? '0' : '1';
                    submenu.style.visibility = isVisible ? 'hidden' : 'visible';
                    submenu.style.transform = isVisible ? 'translateY(-10px)' : 'translateY(0)';
                    
                    // Focus first submenu item if opening
                    if (!isVisible) {
                        const firstSubmenuLink = submenu.querySelector('a');
                        if (firstSubmenuLink) {
                            setTimeout(() => firstSubmenuLink.focus(), 100);
                        }
                    }
                }
            });
        }
    });
    
    // ==========================================================================
    // PERFORMANCE OPTIMIZATIONS
    // ==========================================================================
    
    // Throttle scroll events
    let scrollTimeout;
    const originalScrollHandler = window.onscroll;
    
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        
        scrollTimeout = setTimeout(() => {
            if (originalScrollHandler) {
                originalScrollHandler();
            }
        }, 16); // ~60fps
    });
    
    // Preload critical images
    const criticalImages = [
        // Add paths to critical images here
    ];
    
    criticalImages.forEach(src => {
        const img = new Image();
        img.src = src;
    });
    
    // ==========================================================================
    // SOCIAL MEDIA SHARING (Optional)
    // ==========================================================================
    
    const socialLinks = document.querySelectorAll('.footer-social a');
    
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // If it's a sharing link, open in popup
            if (href && (href.includes('facebook.com/sharer') || 
                        href.includes('twitter.com/intent') || 
                        href.includes('linkedin.com/sharing'))) {
                e.preventDefault();
                
                const popup = window.open(
                    href,
                    'social-share',
                    'width=600,height=400,scrollbars=yes,resizable=yes'
                );
                
                if (popup) {
                    popup.focus();
                }
            }
        });
    });
    
    // ==========================================================================
    // INITIALIZATION COMPLETE
    // ==========================================================================
    
    console.log('Header and Footer scripts initialized successfully');
});

