<?php
/**
 * Custom Professional Single Product Content Template
 *
 * Enhanced single product content with two-column layout,
 * color swatches, and professional styling.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

// Get product color for swatch
if (!function_exists('get_shop_product_color')) {
    function get_shop_product_color($product_name) {
        $name = strtolower($product_name);
        $colors = array(
            'lime green' => '#7EE639',
            'turf green' => '#4F7942',
            'cactus green' => '#6B8E23',
            'forest green' => '#2E332E',
            'brilliant green' => '#00FF7F',
            'dark green' => '#006400',
            'anthracite grey' => '#898C89',
            'zink grey' => '#898C89',
            'aquamarine' => '#7FFFD4',
            'atlantic' => '#4682B4',
            'blue' => '#0000FF',
            'verdigris' => '#43B3AE',
            'mahogany red' => '#9B4444',
            'ahogany red' => '#9B4444',
            'ark brown' => '#8B4513',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'red' => '#FF0000',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'grey' => '#808080',
            'gray' => '#808080',
            'beige' => '#F5F5DC'
        );
        
        foreach ($colors as $color_name => $hex) {
            if (strpos($name, $color_name) !== false) {
                return $hex;
            }
        }
        return '#CCCCCC'; // Default color
    }
}

$product_color = get_shop_product_color($product->get_name());
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'professional-single-product', $product ); ?>>

	<!-- Professional Two-Column Layout -->
	<div class="professional-product-layout">
		
		<!-- Left Column: Main Product Information -->
		<div class="professional-product-left-column">
			
			<!-- Professional Color Swatch -->
			<div class="professional-color-swatch-container">
				<div class="product-color-container">
					<div class="product-color-div product-color-front" style="background-color: <?php echo esc_attr($product_color); ?>;"></div>
					<div class="product-color-div product-color-back" style="background-color: <?php echo esc_attr($product_color); ?>;"></div>
					
					<div class="product-badge">
						<?php if ($product->is_on_sale()) : ?>
							<span class="sale-badge">SALE</span>
						<?php endif; ?>
						<?php if (!$product->is_in_stock()) : ?>
							<span class="stock-badge">LOPPU</span>
						<?php endif; ?>
						<?php if ($product->is_featured()) : ?>
							<span class="featured-badge">SUOSITTU</span>
						<?php endif; ?>
					</div>
				</div>
			</div>

		

			<!-- Professional Pricing Sections -->
			<div class="professional-pricing-container">
				<div class="pricing-card primary-pricing">
					<h3>Edullisin Levyhinta</h3>
					<div class="price-display">
						<?php echo $product->get_price_html(); ?>
						<span class="price-unit">/levy</span>
					</div>
				</div>
				<div class="pricing-card secondary-pricing">
					<h3>Edullisin neliöhinta</h3>
					<div class="price-display">
						<?php 
						$price = $product->get_price();
						if ($price) {
							echo wc_price($price);
						}
						?>
						<span class="price-unit">/m²</span>
					</div>
				</div>
			</div>

			<!-- Add to Cart Section -->
			<div class="professional-add-to-cart">
				<?php
				/**
				 * Hook: woocommerce_single_product_summary.
				 */
				add_action( 'woocommerce_template_single_add_to_cart', 30 );
			do_action( 'woocommerce_single_product_summary' );
			?>
		</div>

		<!-- Product Description -->
		<div class="professional-product-description">
				<h3>Tuotekuvaus</h3>
				<div class="description-content">
					<?php if ($product && $product->get_description()) : ?>
						<?php echo wp_kses_post($product->get_description()); ?>
					<?php else : ?>
						<p>Westface tarjoaa laadukkaita laminaattilevyjä, jotka soveltuvat erinomaisesti sisustukseen ja kalusteiden valmistukseen. Tuotteemme ovat kestäviä, helppohoitoisia ja saatavilla monissa eri väreissä ja pintarakenteissa.</p>
						<p>Laminaattilevymme valmistetaan korkeapainelaminaattitekniikalla, mikä takaa erinomaisen kestävyyden ja pitkäikäisyyden. Tuotteet soveltuvat sekä kotikäyttöön että ammattimaiseen rakentamiseen.</p>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<!-- Right Column: Additional Information -->
		<div class="professional-product-right-column">
			
			<!-- Product Specifications -->
			<div class="professional-specifications">
				<h3>Tuotetiedot</h3>
				<div class="specifications-grid">
					<?php if ($product && $product->get_weight()) : ?>
					<div class="spec-item">
						<span class="spec-label">Paino</span>
						<span class="spec-value"><?php echo esc_html($product->get_weight()); ?> kg</span>
					</div>
					<?php endif; ?>
					
					<?php if ($product && $product->has_dimensions()) : ?>
					<div class="spec-item">
						<span class="spec-label">Mitat</span>
						<span class="spec-value"><?php echo esc_html(wc_format_dimensions($product->get_dimensions(false))); ?></span>
					</div>
					<?php endif; ?>
					
					<?php if ($product && $product->get_sku()) : ?>
					<div class="spec-item">
						<span class="spec-label">Tuotekoodi</span>
						<span class="spec-value"><?php echo esc_html($product->get_sku()); ?></span>
					</div>
					<?php endif; ?>
					
					<?php if ($product) : ?>
					<div class="spec-item">
						<span class="spec-label">Kategoria</span>
						<span class="spec-value"><?php echo wc_get_product_category_list($product->get_id(), ', '); ?></span>
					</div>
					<?php endif; ?>
					
					<div class="spec-item">
						<span class="spec-label">Saatavuus</span>
						<span class="spec-value <?php echo $product->is_in_stock() ? 'in-stock' : 'out-of-stock'; ?>">
							<?php echo $product->is_in_stock() ? 'Varastossa' : 'Loppuunmyyty'; ?>
						</span>
					</div>
					
					<?php
					// Display custom attributes safely
					if ($product) {
						$attributes = $product->get_attributes();
						if ($attributes && is_array($attributes)) {
							foreach ($attributes as $attribute) :
								if ($attribute && $attribute->get_visible()) :
					?>
					<div class="spec-item">
						<span class="spec-label"><?php echo wc_attribute_label($attribute->get_name()); ?></span>
						<span class="spec-value"><?php echo $product->get_attribute($attribute->get_name()); ?></span>
					</div>
					<?php 
								endif;
							endforeach;
						}
					}
					?>
				</div>
			</div>

			<!-- Additional Information -->
			<div class="professional-additional-info">
				<h3>Lisätietoja</h3>
				<div class="info-cards">
					<div class="info-card">
						<h4>FORMICA Vivix 2</h4>
						<p>Korkealaatuinen laminaattilevy sisustuskäyttöön</p>
					</div>
					<div class="info-card">
						<h4>FORMICA</h4>
						<p>Maailman johtava laminaattivalmistaja</p>
					</div>
				</div>
			</div>

			<!-- Contact Information -->
			<div class="professional-contact-info">
				<h3>Ota yhteyttä</h3>
				<div class="contact-grid">
					<div class="contact-item">
						<i class="fas fa-phone"></i>
						<div>
							<strong>Puhelin</strong>
							<span>+358 123 456 789</span>
						</div>
					</div>
					<div class="contact-item">
						<i class="fas fa-envelope"></i>
						<div>
							<strong>Sähköposti</strong>
							<span>info@westface.fi</span>
						</div>
					</div>
					<div class="contact-item">
						<i class="fas fa-map-marker-alt"></i>
						<div>
							<strong>Osoite</strong>
							<span>Teollisuuskatu 1, 00100 Helsinki</span>
						</div>
					</div>
					<div class="contact-item">
						<i class="fas fa-clock"></i>
						<div>
							<strong>Aukioloajat</strong>
							<span>Ma-Pe: 8:00-16:00</span>
						</div>
					</div>
			<!-- WooCommerce Tabs in Right Column -->
			<div class="professional-product-tabs">
				<?php
				/**
				 * Hook: woocommerce_output_product_data_tabs
				 */
				woocommerce_output_product_data_tabs();
				?>
			</div>

		</div>
		<!-- End Right Column -->

	</div>
	<!-- End Two Column Layout -->

</div>

<!-- Full Width Sections (Upsells and Related Products) -->
<div class="professional-full-width-sections">
	<div class="container">
		<?php
		/**
		 * Hook: woocommerce_after_single_product_summary.
		 *
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		do_action( 'woocommerce_after_single_product_summary' );
		?>
	</div>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>

