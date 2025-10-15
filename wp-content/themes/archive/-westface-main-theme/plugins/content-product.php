<?php
/**
 * Custom Professional Product Content Template
 *
 * Enhanced product loop item with color swatches,
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

$product_color = get_shop_product_color($product->get_name());
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

		<!-- Color Swatch (replaces product thumbnail) -->
		<div class="product-color-container">
			<div class="product-color-div product-color-front" style="background-color: <?php echo esc_attr($product_color); ?>;"></div>
			<div class="product-color-div product-color-back" style="background-color: <?php echo esc_attr($product_color); ?>;"></div>
			
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

<!-- Professional Product Card Styling -->
<style>
	/* Professional product grid */
	.woocommerce ul.products {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 20px;
		list-style: none;
		margin: 0;
		padding: 0;
	}

	/* Professional product item - smaller cards */
	.professional-product-item {
		margin: 0;
		padding: 0;
		background: transparent;
	}

	.professional-product-card {
		background: white;
		border-radius: 12px;
		overflow: hidden;
		box-shadow: 0 4px 20px rgba(0, 212, 170, 0.1);
		border: 1px solid rgba(0, 212, 170, 0.1);
		transition: all 0.3s ease;
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.professional-product-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 8px 30px rgba(0, 212, 170, 0.2);
	}

	/* Color swatch container */
	.product-color-container {
		position: relative;
		height: 180px;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.product-color-div {
		width: 100%;
		height: 100%;
		position: absolute;
		top: 0;
		left: 0;
	}

	.product-color-front {
		z-index: 2;
	}

	.product-color-back {
		z-index: 1;
		opacity: 0.7;
		transform: scale(1.1);
	}

	/* Product badges */
	.product-badges {
		position: absolute;
		top: 12px;
		left: 12px;
		display: flex;
		flex-direction: column;
		gap: 6px;
		z-index: 3;
	}

	.badge {
		background: rgba(0, 0, 0, 0.8);
		color: white;
		padding: 4px 8px;
		border-radius: 12px;
		font-size: 0.7rem;
		font-weight: 600;
		display: flex;
		align-items: center;
		gap: 4px;
		backdrop-filter: blur(10px);
	}

	.sale-badge {
		background: linear-gradient(135deg, #e74c3c, #c0392b);
	}

	.stock-badge {
		background: linear-gradient(135deg, #95a5a6, #7f8c8d);
	}

	.featured-badge {
		background: linear-gradient(135deg, #f39c12, #e67e22);
	}

	/* Professional product content */
	.professional-product-content {
		padding: 15px;
		flex: 1;
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	.professional-product-title h2 {
		color: #333;
		font-size: 0.95rem;
		font-weight: 600;
		line-height: 1.3;
		margin: 0;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.professional-product-title a {
		color: inherit;
		text-decoration: none;
		transition: color 0.3s ease;
	}

	.professional-product-title a:hover {
		color: #00d4aa;
	}

	/* Professional dual pricing container */
	.professional-pricing-container {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px;
		margin-top: auto;
	}

	.pricing-card {
		background: #f8f9fa;
		padding: 10px 8px;
		border-radius: 8px;
		text-align: center;
		border: 1px solid #e9ecef;
		transition: all 0.3s ease;
	}

	.pricing-card:hover {
		border-color: #00d4aa;
		transform: translateY(-1px);
	}

	.pricing-card h3 {
		margin: 0 0 6px 0;
		font-size: 0.7rem;
		font-weight: 600;
		color: #666;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.price-display {
		font-size: 0.85rem;
		font-weight: 700;
		color: #00d4aa;
		display: flex;
		align-items: baseline;
		justify-content: center;
		gap: 4px;
		flex-wrap: wrap;
	}

	.price-unit {
		font-size: 0.65rem;
		font-weight: 500;
		color: #999;
	}

	/* List view styles */
	.professional-products-container[data-view="list"] .woocommerce ul.products {
		grid-template-columns: 1fr;
		gap: 15px;
	}

	.professional-products-container[data-view="list"] .professional-product-card {
		display: flex;
		flex-direction: row;
		height: auto;
	}

	.professional-products-container[data-view="list"] .product-color-container {
		width: 150px;
		height: 120px;
		flex-shrink: 0;
	}

	.professional-products-container[data-view="list"] .professional-product-content {
		flex: 1;
		padding: 15px 20px;
	}

	.professional-products-container[data-view="list"] .professional-pricing-container {
		grid-template-columns: 1fr 1fr;
		gap: 10px;
		max-width: 300px;
	}

	/* Responsive design */
	@media (max-width: 768px) {
		.woocommerce ul.products {
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 15px;
		}

		.product-color-container {
			height: 150px;
		}

		.professional-product-content {
			padding: 12px;
		}

		.professional-pricing-container {
			gap: 6px;
		}

		.pricing-card {
			padding: 8px 6px;
		}

		.pricing-card h3 {
			font-size: 0.65rem;
		}

		.price-display {
			font-size: 0.8rem;
		}

		.professional-products-container[data-view="list"] .professional-product-card {
			flex-direction: column;
		}

		.professional-products-container[data-view="list"] .product-color-container {
			width: 100%;
			height: 150px;
		}
	}

	@media (max-width: 480px) {
		.woocommerce ul.products {
			grid-template-columns: 1fr 1fr;
			gap: 10px;
		}

		.product-color-container {
			height: 120px;
		}

		.professional-product-content {
			padding: 10px;
			gap: 8px;
		}

		.professional-product-title h2 {
			font-size: 0.85rem;
		}

		.pricing-card h3 {
			font-size: 0.6rem;
		}

		.price-display {
			font-size: 0.75rem;
		}
	}
</style>

