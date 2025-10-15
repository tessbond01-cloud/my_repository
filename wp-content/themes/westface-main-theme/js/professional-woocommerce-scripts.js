/**
 * Professional WooCommerce Scripts
 * 
 * Enhanced functionality for professional WooCommerce templates
 * including quick view, wishlist, and interactive features.
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        initProfessionalWooCommerce();
    });

    /**
     * Initialize all professional WooCommerce functionality
     */
    function initProfessionalWooCommerce() {
        initViewToggle();
        initQuickView();
        initWishlist();
        initSampleRequest();
        initProductImageHover();
        initSmoothScrolling();
        initNotifications();
        initMobileMenu();
        initLoadingStates();
    }

    /**
     * View toggle functionality (Grid/List view)
     */
    function initViewToggle() {
        $('.view-btn').on('click', function (e) {
            e.preventDefault();

            const view = $(this).data('view');
            const $container = $('.professional-products-container');

            // Update active button
            $('.view-btn').removeClass('active');
            $(this).addClass('active');

            // Update container view
            $container.attr('data-view', view);

            // Save preference to localStorage
            localStorage.setItem('professional_view_preference', view);

            // Animate transition
            $container.addClass('view-changing');
            setTimeout(function () {
                $container.removeClass('view-changing');
            }, 300);
        });

        // Load saved view preference
        const savedView = localStorage.getItem('professional_view_preference');
        if (savedView) {
            $('.view-btn[data-view="' + savedView + '"]').trigger('click');
        }
    }

    /**
     * Quick view functionality
     */
    function initQuickView() {
        $(document).on('click', '.quick-view-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this);
            const productId = $button.data('product-id');

            // Show loading state
            $button.addClass('loading');

            // AJAX request for product data
            $.ajax({
                url: professional_woocommerce_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'professional_quick_view',
                    product_id: productId,
                    nonce: professional_woocommerce_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        showQuickViewModal(response.data);
                    } else {
                        showNotification('Error loading product', 'error');
                    }
                },
                error: function () {
                    showNotification('Error loading product', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                }
            });
        });
    }

    /**
     * Show quick view modal
     */
    function showQuickViewModal(productData) {
        const modalHtml = `
            <div class="professional-quick-view-overlay">
                <div class="professional-quick-view-modal">
                    <button class="quick-view-close">&times;</button>
                    <div class="quick-view-content">
                        <div class="quick-view-image">
                            <img src="${productData.image[0]}" alt="${productData.title}" />
                        </div>
                        <div class="quick-view-details">
                            <h3>${productData.title}</h3>
                            <div class="quick-view-price">${productData.price}</div>
                            <div class="quick-view-description">${productData.description}</div>
                            <a href="${productData.permalink}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        $('.professional-quick-view-overlay').fadeIn(300);

        // Close modal handlers
        $('.quick-view-close, .professional-quick-view-overlay').on('click', function (e) {
            if (e.target === this) {
                $('.professional-quick-view-overlay').fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });

        // ESC key to close
        $(document).on('keyup.quickview', function (e) {
            if (e.keyCode === 27) {
                $('.professional-quick-view-overlay').fadeOut(300, function () {
                    $(this).remove();
                });
                $(document).off('keyup.quickview');
            }
        });
    }

    /**
     * Wishlist functionality
     */
    function initWishlist() {
        $(document).on('click', '.wishlist-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $button = $(this);
            const productId = $button.data('product-id');
            const isActive = $button.hasClass('active');
            const action = isActive ? 'remove' : 'add';

            // Show loading state
            $button.addClass('loading');

            // AJAX request
            $.ajax({
                url: professional_woocommerce_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'professional_wishlist',
                    product_id: productId,
                    wishlist_action: action,
                    nonce: professional_woocommerce_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $button.toggleClass('active');
                        showNotification(response.data.message, 'success');
                        updateWishlistCount(response.data.count);
                    } else {
                        showNotification('Error updating wishlist', 'error');
                    }
                },
                error: function () {
                    showNotification('Error updating wishlist', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                }
            });
        });
    }

    /**
     * Update wishlist count in header
     */
    function updateWishlistCount(count) {
        $('.wishlist-count').text(count);
        if (count > 0) {
            $('.wishlist-count').addClass('has-items');
        } else {
            $('.wishlist-count').removeClass('has-items');
        }
    }

    /**
     * Sample request functionality
     */
    function initSampleRequest() {
        $(document).on('click', '.sample-request-btn', function (e) {
            e.preventDefault();

            const $button = $(this);

            // Show sample request modal or redirect
            showSampleRequestModal();
        });
    }

    /**
     * Show sample request modal
     */
    function showSampleRequestModal() {
        const modalHtml = `
            <div class="professional-sample-request-overlay">
                <div class="professional-sample-request-modal">
                    <button class="sample-request-close">&times;</button>
                    <div class="sample-request-content">
                        <h3><i class="fas fa-cube"></i> Tilaa mallipalat</h3>
                        <p>Täytä tiedot alla, niin lähetämme sinulle mallipalat arvioitavaksi.</p>
                        <form class="sample-request-form">
                            <div class="form-row">
                                <input type="text" name="name" placeholder="Nimi *" required>
                                <input type="email" name="email" placeholder="Sähköposti *" required>
                            </div>
                            <div class="form-row">
                                <input type="tel" name="phone" placeholder="Puhelinnumero">
                                <input type="text" name="company" placeholder="Yritys">
                            </div>
                            <textarea name="message" placeholder="Lisätiedot (valinnainen)" rows="3"></textarea>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Lähetä pyyntö
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        `;

        $('body').append(modalHtml);
        $('.professional-sample-request-overlay').fadeIn(300);

        // Close modal handlers
        $('.sample-request-close, .professional-sample-request-overlay').on('click', function (e) {
            if (e.target === this) {
                $('.professional-sample-request-overlay').fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });

        // Form submission
        $('.sample-request-form').on('submit', function (e) {
            e.preventDefault();
            showNotification('Pyyntö lähetetty! Otamme yhteyttä pian.', 'success');
            $('.professional-sample-request-overlay').fadeOut(300, function () {
                $(this).remove();
            });
        });
    }

    /**
     * Product image hover effects
     */
    function initProductImageHover() {
        $('.professional-product-card').hover(
            function () {
                $(this).addClass('hovered');
            },
            function () {
                $(this).removeClass('hovered');
            }
        );
    }

    /**
     * Smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        $('a[href^="#"]').on('click', function (e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
    }

    /**
     * Notification system
     */
    function initNotifications() {
        // Create notification container if it doesn't exist
        if (!$('.professional-notifications').length) {
            $('body').append('<div class="professional-notifications"></div>');
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info', duration = 4000) {
        const notificationHtml = `
            <div class="professional-notification ${type}">
                <div class="notification-content">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close">&times;</button>
            </div>
        `;

        const $notification = $(notificationHtml);
        $('.professional-notifications').append($notification);

        // Animate in
        setTimeout(function () {
            $notification.addClass('show');
        }, 100);

        // Auto remove
        setTimeout(function () {
            removeNotification($notification);
        }, duration);

        // Manual close
        $notification.find('.notification-close').on('click', function () {
            removeNotification($notification);
        });
    }

    /**
     * Remove notification
     */
    function removeNotification($notification) {
        $notification.removeClass('show');
        setTimeout(function () {
            $notification.remove();
        }, 300);
    }

    /**
     * Get notification icon based on type
     */
    function getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Mobile menu functionality
     */
    function initMobileMenu() {
        $('.menu-toggle').on('click', function () {
            $(this).toggleClass('active');
            $('.main-menu-container').toggleClass('active');
            $('body').toggleClass('menu-open');
        });

        // Close menu when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.main-navigation').length) {
                $('.menu-toggle').removeClass('active');
                $('.main-menu-container').removeClass('active');
                $('body').removeClass('menu-open');
            }
        });
    }

    /**
     * Loading states for buttons
     */
    function initLoadingStates() {
        // Add loading state to add to cart buttons
        $(document).on('click', '.single_add_to_cart_button', function () {
            $(this).addClass('loading');
        });

        // Remove loading state after AJAX complete
        $(document).ajaxComplete(function () {
            $('.loading').removeClass('loading');
        });
    }

    /**
     * Lazy loading for images
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(function (img) {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Scroll to top functionality
     */
    function initScrollToTop() {
        // Add scroll to top button
        $('body').append('<button class="scroll-to-top"><i class="fas fa-arrow-up"></i></button>');

        // Show/hide based on scroll position
        $(window).scroll(function () {
            if ($(this).scrollTop() > 300) {
                $('.scroll-to-top').addClass('show');
            } else {
                $('.scroll-to-top').removeClass('show');
            }
        });

        // Scroll to top on click
        $('.scroll-to-top').on('click', function () {
            $('html, body').animate({ scrollTop: 0 }, 800);
        });
    }

    /**
     * Initialize on window load
     */
    $(window).on('load', function () {
        initLazyLoading();
        initScrollToTop();
    });

    /**
     * Handle window resize
     */
    $(window).on('resize', function () {
        // Close mobile menu on resize
        if ($(window).width() > 768) {
            $('.menu-toggle').removeClass('active');
            $('.main-menu-container').removeClass('active');
            $('body').removeClass('menu-open');
        }
    });

})(jQuery);

