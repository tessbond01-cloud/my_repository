<?php
/**
 * REFACTORED WooCommerce Product Category Template
 *
 * Implements a new three-row filtering and sorting interface as per specifications.
 *
 * Features:
 * - Three-row control layout:
 * 1. Main navigation (Tabs) and primary actions (Color Tool, Advanced Filters).
 * 2. Quick access color category buttons and NCS range sliders.
 * 3. Dynamic results count and custom AJAX sorting controls.
 * - Tabbed view to switch between product grid and category information.
 * - Collapsible "Tarkka suodatus" (Advanced Filtering) section.
 * - Replaces default WooCommerce sorting with custom, powerful sort options.
 * - Fully responsive design, ready for AJAX implementation.
 *
 * @package WooCommerce\Templates
 * @version 4.7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get current category object and its thumbnail ID
$current_category = get_queried_object();
$category_image_id = get_term_meta($current_category->term_id, 'thumbnail_id', true);

get_header('shop'); ?>

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

<div class="wf-category-wrapper">
    <?php
    /**
     * Hook: woocommerce_before_main_content.
     */
    do_action('woocommerce_before_main_content');
    ?>

    <div class="wf-category-container">
        
        <div class="wf-category-header">
            <div class="wf-header-content">
                <div class="wf-header-left">
                    <h1 class="wf-category-title"><?php echo esc_html($current_category->name); ?></h1>
                    <?php if ($current_category->description) : ?>
                        <div class="wf-category-description">
                            <?php echo wp_kses_post($current_category->description); ?>
                        </div>
                    <?php endif; ?>
                    <div class="wf-category-stats">
                        <span class="wf-product-count">
                            <i class="fas fa-box"></i> <?php echo esc_html($current_category->count); ?> tuotetta
                        </span>
                    </div>
                </div>
                <div class="wf-header-right">
                    <?php if ($category_image_id) : ?>
                        <div class="wf-category-image">
                            <?php echo wp_get_attachment_image($category_image_id, 'large', false, array('alt' => esc_attr($current_category->name))); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="wf-refactored-controls">

            <!-- MOVED: Form opening tag now wraps ALL control rows -->
            <form id="wf-product-filters" class="wf-product-filters">
                <?php wp_nonce_field('wf_filter_nonce', 'nonce'); ?>
                <input type="hidden" name="current_category_id" value="<?php echo esc_attr($current_category->term_id); ?>">
                <input type="hidden" name="_ncs_ending" id="wf-ncs-ending-value" value="">

                <div class="wf-controls-row" id="wf-controls-row-1">
                    <div class="wf-controls-left">
                      <nav class="wf-view-tabs">
                            <button class="wf-tab-link active" data-tab="products">Katso tuotteet</button>
                            <button class="wf-tab-link" data-tab="info"><?php echo esc_html($current_category->name); ?> info</button>
                        </nav>
                    </div>
                    <div class="wf-controls-center">
                        
                    </div>
                    <div class="wf-controls-right">  
                        <button type="button" id="wf-color-picker-btn" class="wf-btn">
                            <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/color-picker.svg' ); ?>" alt="Wf colour picker icon" aria-hidden="true" class="wf-icon" /> Avaa värityökalu
                        </button>
                        <button type="button" id="wf-toggle-advanced-filters-btn" class="wf-btn">
                            <i class="fas fa-sliders-h wf-icon"></i> Tarkka suodatus
                        </button>
                    </div>
                </div>

                <div id="wf-advanced-filters-area">
                    <!-- REMOVED: Form opening tag was here, now moved above -->
                    
                    <!-- Row 1: Attribute Selections (6 Columns) -->
                    <div class="wf-filter-row wf-filter-row-1">
                        <!-- Column 1: Materiaalit -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-cube"></i> Materiaalit</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                $material_terms = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0));
                                foreach ($material_terms as $term) : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="material[]" value="<?php echo esc_attr($term->term_id); ?>" id="material_<?php echo esc_attr($term->term_id); ?>">
                                    <label for="material_<?php echo esc_attr($term->term_id); ?>"><?php echo esc_html($term->name); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Column 2: Koot -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-expand-arrows-alt"></i> Paksuus</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                global $wpdb;
                                $sizes = $wpdb->get_results("
                                    SELECT DISTINCT pm.meta_value 
                                    FROM {$wpdb->postmeta} pm
                                    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                                    INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                                    WHERE pm.meta_key = 'attribute_thickness' 
                                    AND pm.meta_value != '' 
                                    AND p.post_type = 'product'
                                    AND p.post_status = 'publish'
                                    AND tt.taxonomy = 'pa_thickness'
                                    ORDER BY pm.meta_value ASC
                                ");
                                foreach ($sizes as $size) : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="koot[]" value="<?php echo esc_attr($size->meta_value); ?>" id="koot_<?php echo esc_attr(sanitize_title($size->meta_value)); ?>">
                                    <label for="koot_<?php echo esc_attr(sanitize_title($size->meta_value)); ?>"><?php echo esc_html($size->meta_value); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Column 3: Paloluokka -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-fire"></i> Paloluokka</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                $fire_ratings = $wpdb->get_results("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_fire_rating' AND meta_value != '' ORDER BY meta_value ASC");
                                foreach ($fire_ratings as $rating) : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="_fire_rating[]" value="<?php echo esc_attr($rating->meta_value); ?>" id="fire_rating_<?php echo esc_attr(sanitize_title($rating->meta_value)); ?>">
                                    <label for="fire_rating_<?php echo esc_attr(sanitize_title($rating->meta_value)); ?>"><?php echo esc_html($rating->meta_value); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Column 4: Käyttöikä suositus -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-clock"></i> Käyttöikä suositus</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                // Combine warranty and structural durability values
                                $lifetime_recommendations = $wpdb->get_results("
                                    SELECT DISTINCT CONCAT(COALESCE(w.meta_value, ''), ' / ', COALESCE(sd.meta_value, '')) as combined_value,
                                           COALESCE(w.meta_value, '') as warranty,
                                           COALESCE(sd.meta_value, '') as structural_durability
                                    FROM {$wpdb->posts} p
                                    LEFT JOIN {$wpdb->postmeta} w ON p.ID = w.post_id AND w.meta_key = '_warranty'
                                    LEFT JOIN {$wpdb->postmeta} sd ON p.ID = sd.post_id AND sd.meta_key = '_structural_durability'
                                    WHERE p.post_type = 'product' 
                                    AND p.post_status = 'publish'
                                    AND (w.meta_value != '' OR sd.meta_value != '')
                                    ORDER BY combined_value ASC
                                ");
                                foreach ($lifetime_recommendations as $recommendation) : 
                                    if (trim($recommendation->combined_value) !== ' / ') : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="lifetime_recommendation[]" value="<?php echo esc_attr($recommendation->combined_value); ?>" id="lifetime_<?php echo esc_attr(sanitize_title($recommendation->combined_value)); ?>">
                                    <label for="lifetime_<?php echo esc_attr(sanitize_title($recommendation->combined_value)); ?>"><?php echo esc_html($recommendation->combined_value); ?></label>
                                </div>
                                    <?php endif;
                                endforeach; ?>
                            </div>
                        </div>

                        <!-- Column 5: Kiiltoaste -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-sun"></i> Kiiltoaste</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                $surface_finishes = $wpdb->get_results("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_surface_finish' AND meta_value != '' ORDER BY meta_value ASC");
                                foreach ($surface_finishes as $finish) : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="_surface_finish[]" value="<?php echo esc_attr($finish->meta_value); ?>" id="surface_finish_<?php echo esc_attr(sanitize_title($finish->meta_value)); ?>">
                                    <label for="surface_finish_<?php echo esc_attr(sanitize_title($finish->meta_value)); ?>"><?php echo esc_html($finish->meta_value); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Column 6: Valmistaja (Brand) -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-industry"></i> Valmistaja</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                $manufacturers = $wpdb->get_results("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'brand' AND meta_value != '' ORDER BY meta_value ASC");
                                foreach ($manufacturers as $manufacturer) : ?>
                                <div class="wf-checkbox-item">
                                    <input type="checkbox" name="brand[]" value="<?php echo esc_attr($manufacturer->meta_value); ?>" id="brand_<?php echo esc_attr(sanitize_title($manufacturer->meta_value)); ?>">
                                    <label for="brand_<?php echo esc_attr(sanitize_title($manufacturer->meta_value)); ?>"><?php echo esc_html($manufacturer->meta_value); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Range and Stock Filters (3 Columns) -->
                    <div class="wf-filter-row wf-filter-row-2">
                        <!-- Column 1: Leveys (Width) -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-arrows-alt-h"></i> Leveys</h4>
                            <div class="wf-range-inputs">
                                <input type="number" name="width_min" id="width_min" placeholder="Min leveys (cm)" class="wf-range-input">
                                <input type="number" name="width_max" id="width_max" placeholder="Max leveys (cm)" class="wf-range-input">
                            </div>
                        </div>

                        <!-- Column 2: Pituus (Length) -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-arrows-alt-v"></i> Pituus</h4>
                            <div class="wf-range-inputs">
                                <input type="number" name="length_min" id="length_min" placeholder="Min pituus (cm)" class="wf-range-input">
                                <input type="number" name="length_max" id="length_max" placeholder="Max pituus (cm)" class="wf-range-input">
                            </div>
                        </div>

                        <!-- Column 3: Varastossa (In Stock) - Checkbox -->
                        <div class="wf-filter-column">
                            <div class="wf-checkbox-item" style="padding-top: 2rem;">
                                <input type="checkbox" name="stock_status" value="instock" id="stock_status_instock">
                                <label for="stock_status_instock">Näytä vain varastossa</label>
                            </div>
                        </div>
                    </div>

                    <div class="wf-filter-buttons">
                        <button type="submit" id="wf-apply-filters" class="wf-btn wf-btn-primary">
                            <i class="fas fa-search"></i> Suodata
                        </button>
                        <button type="button" id="wf-clear-filters" class="wf-btn wf-btn-secondary">
                            <i class="fas fa-times"></i> Tyhjennä
                        </button>
                    </div>
                </div>
                
                <!-- NOW INSIDE FORM: Color buttons and range sliders -->
                <div class="wf-controls-row" id="wf-controls-row-2">
                    <div class="wf-color-filter-buttons">
                        <button class="wf-color-btn" data-color="keltainen">Keltaiset</button>
                        <button class="wf-color-btn" data-color="oranssi">Oranssit</button>
                        <button class="wf-color-btn" data-color="punainen">Punaiset</button>
                        <button class="wf-color-btn" data-color="violetti">Violetit</button>
                        <button class="wf-color-btn" data-color="sininen">Siniset</button>
                        <button class="wf-color-btn" data-color="turkoosi">Turkoosit</button>
                        <button class="wf-color-btn" data-color="vihrea">Vihreät</button>
                        <button class="wf-color-btn" data-color="oliivi">Oliivit</button>
                        <button class="wf-color-btn" data-color="valkoinen">Valkoiset</button>
                        <button class="wf-color-btn" data-color="vaaleanharmaa">Vaaleat</button>
                        <button class="wf-color-btn" data-color="tummanharmaa">Tummat</button>
                        <button class="wf-color-btn" data-color="musta">Mustat</button>
                    </div>
                    <div class="wf-range-filters">
                        <div class="wf-filter-group">
                            <h4><i class="fas fa-adjust"></i> Värin määrä</h4>
                            <div class="wf-range-container">
                                <div class="wf-range-inputs">
                                    <input type="number" id="chromaticness-min" name="chromaticness-min" min="0" max="100" value="0" class="wf-range-input" placeholder="Min">
                                    <input type="number" id="chromaticness-max" name="chromaticness-max" min="0" max="100" value="100" class="wf-range-input" placeholder="Max">
                                </div>
                                <div class="wf-dual-range">
                                    <div class="wf-range-progress" id="chromaticness-progress"></div>
                                    <input type="range" id="chromaticness-range-min" min="0" max="100" value="0" class="wf-range-slider">
                                    <input type="range" id="chromaticness-range-max" min="0" max="100" value="100" class="wf-range-slider">
                                </div>
                            </div>
                        </div>
                        <div class="wf-filter-group">
                            <h4><i class="fas fa-circle"></i> Tummuusaste</h4>
                            <div class="wf-range-container">
                                <div class="wf-range-inputs">
                                    <input type="number" id="blackness-min" name="blackness-min" min="0" max="100" value="0" class="wf-range-input" placeholder="Min">
                                    <input type="number" id="blackness-max" name="blackness-max" min="0" max="100" value="100" class="wf-range-input" placeholder="Max">
                                </div>
                                <div class="wf-dual-range">
                                    <div class="wf-range-progress" id="blackness-progress"></div>
                                    <input type="range" id="blackness-range-min" min="0" max="100" value="0" class="wf-range-slider">
                                    <input type="range" id="blackness-range-max" min="0" max="100" value="100" class="wf-range-slider">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOW INSIDE FORM: Sorting controls -->
                <div class="wf-controls-row" id="wf-controls-row-3">
                    <div class="wf-results-info">
                        <span id="wf-visible-product-count"><?php echo esc_html($current_category->count); ?></span> / <?php echo esc_html($current_category->count); ?> Tuotetta
                    </div>
                    <div class="wf-sorting-controls">
                        <button class="wf-sort-btn" data-sort="price-asc" title="Hinta: Nouseva">€-€€€</button>
                        <button class="wf-sort-btn" data-sort="price-desc" title="Hinta: Laskeva">€€€-€</button>
                        <button class="wf-sort-btn" data-sort="_m2_price-asc" title="Neliöhinta: Nouseva">m<sup>2</sup> €-€€€</button>
                        <button class="wf-sort-btn" data-sort="_m2_price-desc" title="Neliöhinta: Laskeva">m<sup>2</sup> €€€-€</button>
                        <button class="wf-sort-btn" data-sort="lightness-desc" title="Järjestä vaaleimmasta tummimpaan">VAALEAT</button>
                        <button class="wf-sort-btn" data-sort="lightness-asc" title="Järjestä tummimmasta vaaleimpaan">TUMMAT</button>
                        <button class="wf-sort-btn" data-sort="title-asc" title="Nimi: A-Ö">A-Ö</button>
                        <button class="wf-sort-btn" data-sort="warranty-desc" title="Käyttöikä: Pisin ensin">KÄYTTÖIKÄ</button>
                        <button class="wf-sort-btn" data-sort="warranty-desc" title="Käyttöikä: Pisin ensin">SISÄTUOTTEET</button>
                        <button class="wf-sort-btn" data-sort="warranty-desc" title="Käyttöikä: Pisin ensin">ULKOTUOTTEET</button>
                    </div>
                </div>

            <!-- MOVED: Form closing tag now after all control rows -->
            </form>
        </div>

        <div class="wf-main-content-area">
        
            <div id="wf-tab-content-info" class="wf-tab-panel" style="display: none;">
                <?php if ($current_category->description) : ?>
                    <div class="wf-category-description-panel">
                        <?php echo wp_kses_post($current_category->description); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="wf-tab-content-products" class="wf-tab-panel">
                <div class="wf-products-section">
                    <div class="wf-products-wrapper">
                        
                        <!-- <div class="wf-results-count" style="display:none;"></div> -->

                        <div id="wf-products-container" class="wf-products-container">
                            <?php if (woocommerce_product_loop()) : ?>
                                
                                <?php do_action('woocommerce_before_shop_loop'); ?>

                                <div class="wf-products-grid">
                                    <?php
                                    while (have_posts()) {
                                        the_post();
                                        do_action('woocommerce_shop_loop');
                                        wc_get_template_part('content', 'product');
                                    }
                                    ?>
                                </div>

                                <?php do_action('woocommerce_after_shop_loop'); ?>

                            <?php else : ?>
                                
                                <div class="wf-no-products-found">
                                    <h3><i class="fas fa-search"></i> Tuotteita ei löytynyt</h3>
                                    <p>Valitettavasti hakuehdoillasi ei löytynyt tuotteita. Kokeile muuttaa suodattimia.</p>
                                </div>

                                <?php do_action('woocommerce_no_products_found'); ?>

                            <?php endif; ?>
                        </div>

                        <div class="wf-pagination">
                            <?php do_action('woocommerce_after_main_content'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="wf-color-modal" class="wf-color-modal">
        <div class="wf-color-modal-content">
            <div class="wf-color-modal-header">
                <h3><i class="fas fa-palette"></i> NCS Värinvalitsin</h3>
                <button type="button" class="wf-color-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="wf-color-modal-body">
                <div id="wf-ncs-color-circle"></div>
                <div class="wf-color-selection-info">
                    <div class="wf-selected-color-preview"></div>
                    <div class="wf-selected-color-text">Valitse väri yllä olevasta ympyrästä</div>
                </div>
            </div>
            <div class="wf-color-modal-footer">
                <button type="button" id="wf-color-apply" class="wf-btn wf-btn-primary"><i class="fas fa-check"></i> Käytä väriä</button>
                <button type="button" id="wf-color-cancel" class="wf-btn wf-btn-secondary"><i class="fas fa-times"></i> Peruuta</button>
            </div>
        </div>
    </div>

</div>

<?php
get_footer('shop');
?>
