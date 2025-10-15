<?php
/**
 * Custom Add to Cart Functionality for WooCommerce
 * 
 * This file contains PHP functions to be added to the theme's functions.php
 * to implement custom AJAX-powered "Add to Cart" functionality.
 * 
 * @package WestfaceProfessional
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue custom cart scripts and styles
 */
function westface_enqueue_custom_cart_assets() {
    // Enqueue custom cart styles
    wp_enqueue_style(
        'westface-custom-cart',
        get_template_directory_uri() . '/css/custom-cart-styles.css',
        array(),
        '1.0.0'
    );
    
    // Enqueue custom cart JavaScript
    wp_enqueue_script(
        'westface-custom-cart',
        get_template_directory_uri() . '/js/custom-cart-scripts.js',
        array('jquery', 'wc-add-to-cart', 'wc-cart-fragments'),
        '1.0.0',
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('westface-custom-cart', 'westface_cart_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('westface_cart_nonce'),
        'i18n' => array(
            'adding_to_cart' => __('Lisätään koriin...', 'westface-professional'),
            'added_to_cart' => __('Lisätty!', 'westface-professional'),
            'add_to_cart' => __('Lisää koriin', 'westface-professional'),
            'error_adding' => __('Virhe lisättäessä koriin', 'westface-professional'),
            'updating_cart' => __('Päivitetään...', 'westface-professional'),
            'removing_item' => __('Poistetaan...', 'westface-professional'),
            'cart_updated' => __('Ostoskori päivitetty', 'westface-professional'),
        )
    ));
}
add_action('wp_enqueue_scripts', 'westface_enqueue_custom_cart_assets');

/**
 * Replace default Add to Cart button on single product pages
 */
function westface_custom_add_to_cart_button() {
    global $product;
    
    if (!$product || !$product->is_purchasable()) {
        return;
    }
    
    // Remove default add to cart button
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    
    // Add custom add to cart button
    add_action('woocommerce_single_product_summary', 'westface_render_custom_add_to_cart_button', 30);
}
add_action('init', 'westface_custom_add_to_cart_button');

/**
 * Render custom Add to Cart button
 */
function westface_render_custom_add_to_cart_button() {
    global $product;
    
    if (!$product || !$product->is_purchasable()) {
        return;
    }
    
    $product_id = $product->get_id();
    $product_type = $product->get_type();
    $availability_html = wc_get_stock_html($product);
    
    ?>
    <div class="custom-add-to-cart-wrapper">
        <?php if ($product->is_in_stock()) : ?>
            <form class="custom-cart-form" method="post" enctype="multipart/form-data">
                <div class="quantity-selector">
                    <label for="quantity_<?php echo esc_attr($product_id); ?>"><?php esc_html_e('Määrä:', 'westface-professional'); ?></label>
                    <?php
                    woocommerce_quantity_input(array(
                        'min_value'   => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                        'max_value'   => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                        'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                        'input_id'    => 'quantity_' . $product_id,
                        'classes'     => array('input-text', 'qty', 'text'),
                    ));
                    ?>
                </div>
                
                <button type="submit" 
                        name="add-to-cart" 
                        value="<?php echo esc_attr($product_id); ?>" 
                        class="custom-add-to-cart-btn single_add_to_cart_button button alt"
                        data-product_id="<?php echo esc_attr($product_id); ?>"
                        data-product_sku="<?php echo esc_attr($product->get_sku()); ?>">
                    <span class="btn-text"><?php echo esc_html($product->single_add_to_cart_text()); ?></span>
                    <span class="btn-loading" style="display: none;">
                        <span class="loading-spinner"></span>
                        <?php esc_html_e('Lisätään koriin...', 'westface-professional'); ?>
                    </span>
                    <span class="btn-success" style="display: none;">
                        ✓ <?php esc_html_e('Lisätty!', 'westface-professional'); ?>
                    </span>
                </button>
                
                <?php do_action('woocommerce_after_add_to_cart_button'); ?>
            </form>
            
            <div class="add-to-cart-feedback" style="display: none;"></div>
            
        <?php else : ?>
            <div class="out-of-stock-message">
                <p class="stock out-of-stock"><?php esc_html_e('Tuote ei ole varastossa', 'westface-professional'); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($availability_html) : ?>
            <div class="product-availability">
                <?php echo wp_kses_post($availability_html); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Handle AJAX Add to Cart request
 */
function westface_ajax_add_to_cart() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'westface_cart_nonce')) {
        wp_die('Security check failed');
    }
    
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $variation_id = absint($_POST['variation_id']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);
    
    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);
        
        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
        }
        
        WC_AJAX::get_refreshed_fragments();
    } else {
        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
        );
        
        wp_send_json($data);
    }
}
add_action('wp_ajax_westface_add_to_cart', 'westface_ajax_add_to_cart');
add_action('wp_ajax_nopriv_westface_add_to_cart', 'westface_ajax_add_to_cart');

/**
 * Handle AJAX cart quantity update
 */
function westface_ajax_update_cart_quantity() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'westface_cart_nonce')) {
        wp_die('Security check failed');
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity <= 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
    }
    
    WC()->cart->calculate_totals();
    
    // Get updated cart fragments
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
    
    // Get cart totals
    ob_start();
    woocommerce_cart_totals();
    $cart_totals = ob_get_clean();
    
    // Get cart item subtotal
    $cart_item = WC()->cart->get_cart_item($cart_item_key);
    $subtotal = '';
    if ($cart_item) {
        $product = $cart_item['data'];
        $subtotal = WC()->cart->get_product_subtotal($product, $cart_item['quantity']);
    }
    
    wp_send_json_success(array(
        'fragments' => $fragments,
        'cart_totals' => $cart_totals,
        'subtotal' => $subtotal,
        'cart_hash' => WC()->cart->get_cart_hash(),
        'cart_count' => WC()->cart->get_cart_contents_count()
    ));
}
add_action('wp_ajax_westface_update_cart_quantity', 'westface_ajax_update_cart_quantity');
add_action('wp_ajax_nopriv_westface_update_cart_quantity', 'westface_ajax_update_cart_quantity');

