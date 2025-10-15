<?php
/**
 * Enhanced WooCommerce Archive Product Template - COMPLETE FIXED VERSION
 * 
 * Features:
 * - Full-width AJAX-powered filtering bar at top
 * - Professional category grid for main shop page
 * - Interactive NCS color picker with modal
 * - Responsive design with mobile support
 * - All bugs fixed and functionality restored
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<!-- Font Awesome Icons -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->

<?php
// Check if we're on the main shop page (not a category or filtered view)
$is_shop_home = is_shop() && !is_product_category() && !is_product_tag() && !isset($_GET['filter']);
?>

<?php if ($is_shop_home) : ?>
    
    <!-- ========== SECTION 1: HERO WELCOME SECTION ========== -->
    <section class="wf-hero-welcome">
        <div class="wf-hero-container">
            <div class="wf-hero-content">
                <div class="wf-hero-left">
                    <h1 class="wf-hero-title">Tervetuloa verkkokauppaan</h1>
                    <p class="wf-hero-description">
                        Tarjoamme laajan valikoiman korkealaatuisia laminaattituotteita sisustukseen ja rakentamiseen. 
                        Tuotteemme edustavat huippuluokan suunnittelua, kestävyyttä ja estetiikkaa. Löydä täydellinen 
                        ratkaisu projektiisi tutkimalla monipuolista valikoimaamme.
                    </p>
                </div>
                <div class="wf-hero-right">
                    <!-- <img src="<?php echo get_template_directory_uri(); ?>/assets/images/hero-shop.jpg" 
                         alt="Premium laminaattituotteet" 
                         class="wf-hero-image"> -->
                    <img src="https://wf.gromi.fi/wp-content/uploads/2016/02/MAR4736.jpg" 
                         alt="Premium laminaattituotteet" 
                         class="wf-hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- ========== SECTION 2: BREADCRUMBS ========== -->
    <section class="wf-breadcrumbs-section">
        <div class="wf-breadcrumbs-container">
            <nav class="wf-breadcrumbs" aria-label="Breadcrumb">
                <a href="/info" class="wf-breadcrumb-link">Etusivu</a>
                <span class="wf-breadcrumb-separator"><i class="fas fa-chevron-right"></i></span>
                <span class="wf-breadcrumb-current">Verkkokauppa</span>
            </nav>
        </div>
    </section>

    <!-- ========== SECTION 3: MATERIALS AND PRODUCTS (CATEGORIES) ========== -->
    <section class="wf-categories-section">
        <div class="wf-categories-container">
            <div class="wf-section-header">
                <h2 class="wf-section-title">Materiaalit ja tuotteet</h2>
                <p class="wf-section-subtitle">
                    Tutustu laajaan tuotevalikoimaamme ja löydä juuri sinun projektiisi sopivat materiaalit
                </p>
            </div>

            <div class="wf-categories-grid">
                <?php
                // Get main product categories
                $main_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'parent' => 0,
                    'orderby' => 'name',
                    'order' => 'ASC'
                ));

                if (!empty($main_categories) && !is_wp_error($main_categories)) :
                    foreach ($main_categories as $category) :
                        // Get subcategories
                        $subcategories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'hide_empty' => true,
                            'parent' => $category->term_id,
                            'number' => 5
                        ));
                        
                        $category_link = get_term_link($category);
                        $category_image_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $category_image = $category_image_id ? wp_get_attachment_url($category_image_id) : '';
                ?>
                
                <div class="wf-category-card">
                    <?php if ($category_image) : ?>
                        <div class="wf-category-image">
                            <img src="<?php echo esc_url($category_image); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="wf-category-content">
                        <h3 class="wf-category-name">
                            <i class="fas fa-cube"></i>
                            <?php echo esc_html($category->name); ?>
                        </h3>
                        
                        <?php if (!empty($subcategories) && !is_wp_error($subcategories)) : ?>
                            <ul class="wf-subcategory-list">
                                <?php foreach ($subcategories as $subcategory) : ?>
                                    <li>
                                        <i class="fas fa-angle-right"></i>
                                        <?php echo esc_html($subcategory->name); ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($subcategories) >= 5) : ?>
                                    <li class="wf-more-items">
                                        <i class="fas fa-ellipsis-h"></i>
                                        ja lisää...
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url($category_link); ?>" class="wf-category-btn">
                            <i class="fas fa-arrow-right"></i>
                            Selaa tuotteita
                        </a>
                    </div>
                </div>
                
                <?php 
                    endforeach;
                else : 
                ?>
                    <!-- Fallback placeholder cards if no categories exist -->
                    <div class="wf-category-card">
                        <div class="wf-category-content">
                            <h3 class="wf-category-name">
                                <i class="fas fa-cube"></i>
                                Laminaatit
                            </h3>
                            <ul class="wf-subcategory-list">
                                <li><i class="fas fa-angle-right"></i> Pintalaminaatit</li>
                                <li><i class="fas fa-angle-right"></i> Ydinlaminaatit</li>
                                <li><i class="fas fa-angle-right"></i> Rakenteelliset laminaatit</li>
                            </ul>
                            <a href="#" class="wf-category-btn">
                                <i class="fas fa-arrow-right"></i>
                                Selaa tuotteita
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ========== SECTION 4: MANUFACTURERS (BRANDS) ========== -->
    <section class="wf-manufacturers-section">
        <div class="wf-manufacturers-container">
            <div class="wf-section-header">
                <h2 class="wf-section-title">Valmistajat</h2>
                <p class="wf-section-subtitle">
                    Työskentelemme alan johtavien valmistajien kanssa tarjotaksemme sinulle parhaat tuotteet
                </p>
            </div>

            <div class="wf-manufacturers-grid">
                <?php
                // Get all unique manufacturers from product meta
                global $wpdb;
                $manufacturers = $wpdb->get_results("
                    SELECT DISTINCT meta_value 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'brand' 
                    AND meta_value != '' 
                    ORDER BY meta_value ASC
                ");

                if (!empty($manufacturers)) :
                    foreach ($manufacturers as $manufacturer) :
                        $brand_name = $manufacturer->meta_value;
                        $brand_slug = sanitize_title($brand_name);
                        
                        // Get product count for this manufacturer
                        $product_count = $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(DISTINCT p.ID)
                            FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                            WHERE p.post_type = 'product'
                            AND p.post_status = 'publish'
                            AND pm.meta_key = 'brand'
                            AND pm.meta_value = %s
                        ", $brand_name));
                ?>
                
                <div class="wf-manufacturer-card">
                    <div class="wf-manufacturer-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/brands/<?php echo esc_attr($brand_slug); ?>.png" 
                             alt="<?php echo esc_attr($brand_name); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="wf-manufacturer-logo-fallback" style="display: none;">
                            <i class="fas fa-industry"></i>
                            <span><?php echo esc_html($brand_name); ?></span>
                        </div>
                    </div>
                    
                    <div class="wf-manufacturer-content">
                        <h3 class="wf-manufacturer-name"><?php echo esc_html($brand_name); ?></h3>
                        <p class="wf-manufacturer-description">
                            Korkealaatuisia laminaattituotteita ammattilaisille ja kuluttajille. 
                            <?php echo esc_html($product_count); ?> tuotetta saatavilla.
                        </p>
                        <a href="<?php echo esc_url(add_query_arg('filter_brand', $brand_slug, wc_get_page_permalink('shop'))); ?>" 
                           class="wf-manufacturer-btn">
                            <i class="fas fa-arrow-right"></i>
                            Katso valmistajan tuotteet
                        </a>
                    </div>
                </div>
                
                <?php 
                    endforeach;
                else : 
                ?>
                    <!-- Fallback placeholder cards if no manufacturers exist -->
                    <div class="wf-manufacturer-card">
                        <div class="wf-manufacturer-logo">
                            <div class="wf-manufacturer-logo-fallback" style="display: flex;">
                                <i class="fas fa-industry"></i>
                                <span>Fundermax</span>
                            </div>
                        </div>
                        <div class="wf-manufacturer-content">
                            <h3 class="wf-manufacturer-name">Fundermax</h3>
                            <p class="wf-manufacturer-description">
                                Korkealaatuisia laminaattituotteita ammattilaisille ja kuluttajille.
                            </p>
                            <a href="#" class="wf-manufacturer-btn">
                                <i class="fas fa-arrow-right"></i>
                                Katso valmistajan tuotteet
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php endif; ?>

<div class="wf-archive-container">
    <?php
    /**
     * Hook: woocommerce_before_main_content.
     */
    do_action('woocommerce_before_main_content');
    ?>

    <div class="wf-archive-wrapper">
        
        <div class="wf-refactored-controls">

            <!-- Form wraps ALL control rows -->
            <form id="wf-product-filters" class="wf-product-filters">
                <?php wp_nonce_field('wf_filter_nonce', 'nonce'); ?>
                <input type="hidden" name="_ncs_ending" id="wf-ncs-ending-value" value="">

                <div class="wf-controls-row" id="wf-controls-row-1">
                    <div class="wf-controls-left">
                        <h2 class="wf-page-title">Kaikki tuotteet</h2>
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

                        <!-- Column 2: Paksuus -->
                        <div class="wf-filter-column">
                            <h4><i class="fas fa-expand-arrows-alt"></i> Paksuus</h4>
                            <div class="wf-checkbox-group">
                                <?php
                                global $wpdb;
                                $sizes = $wpdb->get_results("
                                    SELECT DISTINCT pm.meta_value 
                                    FROM {$wpdb->postmeta} pm
                                    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                                    WHERE pm.meta_key = 'attribute_thickness' 
                                    AND pm.meta_value != '' 
                                    AND p.post_type = 'product'
                                    AND p.post_status = 'publish'
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
                                $lifetime_recommendations = $wpdb->get_results("
                                    SELECT DISTINCT CONCAT(COALESCE(w.meta_value, ''), ' / ', COALESCE(sd.meta_value, '')) as combined_value
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
                
                <!-- Color buttons and range sliders -->
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

                <!-- Sorting controls -->
                <div class="wf-controls-row" id="wf-controls-row-3">
                    <div class="wf-results-info">
                        <span id="wf-visible-product-count">0</span> / <span id="wf-total-product-count">0</span> Tuotetta
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
                        <button class="wf-sort-btn" data-sort="indoor" title="Sisätuotteet">SISÄTUOTTEET</button>
                        <button class="wf-sort-btn" data-sort="outdoor" title="Ulkotuotteet">ULKOTUOTTEET</button>
                    </div>
                </div>

            </form>
        </div>

        <div class="wf-main-content-area">
        
            <div id="wf-tab-content-products" class="wf-tab-panel">
                <div class="wf-products-section">
                    <div class="wf-products-wrapper">
                        
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

    <!-- NCS Color Picker Modal -->
    <div id="wf-color-modal" class="wf-color-modal">
        <div class="wf-color-modal-content">
            <div class="wf-color-modal-header">
                <h3><i class="fas fa-palette"></i> NCS Värinvalitsin</h3>
                <button type="button" class="wf-color-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="wf-color-modal-body">
                <div id="wf-ncs-color-circle"></div>
                <div class="wf-color-selection-info">
                    <div class="wf-selected-color-preview"></div>
                    <div class="wf-selected-color-text">Valitse väri yllä olevasta ympyrästä</div>
                </div>
            </div>
            <div class="wf-color-modal-footer">
                <button type="button" id="wf-color-apply" class="wf-btn wf-btn-primary">
                    <i class="fas fa-check"></i> Käytä väriä
                </button>
                <button type="button" id="wf-color-cancel" class="wf-btn wf-btn-secondary">
                    <i class="fas fa-times"></i> Peruuta
                </button>
            </div>
        </div>
    </div>

</div>

<?php
get_footer('shop');
?>

