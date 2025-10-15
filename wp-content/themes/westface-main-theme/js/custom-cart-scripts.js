/**
 * Custom Cart Scripts for WooCommerce
 * 
 * Handles AJAX functionality for:
 * - Custom Add to Cart button
 * - Cart quantity updates
 * - Cart item removal
 * - Cart fragments updates
 * 
 * @package WestfaceProfessional
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        initCustomCart();
    });

    /**
     * Initialize all custom cart functionality
     */
    function initCustomCart() {
        initCustomAddToCart();
        initCartQuantityControls();
        initCartItemRemoval();
        initCartUpdates();
        initLoadingStates();
        initNotifications();
    }

    /**
     * Custom Add to Cart functionality for single product pages
     */
    function initCustomAddToCart() {
        $(document).on('click', '.custom-add-to-cart-btn', function (e) {
            e.preventDefault();

            const $button = $(this);
            const $form = $button.closest('.custom-cart-form');
            const productId = $button.data('product_id');
            const quantity = $form.find('input[name="quantity"]').val() || 1;
            const variationId = $form.find('input[name="variation_id"]').val() || 0;

            // Prevent double submission
            if ($button.hasClass('loading')) {
                return false;
            }

            // Show loading state
            showButtonLoading($button);

            // Prepare AJAX data
            const data = {
                action: 'westface_add_to_cart',
                product_id: productId,
                quantity: quantity,
                variation_id: variationId,
                nonce: westface_cart_ajax.nonce
            };

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: westface_cart_ajax.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        showButtonError($button);
                        showNotification(westface_cart_ajax.i18n.error_adding, 'error');
                        
                        // Redirect to product page if needed
                        if (response.product_url) {
                            window.location = response.product_url;
                        }
                    } else {
                        showButtonSuccess($button);
                        showNotification(westface_cart_ajax.i18n.added_to_cart, 'success');
                        
                        // Update cart fragments
                        if (response.fragments) {
                            updateCartFragments(response.fragments);
                        }
                        
                        // Reset button after delay
                        setTimeout(function () {
                            resetButton($button);
                        }, 2000);
                    }
                },
                error: function () {
                    showButtonError($button);
                    showNotification(westface_cart_ajax.i18n.error_adding, 'error');
                    
                    setTimeout(function () {
                        resetButton($button);
                    }, 2000);
                }
            });

            return false;
        });
    }

    /**
     * Cart quantity controls (+/- buttons and direct input)
     */
    function initCartQuantityControls() {
        // Plus button
        $(document).on('click', '.qty-plus', function (e) {
            e.preventDefault();
            
            const $button = $(this);
            const cartKey = $button.data('cart-key');
            const $qtyInput = $button.closest('.quantity-wrapper').find('.qty');
            const currentQty = parseInt($qtyInput.val()) || 0;
            const newQty = currentQty + 1;
            
            updateCartQuantity(cartKey, newQty, $qtyInput);
        });

        // Minus button
        $(document).on('click', '.qty-minus', function (e) {
            e.preventDefault();
            
            const $button = $(this);
            const cartKey = $button.data('cart-key');
            const $qtyInput = $button.closest('.quantity-wrapper').find('.qty');
            const currentQty = parseInt($qtyInput.val()) || 0;
            const newQty = Math.max(0, currentQty - 1);
            
            updateCartQuantity(cartKey, newQty, $qtyInput);
        });

        // Direct quantity input change
        $(document).on('change', '.ajax-quantity', function () {
            const $input = $(this);
            const cartKey = $input.closest('tr').find('.qty-btn').data('cart-key');
            const newQty = parseInt($input.val()) || 0;
            
            updateCartQuantity(cartKey, newQty, $input);
        });
    }

    /**
     * Cart item removal functionality
     */
    function initCartItemRemoval() {
        $(document).on('click', '.ajax-remove-item', function (e) {
            e.preventDefault();

            const $link = $(this);
            const cartKey = $link.data('cart_item_key');
            const $row = $link.closest('tr');

            // Show confirmation
            if (!confirm('Haluatko varmasti poistaa tämän tuotteen ostoskorista?')) {
                return false;
            }

            // Show loading state
            $row.addClass('item-removing');
            showLoadingOverlay();

            // Prepare AJAX data
            const data = {
                action: 'westface_remove_cart_item',
                cart_item_key: cartKey,
                nonce: westface_cart_ajax.nonce
            };

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: westface_cart_ajax.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    hideLoadingOverlay();
                    
                    if (response.success) {
                        // Remove the row with animation
                        $row.fadeOut(300, function () {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if (response.data.is_empty) {
                                // Reload page to show empty cart state
                                window.location.reload();
                            } else {
                                // Update cart totals
                                if (response.data.cart_totals) {
                                    $('.cart-collaterals').html(response.data.cart_totals);
                                }
                                
                                // Update cart fragments
                                if (response.data.fragments) {
                                    updateCartFragments(response.data.fragments);
                                }
                            }
                        });
                        
                        showNotification('Tuote poistettu ostoskorista', 'success');
                    } else {
                        $row.removeClass('item-removing');
                        showNotification(response.data.message || 'Virhe poistettaessa tuotetta', 'error');
                    }
                },
                error: function () {
                    hideLoadingOverlay();
                    $row.removeClass('item-removing');
                    showNotification('Virhe poistettaessa tuotetta', 'error');
                }
            });

            return false;
        });
    }

    /**
     * Update cart quantity via AJAX
     */
    function updateCartQuantity(cartKey, quantity, $input) {
        const $row = $input.closest('tr');
        
        // Show loading state
        $row.addClass('quantity-updating');
        
        // Prepare AJAX data
        const data = {
            action: 'westface_update_cart_quantity',
            cart_item_key: cartKey,
            quantity: quantity,
            nonce: westface_cart_ajax.nonce
        };

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: westface_cart_ajax.ajax_url,
            data: data,
            dataType: 'json',
            success: function (response) {
                $row.removeClass('quantity-updating');
                
                if (response.success) {
                    // Update quantity input
                    $input.val(quantity);
                    
                    // If quantity is 0, remove the row
                    if (quantity === 0) {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if ($('.shop_table tbody tr').length <= 1) {
                                window.location.reload();
                            }
                        });
                    } else {
                        // Update subtotal for this item
                        if (response.data.subtotal) {
                            $row.find('.product-subtotal').html(response.data.subtotal);
                        }
                    }
                    
                    // Update cart totals
                    if (response.data.cart_totals) {
                        $('.cart-collaterals').html(response.data.cart_totals);
                    }
                    
                    // Update cart fragments
                    if (response.data.fragments) {
                        updateCartFragments(response.data.fragments);
                    }
                    
                    showNotification(westface_cart_ajax.i18n.cart_updated, 'success');
                } else {
                    showNotification('Virhe päivitettäessä määrää', 'error');
                }
            },
            error: function () {
                $row.removeClass('quantity-updating');
                showNotification('Virhe päivitettäessä määrää', 'error');
            }
        });
    }

    /**
     * Handle general cart updates (coupon application, etc.)
     */
    function initCartUpdates() {
        $(document).on('submit', '.woocommerce-cart-form', function (e) {
            const $form = $(this);
            const $updateButton = $form.find('button[name="update_cart"]');
            
            if ($updateButton.length) {
                $updateButton.prop('disabled', true).text(westface_cart_ajax.i18n.updating_cart);
                showLoadingOverlay();
            }
        });
    }

    /**
     * Button state management
     */
    function showButtonLoading($button) {
        $button.addClass('loading').prop('disabled', true);
        $button.find('.btn-text').hide();
        $button.find('.btn-loading').show();
        $button.find('.btn-success').hide();
    }

    function showButtonSuccess($button) {
        $button.removeClass('loading').addClass('success');
        $button.find('.btn-text').hide();
        $button.find('.btn-loading').hide();
        $button.find('.btn-success').show();
    }

    function showButtonError($button) {
        $button.removeClass('loading').addClass('error');
        $button.find('.btn-text').show();
        $button.find('.btn-loading').hide();
        $button.find('.btn-success').hide();
    }

    function resetButton($button) {
        $button.removeClass('loading success error').prop('disabled', false);
        $button.find('.btn-text').show();
        $button.find('.btn-loading').hide();
        $button.find('.btn-success').hide();
    }

    /**
     * Loading states management
     */
    function initLoadingStates() {
        // Show loading overlay for form submissions
        $(document).on('submit', 'form.woocommerce-cart-form', function () {
            showLoadingOverlay();
        });
    }

    function showLoadingOverlay() {
        $('#cart-loading-overlay').fadeIn(200);
    }

    function hideLoadingOverlay() {
        $('#cart-loading-overlay').fadeOut(200);
    }

    /**
     * Update cart fragments (header cart count, etc.)
     */
    function updateCartFragments(fragments) {
        if (fragments) {
            $.each(fragments, function (key, value) {
                $(key).replaceWith(value);
            });
        }
        
        // Trigger cart fragments updated event
        $(document.body).trigger('wc_fragments_refreshed');
    }

    /**
     * Notification system
     */
    function initNotifications() {
        // Create notification container if it doesn't exist
        if (!$('.cart-notifications').length) {
            $('body').append('<div class="cart-notifications"></div>');
        }
    }

    function showNotification(message, type = 'info', duration = 4000) {
        const $notification = $('<div class="cart-notification cart-notification-' + type + '">' + message + '</div>');
        
        $('.cart-notifications').append($notification);
        
        // Show notification
        $notification.fadeIn(300);
        
        // Auto hide after duration
        setTimeout(function () {
            $notification.fadeOut(300, function () {
                $(this).remove();
            });
        }, duration);
        
        // Allow manual close
        $notification.on('click', function () {
            $(this).fadeOut(300, function () {
                $(this).remove();
            });
        });
    }

    /**
     * Handle WooCommerce cart fragments refresh
     */
    $(document.body).on('wc_fragments_refreshed', function () {
        // Re-initialize any dynamic elements after fragments update
        console.log('Cart fragments refreshed');
    });

    /**
     * Handle page visibility change to refresh cart when user returns
     */
    $(document).on('visibilitychange', function () {
        if (!document.hidden && $('.woocommerce-cart').length) {
            // Refresh cart fragments when user returns to cart page
            $(document.body).trigger('wc_fragment_refresh');
        }
    });

    /**
     * Keyboard shortcuts for cart page
     */
    $(document).on('keydown', function (e) {
        // ESC key to close notifications
        if (e.keyCode === 27) {
            $('.cart-notification').fadeOut(300, function () {
                $(this).remove();
            });
        }
    });

    /**
     * Smooth scrolling for cart actions
     */
    function smoothScrollTo($element, offset = 0) {
        if ($element.length) {
            $('html, body').animate({
                scrollTop: $element.offset().top - offset
            }, 500);
        }
    }

    /**
     * Handle cart form validation
     */
    $(document).on('submit', '.custom-cart-form', function (e) {
        const $form = $(this);
        const $qtyInput = $form.find('input[name="quantity"]');
        const quantity = parseInt($qtyInput.val());
        
        // Validate quantity
        if (isNaN(quantity) || quantity < 1) {
            e.preventDefault();
            $qtyInput.focus();
            showNotification('Syötä kelvollinen määrä', 'error');
            return false;
        }
    });

    /**
     * Auto-save cart changes (debounced)
     */
    let cartUpdateTimeout;
    
    function debounceCartUpdate(callback, delay = 1000) {
        clearTimeout(cartUpdateTimeout);
        cartUpdateTimeout = setTimeout(callback, delay);
    }

    /**
     * Handle browser back/forward navigation
     */
    $(window).on('popstate', function () {
        if ($('.woocommerce-cart').length) {
            // Refresh cart when navigating back to cart page
            window.location.reload();
        }
    });

    /**
     * Accessibility improvements
     */
    function initAccessibility() {
        // Add ARIA labels to quantity controls
        $('.qty-btn').each(function () {
            const $btn = $(this);
            const action = $btn.hasClass('qty-plus') ? 'Lisää määrää' : 'Vähennä määrää';
            $btn.attr('aria-label', action);
        });
        
        // Add ARIA labels to remove buttons
        $('.ajax-remove-item').each(function () {
            $(this).attr('aria-label', 'Poista tuote ostoskorista');
        });
    }

    // Initialize accessibility features
    initAccessibility();

})(jQuery);