/**
 * Handle AJAX cart item removal
 */
function westface_ajax_remove_cart_item() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'westface_cart_nonce')) {
        wp_die('Security check failed');
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    
    if (WC()->cart->remove_cart_item($cart_item_key)) {
        WC()->cart->calculate_totals();
        
        // Get updated cart fragments
        $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
        
        // Check if cart is empty
        $is_empty = WC()->cart->is_empty();
        
        // Get cart totals if not empty
        $cart_totals = '';
        if (!$is_empty) {
            ob_start();
            woocommerce_cart_totals();
            $cart_totals = ob_get_clean();
        }
        
        wp_send_json_success(array(
            'fragments' => $fragments,
            'cart_totals' => $cart_totals,
            'cart_hash' => WC()->cart->get_cart_hash(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'is_empty' => $is_empty,
            'redirect_url' => $is_empty ? wc_get_cart_url() : ''
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Tuotteen poistaminen epäonnistui', 'westface-professional')
        ));
    }
}
add_action('wp_ajax_westface_remove_cart_item', 'westface_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_westface_remove_cart_item', 'westface_ajax_remove_cart_item');

/**
 * Add custom cart fragments for header cart count update
 */
function westface_add_cart_fragments($fragments) {
    // Cart count fragment
    $cart_count = WC()->cart->get_cart_contents_count();
    $fragments['.cart-count'] = '<span class="cart-count">' . $cart_count . ' kpl</span>';
    
    // Cart total fragment
    $cart_total = WC()->cart->get_cart_total();
    $fragments['.cart-total'] = '<span class="cart-total">' . $cart_total . '</span>';
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'westface_add_cart_fragments');

/**
 * Customize WooCommerce messages for better UX
 */
function westface_custom_woocommerce_messages($message, $message_type) {
    if ($message_type === 'success' && strpos($message, 'added to your cart') !== false) {
        return __('Tuote lisätty ostoskoriin onnistuneesti!', 'westface-professional');
    }
    return $message;
}
add_filter('woocommerce_add_to_cart_message_html', 'westface_custom_woocommerce_messages', 10, 2);

/**
 * Add custom body classes for cart page styling
 */
function westface_cart_body_classes($classes) {
    if (is_cart()) {
        $classes[] = 'westface-custom-cart';
        
        if (WC()->cart->is_empty()) {
            $classes[] = 'cart-empty';
        } else {
            $classes[] = 'cart-populated';
        }
    }
    
    return $classes;
}
add_filter('body_class', 'westface_cart_body_classes');

/**
 * Disable WooCommerce default cart page redirect
 * to allow our custom AJAX functionality
 */
function westface_disable_cart_redirect() {
    remove_action('template_redirect', 'wc_redirect_to_cart_if_cart_page_is_checkout');
}
add_action('init', 'westface_disable_cart_redirect');


function westface_header_cart_widget() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();
    $cart_url = wc_get_cart_url();
    
    ?>
    <?php if ( function_exists( 'wc_get_cart_url' ) ) : 
                            $cart_url   = wc_get_cart_url();
                            $cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
                        ?>
                            <a href="<?php echo esc_url( $cart_url ); ?>" class="header-cart-link" aria-label="<?php esc_attr_e( 'Katso ostoskori', 'westface-child' ); ?>">
                                <i class="fa fa-bag-shopping" aria-hidden="true"></i>
                                <span class="cart-text"><?php esc_html_e( 'Ostoskori', 'westface-child' ); ?></span>
                                <span class="cart-count"><?php echo esc_html( $cart_count ); ?>  kpl</span>
                            </a>
                      
                        <?php endif; ?>
    <?php
}

/**
 * Add cart widget to header (call this function in your header.php)
 */
function westface_add_header_cart() {
    westface_header_cart_widget();
}

/**
 * Optimize cart page performance
 */
function westface_optimize_cart_page() {
    if (is_cart()) {
        // Remove unnecessary WooCommerce scripts on cart page
        wp_dequeue_script('wc-single-product');
        wp_dequeue_script('zoom');
        wp_dequeue_script('flexslider');
        wp_dequeue_script('photoswipe');
        wp_dequeue_script('photoswipe-ui-default');
        
        // Remove unnecessary styles
        wp_dequeue_style('photoswipe');
        wp_dequeue_style('photoswipe-default-skin');
    }
}
add_action('wp_enqueue_scripts', 'westface_optimize_cart_page', 100);

/**
 * Add structured data for cart page
 */
function westface_cart_structured_data() {
    if (!is_cart() || WC()->cart->is_empty()) {
        return;
    }
    
    $cart_items = array();
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        $cart_items[] = array(
            '@type' => 'Product',
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'offers' => array(
                '@type' => 'Offer',
                'price' => $product->get_price(),
                'priceCurrency' => get_woocommerce_currency()
            )
        );
    }
    
    $structured_data = array(
        '@context' => 'https://schema.org',
        '@type' => 'ShoppingCart',
        'potentialAction' => array(
            '@type' => 'CheckoutAction',
            'target' => wc_get_checkout_url()
        ),
        'offers' => $cart_items
    );
    
    echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
}
add_action('wp_head', 'westface_cart_structured_data');
?>
