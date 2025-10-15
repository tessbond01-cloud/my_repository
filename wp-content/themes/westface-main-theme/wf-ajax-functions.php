<?php
/**
 * REFACTORED WooCommerce AJAX Product Filtering Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REFACTORED AJAX handler for product filtering
 */
function wf_ajax_filter_products() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wf_filter_nonce')) {
        wp_send_json_error('Security check failed.', 403);
        return;
    }

    // Sanitize all POST data
    $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    // --- 1. Scope Filtering to Current Category ---
    $current_category_id = isset($post_data['current_category_id']) ? intval($post_data['current_category_id']) : 0;

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 12, // Or your desired number
        'paged'          => isset($post_data['paged']) ? intval($post_data['paged']) : 1,
        'meta_query'     => ['relation' => 'AND'],
        'tax_query'      => ['relation' => 'AND'],
    ];

    if ($current_category_id > 0) {
        $args['tax_query'][] = [
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $current_category_id,
            'include_children' => true,
        ];
    }

    // --- 3. & 4. Multi-Select AND Logic for Color Filters ---
    $color_meta_query = ['relation' => 'AND'];

    // Handle chromatic colors
    if (!empty($post_data['_color_category']) && is_array($post_data['_color_category'])) {
        $color_meta_query[] = [
            'key'     => '_color_category',
            'value'   => $post_data['_color_category'],
            'compare' => 'IN',
        ];
    }

    // Handle neutral colors with blackness range
    if (!empty($post_data['blackness_preset']) && is_array($post_data['blackness_preset'])) {
        $blackness_ranges = [
            'valkoinen'      => [0, 25],
            'vaaleanharmaa'  => [26, 49],
            'tummanharmaa'   => [50, 74],
            'musta'          => [75, 100],
        ];

        $neutral_query = ['relation' => 'OR'];
        foreach ($post_data['blackness_preset'] as $preset) {
            if (isset($blackness_ranges[$preset])) {
                $neutral_query[] = [
                    'key'     => '_ncs_blackness',
                    'value'   => $blackness_ranges[$preset],
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ];
            }
        }

        if (count($neutral_query) > 1) {
            $color_meta_query[] = $neutral_query;
        }
    }

    if (count($color_meta_query) > 1) {
        $args['meta_query'][] = $color_meta_query;
    }
    
    // (Keep other filters from original function if they are still needed)

    $products_query = new WP_Query($args);

    ob_start();
    if ($products_query->have_posts()) {
        woocommerce_product_loop_start();
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }
        woocommerce_product_loop_end();
    } else {
        echo '<div class="wf-no-products-found"><p>No products found matching your selection.</p></div>';
    }
    $products_html = ob_get_clean();

    wp_reset_postdata();

    // --- 2. Update Visible Product Count ---
    wp_send_json_success([
        'products'      => $products_html,
        'visible_count' => $products_query->found_posts,
    ]);
}

// Hook AJAX actions
add_action('wp_ajax_wf_filter_products', 'wf_ajax_filter_products');
add_action('wp_ajax_nopriv_wf_filter_products', 'wf_ajax_filter_products');

/**
 * Enqueue scripts and localize AJAX data
 */
function wf_enqueue_filter_scripts() {
    if (is_shop() || is_product_category() || is_product_taxonomy()) {
        wp_enqueue_script(
            'wf-product-filters',
            get_template_directory_uri() . '/js/wf-product-filters.js',
            ['jquery'],
            '1.0.1', // Incremented version
            true
        );

        wp_localize_script('wf-product-filters', 'wf_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wf_filter_nonce'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'wf_enqueue_filter_scripts');

?>
