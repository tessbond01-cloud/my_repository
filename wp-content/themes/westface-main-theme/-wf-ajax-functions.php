<?php
/**
 * WooCommerce AJAX Product Filtering Functions
 * 
 * This file contains the AJAX handlers for the enhanced product filtering system.
 * Add this code to your theme's functions.php file.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for product filtering
 */
function wf_ajax_filter_products() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'wf_filter_nonce')) {
        wp_die('Security check failed');
    }

    // Get filter parameters
    $filters = array();
    
    // Category filters
    if (!empty($_POST['material'])) {
        $filters['material'] = array_map('intval', $_POST['material']);
    }
    
    if (!empty($_POST['surface_material'])) {
        $filters['surface_material'] = array_map('intval', $_POST['surface_material']);
    }

    // Meta field filters
    $meta_fields = array(
        'brand',
        'mallisto',
        '_ncs_color_taka',
        '_impact_resistance',
        '_fire_rating',
        '_surface_pattern',
        '_surface_finish',
        'koot'
    );

    foreach ($meta_fields as $field) {
        if (!empty($_POST[$field])) {
            $filters[$field] = array_map('sanitize_text_field', $_POST[$field]);
        }
    }

    // Handle _color_category filter (NEW IMPLEMENTATION)
    if (!empty($_POST['_color_category'])) {
        $filters['_color_category'] = sanitize_text_field($_POST['_color_category']);
    }

    // Handle _ncs_ending filter (single value from hidden input)
    if (!empty($_POST['_ncs_ending'])) {
        $filters['_ncs_ending'] = sanitize_text_field($_POST['_ncs_ending']);
    }

    // Handle lifetime recommendation filter (combined warranty/structural durability)
    if (!empty($_POST['lifetime_recommendation'])) {
        $filters['lifetime_recommendation'] = array_map('sanitize_text_field', $_POST['lifetime_recommendation']);
    }

    // Handle stock status filter
    if (!empty($_POST['stock_status'])) {
        $filters['stock_status'] = sanitize_text_field($_POST['stock_status']);
    }

    // UPDATED: Range filters for NCS Chromaticness and Blackness
    // Capture and sanitize chromaticness range values
    if (isset($_POST['chromaticness-min']) && isset($_POST['chromaticness-max'])) {
        $chromaticness_min = intval($_POST['chromaticness-min']);
        $chromaticness_max = intval($_POST['chromaticness-max']);
        
        // Only apply filter if the range has been adjusted from default (0-100)
        if ($chromaticness_min > 0 || $chromaticness_max < 100) {
            $filters['chromaticness_range'] = array($chromaticness_min, $chromaticness_max);
        }
    }
    
    // Capture and sanitize blackness range values
    if (isset($_POST['blackness-min']) && isset($_POST['blackness-max'])) {
        $blackness_min = intval($_POST['blackness-min']);
        $blackness_max = intval($_POST['blackness-max']);
        
        // Only apply filter if the range has been adjusted from default (0-100)
        if ($blackness_min > 0 || $blackness_max < 100) {
            $filters['blackness_range'] = array($blackness_min, $blackness_max);
        }
    }

    // Width range filter
    if (isset($_POST['width_min']) && isset($_POST['width_max'])) {
        $width_min = intval($_POST['width_min']);
        $width_max = intval($_POST['width_max']);
        if ($width_min > 0 || $width_max > 0) {
            $filters['width_range'] = array($width_min, $width_max);
        }
    }

    // Length range filter
    if (isset($_POST['length_min']) && isset($_POST['length_max'])) {
        $length_min = intval($_POST['length_min']);
        $length_max = intval($_POST['length_max']);
        if ($length_min > 0 || $length_max > 0) {
            $filters['length_range'] = array($length_min, $length_max);
        }
    }

    // Pagination
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    
    // Products per page
    $posts_per_page = get_option('posts_per_page', 60);

    // Sorting
    $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : '';

    // Build WP_Query arguments
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'meta_query' => array('relation' => 'AND'),
        'tax_query' => array('relation' => 'AND')
    );

    // Add sorting parameters
    switch ($sort_by) {
        case 'price-asc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'price-m2asc':
            $args['meta_key'] = '_price_per_m2';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-m2desc':
            $args['meta_key'] = '_price_per_m2';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'lightness-asc':
            $args['meta_key'] = '_ncs_blackness';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC'; // Lower blackness = lighter
            break;
        case 'lightness-desc':
            $args['meta_key'] = '_ncs_blackness';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC'; // Higher blackness = darker
            break;
        case 'title-asc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'warranty-desc':
            $args['meta_key'] = '_warranty';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order title';
            $args['order'] = 'ASC';
            break;
    }

    // Add category filters to tax_query
    if (!empty($filters['material']) || !empty($filters['surface_material'])) {
        $category_terms = array();
        
        if (!empty($filters['material'])) {
            $category_terms = array_merge($category_terms, $filters['material']);
        }
        
        if (!empty($filters['surface_material'])) {
            $category_terms = array_merge($category_terms, $filters['surface_material']);
        }
        
        if (!empty($category_terms)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_terms,
                'operator' => 'IN'
            );
        }
    }

    // Add meta field filters to meta_query
    foreach ($meta_fields as $field) {
        if (!empty($filters[$field])) {
            $args['meta_query'][] = array(
                'key' => $field,
                'value' => $filters[$field],
                'compare' => 'IN'
            );
        }
    }

    // Add _color_category filter (NEW IMPLEMENTATION)
    if (!empty($filters['_color_category'])) {
        $args['meta_query'][] = array(
            'key' => '_color_category',
            'value' => $filters['_color_category'],
            'compare' => '='
        );
    }

    // Add _ncs_ending filter (single value)
    if (!empty($filters['_ncs_ending'])) {
        $args['meta_query'][] = array(
            'key' => '_ncs_ending',
            'value' => $filters['_ncs_ending'],
            'compare' => '='
        );
    }

    // UPDATED: Add NCS Chromaticness range filter
    if (isset($filters['chromaticness_range'])) {
        $args['meta_query'][] = array(
            'key' => '_ncs_chromaticness',
            'value' => $filters['chromaticness_range'],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        );
    }

    // UPDATED: Add NCS Blackness range filter
    if (isset($filters['blackness_range'])) {
        $args['meta_query'][] = array(
            'key' => '_ncs_blackness',
            'value' => $filters['blackness_range'],
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        );
    }

    // Add width range filter
    if (isset($filters['width_range'])) {
        $width_min = $filters['width_range'][0];
        $width_max = $filters['width_range'][1];
        
        if ($width_min > 0 && $width_max > 0) {
            $args['meta_query'][] = array(
                'key' => '_width',
                'value' => $filters['width_range'],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
        } elseif ($width_min > 0) {
            $args['meta_query'][] = array(
                'key' => '_width',
                'value' => $width_min,
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        } elseif ($width_max > 0) {
            $args['meta_query'][] = array(
                'key' => '_width',
                'value' => $width_max,
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }
    }

    // Add length range filter
    if (isset($filters['length_range'])) {
        $length_min = $filters['length_range'][0];
        $length_max = $filters['length_range'][1];
        
        if ($length_min > 0 && $length_max > 0) {
            $args['meta_query'][] = array(
                'key' => '_length',
                'value' => $filters['length_range'],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
        } elseif ($length_min > 0) {
            $args['meta_query'][] = array(
                'key' => '_length',
                'value' => $length_min,
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        } elseif ($length_max > 0) {
            $args['meta_query'][] = array(
                'key' => '_length',
                'value' => $length_max,
                'type' => 'NUMERIC',
                'compare' => '<='
            );
        }
    }

    // Add lifetime recommendation filter (complex query for combined warranty/structural durability)
    if (!empty($filters['lifetime_recommendation'])) {
        $lifetime_meta_query = array('relation' => 'OR');
        
        foreach ($filters['lifetime_recommendation'] as $lifetime_value) {
            $lifetime_meta_query[] = array(
                'relation' => 'AND',
                array(
                    'key' => '_warranty',
                    'value' => $lifetime_value,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_structural_durability',
                    'value' => $lifetime_value,
                    'compare' => 'LIKE'
                )
            );
        }
        
        $args['meta_query'][] = $lifetime_meta_query;
    }

    // Add stock status filter
    if (!empty($filters['stock_status'])) {
        if ($filters['stock_status'] === 'instock') {
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            );
        }
    }

    // Execute query
    $products_query = new WP_Query($args);

    // Start output buffering
    ob_start();

    if ($products_query->have_posts()) {
        woocommerce_product_loop_start();

        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }

        woocommerce_product_loop_end();
    } else {
        echo '<div class="wf-no-products-found">';
        echo '<h3>Tuotteita ei löytynyt</h3>';
        echo '<p>Valitettavasti hakuehdoillasi ei löytynyt tuotteita. Kokeile muuttaa suodattimia.</p>';
        echo '</div>';
    }

    $products_html = ob_get_clean();

    // Generate pagination
    ob_start();
    
    $pagination_args = array(
        'total' => $products_query->max_num_pages,
        'current' => $paged,
        'format' => '?paged=%#%',
        'show_all' => false,
        'end_size' => 1,
        'mid_size' => 2,
        'prev_next' => true,
        'prev_text' => '‹ Edellinen',
        'next_text' => 'Seuraava ›',
        'type' => 'plain'
    );
    
    echo paginate_links($pagination_args);
    
    $pagination_html = ob_get_clean();

    // Reset post data
    wp_reset_postdata();

    // Return JSON response
    wp_send_json_success(array(
        'products' => $products_html,
        'pagination' => $pagination_html,
        'found_posts' => $products_query->found_posts,
        'max_pages' => $products_query->max_num_pages,
        'current_page' => $paged,
        'visible_count' => $products_query->found_posts
    ));
}

// Hook AJAX actions
add_action('wp_ajax_wf_filter_products', 'wf_ajax_filter_products');
add_action('wp_ajax_nopriv_wf_filter_products', 'wf_ajax_filter_products');

/**
 * Enqueue scripts and localize AJAX data
 */
function wf_enqueue_filter_scripts() {
    if (is_shop() || is_product_category() || is_product_taxonomy()) {
        // Enqueue the filter script
        wp_enqueue_script(
            'wf-product-filters',
            get_template_directory_uri() . '/js/wf-product-filters.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script with AJAX data
        wp_localize_script('wf-product-filters', 'wf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wf_filter_nonce'),
            'loading_text' => 'Ladataan tuotteita...',
            'error_text' => 'Virhe suodattamisessa. Yritä uudelleen.'
        ));

        // Enqueue filter styles
        wp_enqueue_style(
            'wf-product-filters',
            get_template_directory_uri() . '/css/wf-product-filters.css',
            array(),
            '1.0.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'wf_enqueue_filter_scripts');

/**
 * Map NCS color code to Finnish color category
 */
function wf_map_ncs_to_color_category($ncs) {
    if (empty($ncs)) {
        return 'Neutraali';
    }
    
    $ncs_str = strtoupper(trim($ncs));
    
    $color_map = [
        'keltainen' => ['-Y', '-Y10R', '-Y20R', '-Y30R', '-Y40R'],
        'oranssi' => ['-Y50R', '-Y60R', '-Y70R', '-Y80R'],
        'punainen' => ['-Y90R', '-R', '-R10B', '-R20B', '-R30B', '-R40B'],
        'liila' => ['-R50B', '-R60B', '-R70B', '-R80B'],
        'sininen' => ['-R90B', '-B', '-B10G', '-B20G', '-B30G', '-B40G'],
        'turkoosi' => ['-B50G', '-B60G', '-B70G', '-B80G'],
        'vihrea' => ['-B90G', '-G', '-G10Y', '-G20Y', '-G30Y', '-G40Y'],
        'oliivi' => ['-G50Y', '-G60Y', '-G70Y', '-G80Y', '-G90Y']
    ];

    foreach ($color_map as $category => $hues) {
        foreach ($hues as $hue) {
            if (strpos($ncs_str, $hue) !== false) {
                return $category;
            }
        }
    }

    // Handle neutrals based on blackness
    if (strpos($ncs_str, '-N') !== false || !preg_match('/[YRGB]/', $ncs_str)) {
        $blackness = wf_get_ncs_blackness($ncs);
        if ($blackness <= 20) {
            return 'valkoinen';
        } elseif ($blackness <= 49) {
            return 'vaaleanharmaat';
        } elseif ($blackness <= 80) {
            return 'tummanharmaat';
        } else {
            return 'mustat';
        }
    }
    
    return 'Neutraali';
}

/**
 * Helper function to get blackness value from NCS code.
 */
function wf_get_ncs_blackness($ncs_code) {
    if (empty($ncs_code)) {
        return 0;
    }
    $s = strtoupper(trim($ncs_code));
    if (preg_match('/^S?(\d{2})/', $s, $matches)) {
        return intval($matches[1]);
    }
    return 0;
}

/**
 * Get products by color category using NCS mapping
 */
function wf_get_products_by_color_category($color_category) {
    global $wpdb;
    
    // Get all products with NCS ending values
    $products = $wpdb->get_results("
        SELECT post_id, meta_value as ncs_ending 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_ncs_ending' 
        AND meta_value != ''
    ");
    
    $matching_product_ids = array();
    
    foreach ($products as $product) {
        $mapped_category = wf_map_ncs_to_color_category($product->ncs_ending);
        if ($mapped_category === $color_category) {
            $matching_product_ids[] = $product->post_id;
        }
    }
    
    return $matching_product_ids;
}

/**
 * Get filter options for AJAX response
 */
function wf_get_filter_options($meta_key) {
    global $wpdb;
    
    $options = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = %s 
        AND meta_value != '' 
        ORDER BY meta_value ASC
    ", $meta_key));
    
    return $options;
}

/**
 * AJAX handler for getting filter options dynamically
 */
function wf_ajax_get_filter_options() {
    if (!wp_verify_nonce($_POST['nonce'], 'wf_filter_nonce')) {
        wp_die('Security check failed');
    }
    
    $meta_key = sanitize_text_field($_POST['meta_key']);
    $options = wf_get_filter_options($meta_key);
    
    wp_send_json_success($options);
}

add_action('wp_ajax_wf_get_filter_options', 'wf_ajax_get_filter_options');
add_action('wp_ajax_nopriv_wf_get_filter_options', 'wf_ajax_get_filter_options');
?>
