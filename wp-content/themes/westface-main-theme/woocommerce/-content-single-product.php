<?php
/**
 * Complete Modified Single Product Content Template with Inventory List View
 * 
 * This template transforms WooCommerce variations into a comprehensive
 * inventory list grouped by thickness with prominent stock status indicators,
 * while preserving all original right column functionality.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;
global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}


// Helper function to get variation stock status
if (!function_exists('get_variation_stock_status')) {
    function get_variation_stock_status($variation_data) {
        $variation_obj = wc_get_product($variation_data['variation_id']);
        if ($variation_obj && $variation_obj->is_in_stock()) {
            return $variation_obj->backorders_allowed() ? 'tilattavissa' : 'varastossa';
        }
        return 'loppu';
    }
}

// Helper function to get specific attribute value
if (!function_exists('get_attribute_value')) {
    function get_attribute_value($attributes, $target_attribute) {
        $target = strtolower(trim($target_attribute));
        foreach ($attributes as $attribute_name => $attribute_value) {
            $clean_name = str_replace('attribute_', '', $attribute_name);
            // Remove global attribute prefix 'pa_'
            if (stripos($clean_name, 'pa_') === 0) {
                $clean_name = substr($clean_name, 3);
            }
            $clean_name = str_replace('-', ' ', $clean_name);
            $clean_name = strtolower(trim($clean_name));
            // Also normalize multiple spaces
            $clean_name = preg_replace('/\s+/', ' ', $clean_name);

            if ($clean_name === $target) {
                return $attribute_value;
            }
        }
        return '';
    }
}

// Helper function to format dimensions
if (!function_exists('format_dimensions')) {
    function format_dimensions($attributes) {
        // Expect attributes as 'leveys mm' and 'pituus mm'
        $leveys = get_attribute_value($attributes, 'leveys mm');
        $pituus = get_attribute_value($attributes, 'pituus mm');

        if ($leveys && $pituus) {
            $leveys_clean = preg_replace('/[^0-9.]/', '', $leveys);
            $pituus_clean = preg_replace('/[^0-9.]/', '', $pituus);
            return $leveys_clean . ' × ' . $pituus_clean . ' mm';
        } elseif ($leveys) {
            $leveys_clean = preg_replace('/[^0-9.]/', '', $leveys);
            return $leveys_clean . ' mm (leveys)';
        } elseif ($pituus) {
            $pituus_clean = preg_replace('/[^0-9.]/', '', $pituus);
            return $pituus_clean . ' mm (pituus)';
        }

        return '-';
    }
}

// Helper function to get leveys value for sorting
if (!function_exists('get_leveys_value')) {
    function get_leveys_value($attributes) {
        foreach ($attributes as $attribute_name => $attribute_value) {
            $clean_name = str_replace('attribute_', '', $attribute_name);
            if (stripos($clean_name, 'pa_') === 0) {
                $clean_name = substr($clean_name, 3);
            }
            $clean_name = str_replace('-', ' ', $clean_name);
            $clean_name = strtolower($clean_name);

            if ($clean_name === 'leveys mm') {
                return floatval(preg_replace('/[^0-9.]/', '', $attribute_value));
            }
        }
        return 0;
    }
}

$product_color           = get_post_meta($product->get_id(), 'rgb_color_etu', true);
$product_color_back      = get_post_meta($product->get_id(), 'rgb_color_taka', true);
$product_ncs             = get_post_meta($product->get_id(), '_ncs_color_etu', true);
$product_ncs_back        = get_post_meta($product->get_id(), '_ncs_color_taka', true);
$product_labelcolor      = get_post_meta($product->get_id(), '_ncs_text_color', true);
$product_labelcolor_back = get_post_meta($product->get_id(), '_ncs_toinen_text_color', true);

// Get Atlantis code for prominent display
$atlantis_code = get_post_meta($product->get_id(), '_atlantis_code', true);

// Get all variations and group by thickness
$available_variations = array();
$grouped_variations = array();

if ($product->is_type('variable')) {
    $available_variations = $product->get_available_variations();
    
    foreach ($available_variations as $variation) {
        // Extract thickness from standardized attribute 'paksuus mm'
        $thickness = get_attribute_value($variation['attributes'], 'paksuus mm');
        if (!$thickness) {
            // Fallback try just 'paksuus'
            $thickness = get_attribute_value($variation['attributes'], 'paksuus');
        }
        if (!$thickness) {
            $thickness = 'Unknown';
        }

        if (!isset($grouped_variations[$thickness])) {
            $grouped_variations[$thickness] = array();
        }

        $grouped_variations[$thickness][] = $variation;
    }
    
    // Sort thickness groups numerically
    uksort($grouped_variations, function($a, $b) {
        $a_num = floatval(preg_replace('/[^0-9.]/', '', $a));
        $b_num = floatval(preg_replace('/[^0-9.]/', '', $b));
        return $a_num <=> $b_num;
    });
    
    // Sort variations within each thickness group by leveys
    foreach ($grouped_variations as $thickness => &$variations) {
        usort($variations, function($a, $b) {
            $leveys_a = get_leveys_value($a['attributes']);
            $leveys_b = get_leveys_value($b['attributes']);
            return $leveys_a <=> $leveys_b;
        });
    }
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('professional-single-product', $product); ?>>


    <div class="professional-product-layout">
        
        <div class="professional-product-left-column">
<?php 
            

            // Product title and SKU
            if (function_exists('woocommerce_template_single_title')) {
                woocommerce_template_single_title();
            } else {
                echo '<h1 class="product_title entry-title">' . esc_html(get_the_title()) . '</h1>';
            }
            // Show Brand in the left column (if available)
            $brand = get_post_meta($product->get_id(), 'brand', true);
            if (!empty($brand)) {
                echo '<div class="product-brand"><span class="brand-label">Valmistaja </span> <span class="brand-name">' . esc_html($brand) . '</span></div>';
            }
            $sku = is_object($product) ? $product->get_sku() : '';
            if (!empty($sku)) {
                echo '<div class="product-sku">' . esc_html($sku) . '</div>';
            }

            
            ?>
            
            <?php
            // Get product images
            $thumbnail_id = get_post_thumbnail_id($product->get_id());
            $gallery_image_ids = $product->get_gallery_image_ids();
            $second_image_id = !empty($gallery_image_ids) ? $gallery_image_ids[0] : null;
            
            // Get image URLs
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
            $second_image_url = $second_image_id ? wp_get_attachment_image_url($second_image_id, 'large') : '';
            ?>
            
            <?php if (has_post_thumbnail($product->get_id())) : ?>
                <!-- Display standard WooCommerce product images -->
                <div class="professional-product-images">
                    <?php
                    if (function_exists('woocommerce_show_product_images')) {
                        woocommerce_show_product_images();
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Always display color swatches, with images as background if available -->
            <div class="professional-color-swatch-container">
                <div class="product-color-container">
                    <div class="product-color-div product-color-front" style="<?php 
                        if ($thumbnail_url) {
                            echo 'background-image: url(' . esc_url($thumbnail_url) . '); background-size: cover; background-position: center;';
                        } else {
                            echo 'background-color: ' . esc_attr($product_color) . ';';
                        }
                    ?>">
                        <span class="swatch-label">ETUPUOLI</span>
                        <?php if (!empty($product_ncs)): ?>
                            <div class="color-info" style="color: #fff;">
                                <span class="ncs-code"><?php echo esc_html($product_ncs); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-color-div product-color-back" style="<?php 
                        if ($second_image_url) {
                            echo 'background-image: url(' . esc_url($second_image_url) . '); background-size: cover; background-position: center;';
                        } else {
                            echo 'background-color: ' . esc_attr($product_color_back) . ';';
                        }
                    ?>">
                        <span class="swatch-label">TAKAPUOLI</span>
                        <?php if (!empty($product_ncs_back)): ?>
                            <div class="color-info" style="color: #fff;">
                                <span class="ncs-code"><?php echo esc_html($product_ncs_back); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-badge">
                        <?php if ($product->is_on_sale()): ?>
                            <span class="sale-badge">SALE</span>
                        <?php endif; ?>
                        <?php if (!$product->is_in_stock()): ?>
                            <span class="stock-badge">LOPPU</span>
                        <?php endif; ?>
                        <?php if ($product->is_featured()): ?>
                            <span class="featured-badge">SUOSITTU</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="professional-product-description">
                <h3>Tuotekuvaus</h3>
                <div class="description-content">
                    <?php if ($product && $product->get_description()): ?>
                        <?php echo wp_kses_post($product->get_description()); ?>
                    <?php else: ?>
                        <p>Westface tarjoaa laadukkaita laminaattilevyjä, jotka soveltuvat erinomaisesti sisustukseen ja kalusteiden valmistukseen. Tuotteemme ovat kestäviä, helppohoitoisia ja saatavilla monissa eri väreissä ja pintarakenteissa.</p>
                        <p>Laminaattilevymme valmistetaan korkeapainelaminaattitekniikalla, mikä takaa erinomaisen kestävyyden ja pitkäikäisyyden. Tuotteet soveltuvat sekä kotikäyttöön että ammattimaiseen rakentamiseen.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="professional-pricing-container">
                <div class="pricing-card primary-pricing">
                    <div class="pricing-label">Edullisin Levyhinta</div>
                    <div class="price-display">
                        <?php
                        $price = $product->get_price();
                        if ($price) {
                            $price_float = floatval(str_replace(',', '.', $price));
                            echo wc_price($price_float);
                        } else {
                            echo "N/A";
                        }
                        ?>
                        <span class="price-unit">/levy</span>
                    </div>
                </div>
                <div class="pricing-card secondary-pricing">
                    <div class="pricing-label">Edullisin neliöhinta</div>
                    <div class="price-display">
                        <?php
                        $m2_price = get_post_meta($product->get_id(), '_m2_price', true);
                        if ($m2_price) {
                            $m2_price_float = floatval(str_replace(',', '.', $m2_price));
                            echo wc_price($m2_price_float);
                        } else {
                            echo "N/A";
                        }
                        ?>
                        <span class="price-unit">/m²</span>
                    </div>
                </div>
            </div>
            
            <?php if ($product->is_type('variable') && !empty($grouped_variations)): ?>
            <!-- NEW INVENTORY LIST VIEW -->
            <div class="inventory-list-container">
                <h3>Saatavilla olevat vaihtoehdot</h3>
                
                <?php foreach ($grouped_variations as $thickness => $variations): ?>
                <div class="thickness-group">
                    <h4 class="thickness-header">Paksuus: <?php echo esc_html((strpos($thickness, 'mm') !== false) ? $thickness : (preg_replace('/[^0-9.]/', '', $thickness) ? preg_replace('/[^0-9.]/', '', $thickness) . ' mm' : $thickness)); ?></h4>
                    
                    <div class="variations-table-container">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th class="dimensions-col">Koot</th> 
                                    <th class="fire-rating-col">Paloluokka</th> 
                                    <th class="material-col">Pelti</th> 
                                    <th class="pricing-col">Hinnat</th>
                                    <th class="availability-col">Saatavuus</th>
                                    <th class="add-to-cart-col">Tilaus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variations as $variation): ?>
                                <?php 
                                $stock_status = get_variation_stock_status($variation);
                                $variation_obj = wc_get_product($variation['variation_id']);
                                $variation_price = $variation_obj ? $variation_obj->get_price() : '';
                                ?>
                                <tr class="variation-row" data-variation-id="<?php echo esc_attr($variation['variation_id']); ?>">
                                    <td class="dimensions" data-label="Leveys × Pituus">
                                        <?php echo esc_html(format_dimensions($variation['attributes'])); ?>
                                    </td>
                                    <td class="fire-rating" data-label="Paloluokka">
                                        <?php 
                                        $paloluokka = get_attribute_value($variation['attributes'], 'paloluokka');
                                        echo esc_html($paloluokka ?: '-');
                                        ?>
                                    </td>
                                    <td class="material" data-label="Pelti Materiaali">
                                        <?php 
                                        $peltimateriaali = get_attribute_value($variation['attributes'], 'pelti materiaali');
                                        echo esc_html($peltimateriaali ?: '-');
                                        ?>
                                    </td>
                                    <td class="pricing" data-label="Hinnat">
                                        <?php if ($variation_price): ?>
                                        <div class="variation-price-sheet">
                                            <span class="price-label">Levyhinta:</span>
                                            <span class="price-value"><?php echo wc_price($variation_price); ?></span>
                                        </div>
                                        <?php 
                                        // Calculate m² price if area is available
                                        $variation_area = get_post_meta($variation['variation_id'], '_area_m2', true);
                                        if ($variation_area && $variation_price) {
                                            $area_float = floatval(str_replace(',', '.', $variation_area));
                                            if ($area_float > 0) {
                                                $m2_price = $variation_price / $area_float;
                                                ?>
                                                <div class="variation-price-m2">
                                                    <span class="price-label">Neliöhinta:</span>
                                                    <span class="price-value"><?php echo wc_price($m2_price); ?>/m²</span>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <?php else: ?>
                                        <div class="no-price">Hinta pyynnöstä</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="availability" data-label="Saatavuus">
                                        <span class="stock-status <?php echo esc_attr($stock_status); ?>">
                                            <?php 
                                            switch ($stock_status) {
                                                case 'varastossa':
                                                    echo 'Varastossa';
                                                    break;
                                                case 'tilattavissa':
                                                    echo 'Tilattavissa';
                                                    break;
                                                case 'loppu':
                                                    echo 'Loppu';
                                                    break;
                                            }
                                            ?>
                                        </span>
                                        <?php 
                                        // Вывод количества товара для отладки
                                        if ($variation_obj) {
                                            $qty = $variation_obj->get_stock_quantity();
                                            echo '<span class="variation-qty">Количество: ' . ($qty !== null ? $qty : '—') . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="add-to-cart" data-label="Lisää koriin">
                                        <?php if ($stock_status !== 'loppu'): ?>
                                        <div class="variation-cart-form">
                                            <div class="quantity-wrapper">
                                                <label for="quantity-<?php echo esc_attr($variation['variation_id']); ?>">Kpl:</label>
                                                <input type="number" 
                                                       id="quantity-<?php echo esc_attr($variation['variation_id']); ?>"
                                                       class="quantity-input" 
                                                       name="quantity" 
                                                       value="1" 
                                                       min="1" 
                                                       step="1">
                                            </div>
                                            <button type="button" 
                                                    class="button variation-add-to-cart" 
                                                    data-variation-id="<?php echo esc_attr($variation['variation_id']); ?>"
                                                    data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                                Lisää koriin
                                            </button>
                                        </div>
                                        <?php else: ?>
                                        <div class="out-of-stock-notice">
                                            <span class="disabled-text">Ei saatavilla</span>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- FALLBACK TO STANDARD ADD TO CART FOR SIMPLE PRODUCTS -->
              <div class="professional-pricing-container">
                <div class="pricing-card primary-pricing">
                    <div class="pricing-label">Edullisin Levyhinta</div>
                    <div class="price-display">
                        <?php
                        $price = $product->get_price();
                        if ($price) {
                            $price_float = floatval(str_replace(',', '.', $price));
                            echo wc_price($price_float);
                        } else {
                            echo "N/A";
                        }
                        ?>
                        <span class="price-unit">/levy</span>
                    </div>
                </div>
                <div class="pricing-card secondary-pricing">
                    <div class="pricing-label">Edullisin neliöhinta</div>
                    <div class="price-display">
                        <?php
                        $m2_price = get_post_meta($product->get_id(), '_m2_price', true);
                        if ($m2_price) {
                            $m2_price_float = floatval(str_replace(',', '.', $m2_price));
                            echo wc_price($m2_price_float);
                        } else {
                            echo "N/A";
                        }
                        ?>
                        <span class="price-unit">/m²</span>
                    </div>
                </div>
            </div>
            <div class="professional-add-to-cart">
                <?php
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
                do_action('woocommerce_single_product_summary');
                ?>
            </div>
            <?php endif; ?>

           

        </div>

        <div class="professional-product-right-column">
            <div class="professional-specifications">
                <!-- <h3>Tuotetiedot</h3> -->
                <div class="specifications-grid">
                    <?php
                    // Labels for meta fields (shared across groups)
                    $meta_labels = array(
                        // Front Side Attributes
                        '_front_name'        => 'Etupuolen väri',
                        '_ncs_color_etu'         => 'NCS väri (etu)',
                        '_surface_finish'        => 'Etupuolen kiiltoaste',
                        '_product_pattern'        => 'Etupuolen Pelti',
                        'mallisto'        => 'Etupuolen mallisto',

                        '_back_name'        => 'Takapuolen väri',
                        '_ncs_color_taka'         => 'NCS väri (taka)',
                        '_surface_finish'        => 'Takapuolen Kiiltoaste',
                        '_product_pattern'        => 'Takapuolen Pelti',
                        'mallisto'        => 'Takapuolen mallisto',

                      
                        // Technical Specifications
                        '_available_lengths_mm'              => 'Saatavissa Leveyksiä (mm)',
                        '_available_heights_mm'             => 'Saatavissa Pituuksia (mm)',
                        '_area_m2'               => 'Pinta-ala',
                        '_available_thicknesses_mm'               => 'Saatavissa Paksuuksia (mm)',
                        '_fire_rating'           => 'Paloluokka',
                        '_impact_resistance'     => 'Iskunkestävyys',
                        '_structural_durability' => 'Rakennekesto',
                        '_maintenance_interval'  => 'Huoltoväli',
                        '_warranty'              => 'Takuu',

                        // Other Information
                        'brand'                  => 'Valmistaja',
                        '_color_durability'      => 'Käytettävissä olevat järjestelmät',

                        // '_surface_pattern'       => 'Mallisto',

                    );

                    // Group definitions (order matters)
                    $group_front = array(
                        '_front_name',
                        '_ncs_color_etu',
                        '_surface_finish',
                        '_product_pattern',
                        'mallisto',
                    );
                    $group_back  = array(
                        '_back_name',
                        '_ncs_color_taka',
                        '_surface_finish',
                        '_product_pattern',
                        'mallisto',
                    );
                    $group_tech  = array(
                        '_available_lengths_mm',
                        '_available_heights_mm',
                        '_area_m2',
                        '_available_thicknesses_mm',
                        '_fire_rating',
                        '_impact_resistance',
                        '_structural_durability',
                        '_maintenance_interval',
                        '_warranty'
                    );
                    $group_other = array(
                        'brand',
                        '_color_durability',
                        // '_surface_pattern',
                    );

                    // Helper to render value with minimal formatting
                    function wf_format_meta_value($key, $value)
                    {
                        if ($value === '' || $value === null) {
                            return '';
                        }
                        $normalized = is_string($value) ? str_replace(',', '.', $value) : $value;
                        
                        // Format area to 2 decimal places
                        if ($key === '_area_m2') {
                            $num = floatval($normalized);
                            return number_format($num, 2, ',', '') . ' m²';
                        }
                        
                        // Display mm values directly for dimensions
                        if ($key === '_width_mm' || $key === '_height_mm' || $key === '_available_lengths_mm' || $key === '_available_heights_mm' || $key === '_available_thicknesses_mm') {
                            $num = floatval($normalized);
                            return number_format($num, 0, ',', '') . ' mm';
                        }
                        
                        // Append units/suffixes for specific meta keys
                        if (in_array($key, array('_structural_durability', '_warranty', '_maintenance_interval'), true)) {
                            $val = trim((string) $value);
                            if (!preg_match('/\\bvuotta$/iu', $val)) {
                                $val .= ' vuotta';
                            }
                            return esc_html($val);
                        }
                        if ($key === '_delivery_time') {
                            $val = trim((string) $value);
                            if (!preg_match('/\\bpäivää$/iu', $val)) {
                                $val .= ' päivää';
                            }
                            return esc_html($val);
                        }
                        if ($key === 'rgb_color_etu' || $key === 'rgb_color_taka' || $key === '_vkoodi' || $key === '_vkoodi_taka') {
                            $color = trim($value);
                            $chip  = '<span style="display:inline-block;width:12px;height:12px;border:1px solid #ddd;border-radius:2px;margin-right:6px;vertical-align:middle;background:'
                                . esc_attr($color) . '"></span>';
                            return $chip . '<span>' . esc_html($value) . '</span>';
                        }
                        return esc_html($value);
                    }

                    // Helper to format attribute values based on their label
                    function wf_format_attribute_value($label, $value)
                    {
                        $val = trim((string) $value);
                        if (preg_match('/paksu.*\(mm\)/i', (string) $label)) {
                            $parts = preg_split('/\s*,\s*/', $val);
                            foreach ($parts as &$p) {
                                if ($p !== '' && !preg_match('/mm\b/i', $p)) {
                                    $p .= ' mm';
                                }
                            }
                            $val = implode(', ', $parts);
                        }
                        return esc_html($val);
                    }

                    // Utility: check if any meta in a list has a value
                    $has_any_meta = function (array $keys) use ($product) {
                        foreach ($keys as $k) {
                            $val = get_post_meta($product->get_id(), $k, true);
                            if (!empty($val) || $val === '0' || $val === 0) {
                                return true;
                            }
                        }
                        return false;
                    };

                    // Get product attributes and categorize them
                    $attributes = $product->get_attributes();
                    $attr_groups = array(
                        'front' => array(),
                        'back'  => array(),
                        'tech'  => array(),
                        'other' => array(),
                    );

                    $thickness_attribute = null;
                    $area_displayed = false;

                    foreach ($attributes as $attribute) {
                        if ($attribute->get_variation()) {
                            $name  = $attribute->get_name();
                            $label = wc_attribute_label($name);
                            $terms = wc_get_product_terms($product->get_id(), $name, array('fields' => 'names'));
                            $value = implode(', ', $terms);

                            $attr_data = array(
                                'name'  => $name,
                                'label' => $label,
                                'value' => $value,
                            );

                            // Categorize attributes
                            $lower_label = strtolower($label);
                            if (strpos($lower_label, 'paksu') !== false) {
                                // $thickness_attribute = $attr_data;
                            } elseif (strpos($lower_label, 'pintastruktuuri') !== false || 
                                     strpos($lower_label, 'kiilto') !== false ||
                                     strpos($lower_label, 'surface') !== false) {
                                $attr_groups['front'][] = $attr_data;
                            } elseif (strpos($lower_label, 'taka') !== false || 
                                     strpos($lower_label, 'back') !== false ||
                                     strpos($lower_label, 'mallisto') !== false) {
                                $attr_groups['back'][] = $attr_data;
                            } elseif (strpos($lower_label, 'koot') !== false || 
                                     strpos($lower_label, 'palo') !== false ||
                                     strpos($lower_label, 'fire') !== false ||
                                     strpos($lower_label, 'size') !== false) {
                                // $attr_groups['tech'][] = $attr_data;
                            } else {
                                // $attr_groups['other'][] = $attr_data;
                            }
                        }
                    }

                    // Render Front Side Attributes section
                    if ($has_any_meta($group_front) || !empty($attr_groups['front'])) {
                        echo '<h4>Etupuoli <b>Fundermax Voyager oak</b></h4>';
                        echo '<div class="spec-group front-specs">';
                        foreach ($group_front as $key) {
                            $value = get_post_meta($product->get_id(), $key, true);
                            if (!empty($value) || $value === '0' || $value === 0) {
                                $formatted = wf_format_meta_value($key, $value);
                                if ($formatted !== '') {
                                    echo '<div class="spec-item">';
                                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                                    echo '<span class="spec-value">' . $formatted . '</span>';
                                    echo '</div>';
                                }
                            }
                        }
                        foreach ($attr_groups['front'] as $attr) {
                            $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                            echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                            echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                            echo '<span class="spec-value">' . $formatted . '</span>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    // Render Back Side Attributes section
                    if ($has_any_meta($group_back) || !empty($attr_groups['back'])) {
                        echo '<h4>Takapuoli <b>Fundermax Voyager oak</b></h4>';
                        echo '<div class="spec-group back-specs">';
                        foreach ($group_back as $key) {
                            $value = get_post_meta($product->get_id(), $key, true);
                            if (!empty($value) || $value === '0' || $value === 0) {
                                $formatted = wf_format_meta_value($key, $value);
                                if ($formatted !== '') {
                                    echo '<div class="spec-item">';
                                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                                    echo '<span class="spec-value">' . $formatted . '</span>';
                                    echo '</div>';
                                }
                            }
                        }
                        foreach ($attr_groups['back'] as $attr) {
                            $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                            echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                            echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                            echo '<span class="spec-value">' . $formatted . '</span>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    // Render Technical Specifications section
                    if ($has_any_meta($group_tech) || !empty($attr_groups['tech']) || $thickness_attribute) {
                        echo '<h4>Tekniset tiedot</h4>';
                        echo '<div class="spec-group tech-specs">';
                         // If area wasn't displayed but we have thickness, show thickness anyway
                        if (!$area_displayed && $thickness_attribute) {
                            $formatted_thickness = wf_format_attribute_value($thickness_attribute['label'], $thickness_attribute['value']);
                            echo '<div class="spec-item" data-attribute="paksuus">';
                            echo '<span class="spec-label">' . esc_html($thickness_attribute['label']) . ':</span>';
                            echo '<span class="spec-value">' . $formatted_thickness . '</span>';
                            echo '</div>';
                        }
                        $area_displayed = false;
                        foreach ($group_tech as $key) {
                            // Skip _m2_price as it's displayed in pricing card
                            if ($key === '_m2_price') {
                                continue;
                            }
                            
                            $value = get_post_meta($product->get_id(), $key, true);
                            if (!empty($value) || $value === '0' || $value === 0) {
                                $formatted = wf_format_meta_value($key, $value);
                                if ($formatted !== '') {
                                    echo '<div class="spec-item">';
                                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                                    echo '<span class="spec-value">' . $formatted . '</span>';
                                    echo '</div>';
                                }
                            }
                        }
                        
                        foreach ($attr_groups['tech'] as $attr) {
                            $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                            echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                            echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                            echo '<span class="spec-value">' . $formatted . '</span>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    // Render Other Information section
                    if ($has_any_meta($group_other) || !empty($attr_groups['other'])) {
                        echo '<h4>Muut tiedot</h4>';
                        echo '<div class="spec-group other-specs">';
                        foreach ($group_other as $key) {
                            $value = get_post_meta($product->get_id(), $key, true);
                            if (!empty($value) || $value === '0' || $value === 0) {
                                $formatted = wf_format_meta_value($key, $value);
                                if ($formatted !== '') {
                                    echo '<div class="spec-item">';
                                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                                    echo '<span class="spec-value">' . $formatted . '</span>';
                                    echo '</div>';
                                }
                            }
                        }
                        // foreach ($attr_groups['other'] as $attr) {
                        //     $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                        //     echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                        //     echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                        //     echo '<span class="spec-value">' . $formatted . '</span>';
                        //     echo '</div>';
                        // }
                        echo '</div>';
                    }
                    ?>
                     <!-- Sample Request Section -->
                    <div class="professional-sample-request">
                        <h3>Tilaa levyn mallipalat</h3>
                        <p>Mallipalapurske tarvitaan tuotteen arviointiin</p>
                        <!-- <button class="sample-request-btn" type="button">
                            <i class="fas fa-cube"></i>
                            TILAA MALLIPALAT
                        </button> -->
                    </div>
                </div>
            </div>
           
        </div>
        </div>
    </div>

<div class="professional-full-width-sections">
    <!-- <div class="container"> -->
        <?php
        /**
         * Hook: woocommerce_after_single_product_summary.
         *
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action('woocommerce_after_single_product_summary');
        ?>
    <!-- </div> -->
</div>

<?php do_action('woocommerce_after_single_product'); ?>

<script>
jQuery(document).ready(function($) {
    // Handle add to cart for individual variations
    $('.variation-add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $row = $button.closest('.variation-row');
        var variationId = $button.data('variation-id');
        var productId = $button.data('product-id');
        var quantity = $row.find('.quantity-input').val();
        
        // Disable button and show loading state
        $button.prop('disabled', true).text('Lisätään...');
        
        // AJAX add to cart
        var variationData = {};
        $row.find('[data-attribute]').each(function() {
            var attr = $(this).data('attribute');
            var val = $(this).data('value') || $(this).text().trim();
            if (attr && val) {
                variationData[attr] = val;
            }
        });
        $.post(wc_add_to_cart_params.ajax_url, {
            action: 'add_to_cart',
             product_id: productId,
            variation_id: variationId,
            quantity: quantity,
            variation: variationData
        }, function(response) {
            if (response.error) {
                alert('Virhe: ' + response.error_message);
            } else {
                // Update cart fragments
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                
                // Show success message
                $button.text('Lisätty!').addClass('added');
                setTimeout(function() {
                    $button.text('Lisää koriin').removeClass('added').prop('disabled', false);
                }, 2000);
            }
        }).fail(function() {
            alert('Verkkovirhe. Yritä uudelleen.');
            $button.text('Lisää koriin').prop('disabled', false);
        });
    });
    
    // Dynamic highlighting for in-stock variations (enhanced from original)
    function highlightInStockAttributes() {
        // Get all variation dropdowns (if any still exist)
        var $variationSelects = $('.variations select');
        
        // Function to check if current selection is in stock
        function checkStockStatus() {
            var selectedValues = {};
            var allSelected = true;
            
            $variationSelects.each(function() {
                var $select = $(this);
                var value = $select.val();
                var attributeName = $select.attr('name');
                
                if (value && value !== '') {
                    selectedValues[attributeName] = value;
                } else {
                    allSelected = false;
                }
            });
            
            // Remove all previous highlighting
            $('.spec-item').removeClass('in-stock-attribute');
            
            if (allSelected) {
                // Check if this combination is in stock
                var $form = $('form.variations_form');
                var variations = $form.data('product_variations');
                
                if (variations) {
                    var matchingVariation = null;
                    
                    for (var i = 0; i < variations.length; i++) {
                        var variation = variations[i];
                        var matches = true;
                        
                        for (var attr in selectedValues) {
                            var variationAttr = 'attribute_' + attr.replace('attribute_', '');
                            if (variation.attributes[variationAttr] !== selectedValues[attr]) {
                                matches = false;
                                break;
                            }
                        }
                        
                        if (matches) {
                            matchingVariation = variation;
                            break;
                        }
                    }
                    
                    // If variation is in stock, highlight corresponding spec items
                    if (matchingVariation && matchingVariation.is_in_stock) {
                        for (var attr in selectedValues) {
                            var cleanAttr = attr.replace('attribute_', '');
                            $('.spec-item[data-attribute*="' + cleanAttr + '"]').addClass('in-stock-attribute');
                        }
                    }
                }
            }
        }
        
        // Bind to variation change events
        $variationSelects.on('change', checkStockStatus);
        
        // Initial check
        checkStockStatus();
    }
    
    // Handle Pintastruktuuri as static text when value is "NT"
    function handleSurfaceStructure() {
        $('.spec-item').each(function() {
            var $item = $(this);
            var label = $item.find('.spec-label').text().toLowerCase();
            var value = $item.find('.spec-value').text().trim();
            
            if (label.indexOf('pintastruktuuri') !== -1 && value === 'NT') {
                // Make this item appear as static text (remove any interactive elements)
                $item.addClass('static-attribute');
            }
        });
    }
    
    // Mobile table responsiveness
    function handleMobileTable() {
        if ($(window).width() <= 768) {
            $('.inventory-table').addClass('mobile-table');
        } else {
            $('.inventory-table').removeClass('mobile-table');
        }
    }
    
    // Initialize functions
    if ($('.variations_form').length > 0) {
        highlightInStockAttributes();
    }
    handleSurfaceStructure();
    handleMobileTable();
    $(window).resize(handleMobileTable);
});
</script>

<?php do_action('woocommerce_after_single_product'); ?>
