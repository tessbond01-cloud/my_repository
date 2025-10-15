<?php
/**
 * Westface Professional Theme Functions
 * 
 * Professional WordPress theme with enhanced WooCommerce templates
 * featuring color swatches, two-column layouts, and modern design.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function westface_professional_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    add_theme_support('customize-selective-refresh-widgets');
    
    // WooCommerce support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'westface-professional'),
        'footer' => esc_html__('Footer Menu', 'westface-professional'),
    ));
}
add_action('after_setup_theme', 'westface_professional_setup');

// Убираем блок cross-sells (перекрёстные продажи) с корзины
function remove_cross_sells_from_cart() {
    remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
}
add_action( 'woocommerce_before_cart', 'remove_cross_sells_from_cart' );

/**
 * Enqueue theme styles and scripts
 */
function westface_professional_enqueue_styles() {
    // Enqueue Google Fonts first
    wp_enqueue_style(
        'westface-professional-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );
    
    // Enqueue Font Awesome
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        array(),
        '6.0.0'
    );
    
    // Enqueue main theme stylesheet
    wp_enqueue_style(
        'westface-professional-style',
        get_stylesheet_uri(),
        array('westface-professional-fonts', 'font-awesome'),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue header-footer styles
    wp_enqueue_style(
        'header-footer-styles',
        get_stylesheet_directory_uri() . '/css/header-footer-styles.css',
        array('westface-professional-style'),
        '1.0.0'
    );
    
    // Enqueue header-footer scripts
    wp_enqueue_script(
        'header-footer-scripts',
        get_stylesheet_directory_uri() . '/js/header-footer-scripts.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Enqueue navigation scripts
    wp_enqueue_script(
        'navigation-scripts',
        get_stylesheet_directory_uri() . '/js/navigation.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'westface_professional_enqueue_styles');

/**
 * Disable caching for theme development
 */
function westface_professional_disable_cache() {
    if (!is_admin()) {
        // Disable WordPress object cache
        wp_cache_flush();
        
        // Set no-cache headers
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Disable WP Super Cache if active
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        
        // Disable W3 Total Cache if active
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        
        // Disable WP Rocket cache if active
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
    }
}
add_action('init', 'westface_professional_disable_cache');

/**
 * Add cache-busting version to stylesheets and scripts
 */
function westface_professional_cache_buster($src) {
    if (strpos($src, get_template_directory_uri()) !== false || strpos($src, get_stylesheet_directory_uri()) !== false) {
        $src = add_query_arg('v', time(), $src);
    }
    return $src;
}
add_filter('style_loader_src', 'westface_professional_cache_buster');
add_filter('script_loader_src', 'westface_professional_cache_buster');

/**
 * Enqueue Professional WooCommerce Styles and Scripts
 */
function westface_professional_woocommerce_assets() {
    // Only load on WooCommerce pages
    if (is_woocommerce() || is_cart() || is_checkout() || is_shop() || is_product_category() || is_product() || is_product_tag()) {
        
        // Enqueue professional WooCommerce styles
        wp_enqueue_style(
            'professional-woocommerce-styles',
            get_stylesheet_directory_uri() . '/css/professional-woocommerce-styles.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Font Awesome for icons
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
            array(),
            '6.0.0'
        );
        
        // Enqueue professional WooCommerce JavaScript
        wp_enqueue_script(
            'professional-woocommerce-scripts',
            get_stylesheet_directory_uri() . '/js/professional-woocommerce-scripts.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Enqueue quote request JavaScript
        wp_enqueue_script(
            'quote-request-scripts',
            get_stylesheet_directory_uri() . '/js/quote-request.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Enqueue laminate quiz JavaScript
        wp_enqueue_script(
            'laminate-quiz-scripts',
            get_stylesheet_directory_uri() . '/js/laminate-quiz.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('professional-woocommerce-scripts', 'professional_woocommerce_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('professional_woocommerce_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'westface_professional_woocommerce_assets');

/**
 * Add theme support for WooCommerce
 */
function westface_professional_woocommerce_support() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'westface_professional_woocommerce_support');

/**
 * Remove default WooCommerce styles that conflict with professional design
 */
function westface_professional_remove_woocommerce_styles() {
    // Remove default WooCommerce styles on professional pages
    if (is_woocommerce() || is_shop() || is_product_category() || is_product()) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
    }
}
add_action('wp_enqueue_scripts', 'westface_professional_remove_woocommerce_styles', 99);

/**
 * Customize WooCommerce product loop columns
 */
function westface_professional_loop_columns() {
    return 3; // Display 3 products per row
}
add_filter('loop_shop_columns', 'westface_professional_loop_columns');

/**
 * Customize WooCommerce products per page
 */
function westface_professional_products_per_page() {
    return 12; // Display 12 products per page
}
add_filter('loop_shop_per_page', 'westface_professional_products_per_page');

/**
 * Add custom body classes for professional styling
 */
function westface_professional_body_classes($classes) {
    if (is_woocommerce()) {
        $classes[] = 'professional-woocommerce';
    }
    
    if (is_shop()) {
        $classes[] = 'professional-shop';
    }
    
    if (is_product()) {
        $classes[] = 'professional-single-product';
    }
    
    if (is_product_category()) {
        $classes[] = 'professional-product-category';
    }
    
    return $classes;
}
add_filter('body_class', 'westface_professional_body_classes');

/**
 * Customize WooCommerce breadcrumbs
 */
function westface_professional_breadcrumb_defaults($defaults) {
    $defaults['delimiter'] = ' <i class="fas fa-chevron-right"></i> ';
    $defaults['home'] = '<i class="fas fa-home"></i> ' . _x('Etusivu', 'breadcrumb', 'westface-professional');
    return $defaults;
}
add_filter('woocommerce_breadcrumb_defaults', 'westface_professional_breadcrumb_defaults');

/**
 * Add custom meta box for product color override
 */
function westface_professional_add_product_color_meta_box() {
    add_meta_box(
        'professional_product_color',
        __('Professional Color Swatch', 'westface-professional'),
        'westface_professional_product_color_meta_box_callback',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'westface_professional_add_product_color_meta_box');

/**
 * Product color meta box callback
 */
function westface_professional_product_color_meta_box_callback($post) {
    wp_nonce_field('professional_product_color_nonce', 'professional_product_color_nonce');
    $color = get_post_meta($post->ID, '_professional_product_color', true);
    ?>
    <p>
        <label for="professional_product_color"><?php _e('Custom Color (Hex Code):', 'westface-professional'); ?></label>
        <input type="color" id="professional_product_color" name="professional_product_color" value="<?php echo esc_attr($color ? $color : '#00d4aa'); ?>" />
    </p>
    <p class="description">
        <?php _e('Override automatic color detection with a custom color for this product.', 'westface-professional'); ?>
    </p>
    <?php
}

/**
 * Save product color meta
 */
function westface_professional_save_product_color_meta($post_id) {
    if (!isset($_POST['professional_product_color_nonce']) || !wp_verify_nonce($_POST['professional_product_color_nonce'], 'professional_product_color_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['professional_product_color'])) {
        update_post_meta($post_id, '_professional_product_color', sanitize_hex_color($_POST['professional_product_color']));
    }
}
add_action('save_post', 'westface_professional_save_product_color_meta');

/**
 * AJAX handler for quick view functionality
 */
function westface_professional_quick_view_handler() {
    check_ajax_referer('professional_woocommerce_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_die('Product not found');
    }
    
    // Return product data for quick view modal
    wp_send_json_success(array(
        'title' => $product->get_name(),
        'price' => $product->get_price_html(),
        'description' => $product->get_short_description(),
        'image' => wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium'),
        'permalink' => get_permalink($product_id)
    ));
}
add_action('wp_ajax_professional_quick_view', 'westface_professional_quick_view_handler');
add_action('wp_ajax_nopriv_professional_quick_view', 'westface_professional_quick_view_handler');

/**
 * AJAX handler for wishlist functionality
 */
function westface_professional_wishlist_handler() {
    check_ajax_referer('professional_woocommerce_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    $action = sanitize_text_field($_POST['wishlist_action']);
    
    // Get current wishlist from session/cookie
    $wishlist = isset($_SESSION['professional_wishlist']) ? $_SESSION['professional_wishlist'] : array();
    
    if ($action === 'add' && !in_array($product_id, $wishlist)) {
        $wishlist[] = $product_id;
        $message = __('Product added to wishlist', 'westface-professional');
    } elseif ($action === 'remove' && in_array($product_id, $wishlist)) {
        $wishlist = array_diff($wishlist, array($product_id));
        $message = __('Product removed from wishlist', 'westface-professional');
    }
    
    $_SESSION['professional_wishlist'] = $wishlist;
    
    wp_send_json_success(array(
        'message' => $message,
        'count' => count($wishlist)
    ));
}
add_action('wp_ajax_professional_wishlist', 'westface_professional_wishlist_handler');
add_action('wp_ajax_nopriv_professional_wishlist', 'westface_professional_wishlist_handler');

/**
 * Start session for wishlist functionality
 */
function westface_professional_start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'westface_professional_start_session');

/**
 * Add custom image sizes for professional templates
 */
function westface_professional_image_sizes() {
    add_image_size('professional-product-thumb', 300, 300, true);
    add_image_size('professional-product-large', 600, 600, true);
    add_image_size('professional-category-hero', 1200, 400, true);
}
add_action('after_setup_theme', 'westface_professional_image_sizes');

/**
 * Customize WooCommerce single product tabs
 */
function westface_professional_product_tabs($tabs) {
    // Rename tabs
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = __('Tuotekuvaus', 'westface-professional');
    }
    
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = __('Lisätiedot', 'westface-professional');
    }
    
    if (isset($tabs['reviews'])) {
        $tabs['reviews']['title'] = __('Arvostelut', 'westface-professional');
    }
    
    // Add custom tab
    $tabs['professional_info'] = array(
        'title' => __('Tekninen tieto', 'westface-professional'),
        'priority' => 25,
        'callback' => 'westface_professional_info_tab_content'
    );
    
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'westface_professional_product_tabs');

/**
 * Custom tab content
 */
function westface_professional_info_tab_content() {
    echo '<div class="professional-technical-info">';
    echo '<h3>' . __('Tekninen tieto', 'westface-professional') . '</h3>';
    echo '<p>' . __('Tässä voit lisätä teknisiä tietoja tuotteesta, kuten asennusohjeita, huolto-ohjeita tai muita tärkeitä tietoja.', 'westface-professional') . '</p>';
    echo '</div>';
}

/**
 * Remove WooCommerce generator tag
 */
remove_action('wp_head', 'wc_generator_tag');

/**
 * Optimize WooCommerce scripts loading
 */
function westface_professional_optimize_woocommerce_scripts() {
    // Remove WooCommerce scripts on non-WooCommerce pages
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-add-to-cart');
    }
}
add_action('wp_enqueue_scripts', 'westface_professional_optimize_woocommerce_scripts', 99);

/**
 * Add professional schema markup for products
 */
function westface_professional_product_schema() {
    if (is_product()) {
        global $product;
        
        // Ensure we have a valid product object
        if (!$product || !is_a($product, 'WC_Product')) {
            $product = wc_get_product(get_the_ID());
        }
        
        // Double check we have a valid product
        if (!$product || !is_a($product, 'WC_Product')) {
            return;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->get_name(),
            'description' => $product->get_short_description() ? $product->get_short_description() : $product->get_description(),
            'offers' => array(
                '@type' => 'Offer',
                'priceCurrency' => get_woocommerce_currency(),
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
            )
        );
        
        // Add SKU if available
        if ($product->get_sku()) {
            $schema['sku'] = $product->get_sku();
        }
        
        // Add price if available
        if ($product->get_price()) {
            $schema['offers']['price'] = $product->get_price();
        }
        
        // Add image if available
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            if ($image_url) {
                $schema['image'] = $image_url;
            }
        }
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'westface_professional_product_schema');

/**
 * Professional theme customizer options
 */
function westface_professional_customizer($wp_customize) {
    // Add professional section
    $wp_customize->add_section('professional_woocommerce', array(
        'title' => __('Professional WooCommerce', 'westface-professional'),
        'priority' => 120,
    ));
    
    // Primary color setting
    $wp_customize->add_setting('professional_primary_color', array(
        'default' => '#00d4aa',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'professional_primary_color', array(
        'label' => __('Primary Color', 'westface-professional'),
        'section' => 'professional_woocommerce',
        'settings' => 'professional_primary_color',
    )));
    
    // Products per page setting
    $wp_customize->add_setting('professional_products_per_page', array(
        'default' => 12,
        'sanitize_callback' => 'absint',
    ));
    
    $wp_customize->add_control('professional_products_per_page', array(
        'label' => __('Products Per Page', 'westface-professional'),
        'section' => 'professional_woocommerce',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 50,
        ),
    ));
}
add_action('customize_register', 'westface_professional_customizer');

/**
 * Output custom CSS based on customizer settings
 */
function westface_professional_custom_css() {
    $primary_color = get_theme_mod('professional_primary_color', '#00d4aa');
    
    // if ($primary_color !== '#00d4aa') {
    //     echo '<style type="text/css">';
    //     echo ':root { --primary-color: ' . esc_attr($primary_color) . '; }';
    //     echo '</style>';
    // }
}
add_action('wp_head', 'westface_professional_custom_css');

// Include custom plugins only if they exist
$quote_plugin_path = get_stylesheet_directory() . '/plugins/quote-request-plugin.php';
$quiz_plugin_path = get_stylesheet_directory() . '/plugins/laminate-quiz-plugin.php';

if (file_exists($quote_plugin_path)) {
    require_once $quote_plugin_path;
}

if (file_exists($quiz_plugin_path)) {
    require_once $quiz_plugin_path;
}

// Add shortcode for laminate quiz in sample request sections
function westface_add_sample_request_button() {
    echo '<div class="sample-request-section">';
    echo '<button class="btn btn-secondary sample-quiz-btn" onclick="showLaminateQuiz();">';
    echo '<i class="fas fa-puzzle-piece"></i> Tilaa mallipalat';
    echo '</button>';
    echo '</div>';
    
    // Add the quiz modal
    echo '<div id="laminate-quiz-modal" class="quiz-modal-overlay" style="display: none;">';
    echo '<div class="quiz-modal-content">';
    echo '<button class="quiz-modal-close" onclick="hideLaminateQuiz();">&times;</button>';
    echo do_shortcode('[laminate_quiz]');
    echo '</div>';
    echo '</div>';
    
    // Add JavaScript for quiz modal
    echo '<script>
    function showLaminateQuiz() {
        document.getElementById("laminate-quiz-modal").style.display = "flex";
    }
    function hideLaminateQuiz() {
        document.getElementById("laminate-quiz-modal").style.display = "none";
    }
    </script>';
    
   
}

// Add sample request button to single product pages
add_action('woocommerce_single_product_summary', 'westface_add_sample_request_button', 35);

// Ensure Font Awesome is loaded
function westface_enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
}
add_action('wp_enqueue_scripts', 'westface_enqueue_font_awesome');

// Add custom body classes for better styling
function westface_custom_body_classes($classes) {
    if (is_woocommerce()) {
        $classes[] = 'westface-woocommerce';
    }
    if (is_shop()) {
        $classes[] = 'westface-shop';
    }
    if (is_product()) {
        $classes[] = 'westface-single-product';
    }
    if (is_product_category()) {
        $classes[] = 'westface-product-category';
    }
    return $classes;
}
add_filter('body_class', 'westface_custom_body_classes');

// Customize WooCommerce pagination
function westface_woocommerce_pagination_args($args) {
    $args['prev_text'] = '<i class="fas fa-chevron-left"></i> Edellinen';
    $args['next_text'] = 'Seuraava <i class="fas fa-chevron-right"></i>';
    return $args;
}
add_filter('woocommerce_pagination_args', 'westface_woocommerce_pagination_args');

// Custom AJAX handler for add to cart (moved from content-single-product.php)
add_action('wp_ajax_custom_add_to_cart', 'custom_add_to_cart_handler');
add_action('wp_ajax_nopriv_custom_add_to_cart', 'custom_add_to_cart_handler');
function custom_add_to_cart_handler() {
    // Debug: log incoming POST data
    error_log('DEBUG custom_add_to_cart_handler called');
    error_log('POST: ' . print_r($_POST, true));
    if (!isset($_POST['product_id'], $_POST['variation_id'], $_POST['quantity'])) {
        error_log('DEBUG missing data');
        wp_send_json_error(['message' => 'Missing data']);
    }
    $product_id = intval($_POST['product_id']);
    $variation_id = intval($_POST['variation_id']);
    $quantity = intval($_POST['quantity']);
    $attributes = isset($_POST['attributes']) ? (array) $_POST['attributes'] : array();
    error_log("DEBUG product_id=$product_id, variation_id=$variation_id, quantity=$quantity");
    $cart = WC()->cart;
    if (!$cart) {
        error_log('DEBUG WC()->cart not available');
        wp_send_json_error(['message' => 'Cart not available']);
    }
    $result = $cart->add_to_cart($product_id, $quantity, $variation_id, $attributes);
    error_log('DEBUG add_to_cart result: ' . print_r($result, true));
    if ($result) {
        // Return fragments for cart update
        WC_AJAX::get_refreshed_fragments();
    } else {
        error_log('DEBUG Could not add to cart');
        wp_send_json_error(['message' => 'Could not add to cart']);
    }
    wp_die();
}

// Add this to the end of your theme's functions.php file
require_once get_template_directory() . '/custom-add-to-cart-functions.php';
require_once get_template_directory() . '/wf-ajax-functions.php';



/* End of file */

