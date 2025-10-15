<?php
/**
 * Custom Professional Product Content Template
 *
 * EnhaFed product loop item with color swatches,
 * professional styling, and improved user experience.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
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

$product_color = get_post_meta($product->get_id(), 'rgb_color_etu', true);
$product_color_back = get_post_meta($product->get_id(), 'rgb_color_taka', true);
$brand = get_post_meta($product->get_id(), 'brand', true);
$mallisto = get_post_meta($product->get_id(), 'mallisto', true);

// Get product images
$thumbnail_id = get_post_thumbnail_id($product->get_id());
$gallery_image_ids = $product->get_gallery_image_ids();
$second_image_id = !empty($gallery_image_ids) ? $gallery_image_ids[0] : null;

// Get image URLs
$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'woocommerce_thumbnail') : '';
$second_image_url = $second_image_id ? wp_get_attachment_image_url($second_image_id, 'woocommerce_thumbnail') : '';
?>

<li <?php wc_product_class( 'professional-product-item', $product ); ?>>
	<div class="professional-product-card">
		
		<?php
		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
		?>

	
		
		<!-- Always display color swatches, with images as background if available -->
		<div class="product-color-container">
			<div class="product-color-div product-color-front" style="<?php 
				if ($thumbnail_url) {
					echo 'background-image: url(' . esc_url($thumbnail_url) . '); background-size: cover; background-position: center;';
				} else {
					echo 'background-color: ' . esc_attr($product_color) . ';';
				}
			?>">
				<span class="swatch-label">ETUPUOLI</span>
			</div>
			<div class="product-color-div product-color-back" style="<?php 
				if ($second_image_url) {
					echo 'background-image: url(' . esc_url($second_image_url) . '); background-size: cover; background-position: center;';
				} else {
					echo 'background-color: ' . esc_attr($product_color_back) . ';';
				}
			?>">
				<span class="swatch-label">TAKAPUOLI</span>
			</div>
			
			<!-- Product Badges -->
			<div class="product-badges">
				<?php if ($product->is_on_sale()) : ?>
					<span class="badge sale-badge">
						<i class="fas fa-tag"></i>
						SALE
					</span>
				<?php endif; ?>
				
				<?php if (!$product->is_in_stock()) : ?>
					<span class="badge stock-badge">
						<i class="fas fa-times"></i>
						LOPPU
					</span>
				<?php endif; ?>
				
				<?php if ($product->is_featured()) : ?>
					<span class="badge featured-badge">
						<i class="fas fa-star"></i>
						SUOSITTU
					</span>
				<?php endif; ?>
			</div>
		</div>

		<!-- Professional Product Content -->
		<div class="professional-product-content">
			
			<?php if ( ! empty( $brand ) || ! empty( $mallisto ) ) : ?>
				<div class="professional-product-meta">
					<?php if ( ! empty( $brand ) ) : ?>
						<span class="brand"><?php echo esc_html( $brand ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $mallisto ) ) : ?>
						 <b>/</b><span class="collection"><?php echo esc_html( $mallisto ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- Product Title -->
			<div class="professional-product-title">
				<?php
				/**
				 * Hook: woocommerce_shop_loop_item_title.
				 *
				 * @hooked woocommerce_template_loop_product_title - 10
				 */
				do_action( 'woocommerce_shop_loop_item_title' );
				?>
			</div>

			<!-- Professional Dual Price Display -->
			<div class="professional-pricing-container">
				<div class="pricing-card primary-pricing">
					<div class="pricing-label">Edullisin Levyhinta</div>
					<div class="price-display">
						<?php echo $product->get_price_html(); ?>
						<span class="price-unit">/levy</span>
					</div>
				</div>
				<div class="pricing-card secondary-pricing">
					<div class="pricing-label">Edullisin neliöhinta</div>
					<div class="price-display">
						<?php 
						// Get the square meter price from meta field
						$m2_price = get_post_meta($product->get_id(), '_m2_price', true);
						if ($m2_price) {
							// Convert comma-separated decimal to proper float for wc_price
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

		</div>

		<?php
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 */
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		do_action( 'woocommerce_after_shop_loop_item' );
		?>
		
		<!-- Quote Request Button -->
		<div class="product-quote-request">
			<button class="quote-request-btn btn btn-primary">
				<i class="fas fa-file-invoice"></i> Jätä tarjouspyyntö
			</button>
		</div>

	</div>
</li>

