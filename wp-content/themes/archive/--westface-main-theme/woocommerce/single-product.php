<?php
/**
 * Custom Professional Single Product Template
 *
 * This template displays single products with a professional design
 * featuring color swatches, two-column layout, and enhanced styling.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

<div class="professional-single-product-wrapper">
	<?php
		/**
		 * woocommerce_before_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>

	<div class="professional-single-product-container">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php wc_get_template_part( 'content', 'single-product' ); ?>

		<?php endwhile; // end of the loop. ?>
	</div>

	<?php
		/**
		 * woocommerce_after_main_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<!-- Custom Professional Styling -->
	<style>
		/* Remove default sidebar */
		.professional-single-product-wrapper .sidebar,
		.professional-single-product-wrapper #sidebar,
		.professional-single-product-wrapper .widget-area,
		.professional-single-product-wrapper .secondary {
			display: none;
		}

		/* Professional container styling */
		.professional-single-product-wrapper {
			background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
			min-height: 100vh;
			padding: 40px 0;
		}

		.professional-single-product-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 0 20px;
			background: white;
			border-radius: 16px;
			box-shadow: 0 8px 32px rgba(0, 212, 170, 0.1);
			border: 1px solid rgba(0, 212, 170, 0.1);
			overflow: hidden;
		}

		/* Professional breadcrumb styling */
		.woocommerce-breadcrumb {
			background: linear-gradient(135deg, #00d4aa, #00b894);
			color: white;
			padding: 15px 30px;
			margin: 0 -20px 30px -20px;
			font-size: 0.9rem;
		}

		.woocommerce-breadcrumb a {
			color: rgba(255, 255, 255, 0.9);
			text-decoration: none;
			transition: color 0.3s ease;
		}

		.woocommerce-breadcrumb a:hover {
			color: white;
		}

		/* Professional notices styling */
		.woocommerce-notices-wrapper {
			margin: 20px 0;
		}

		.woocommerce-message,
		.woocommerce-error,
		.woocommerce-info {
			border-radius: 8px;
			padding: 15px 20px;
			margin-bottom: 15px;
			border-left: 4px solid #00d4aa;
			background: rgba(0, 212, 170, 0.1);
			color: #333;
		}

		.woocommerce-error {
			border-left-color: #e74c3c;
			background: rgba(231, 76, 60, 0.1);
		}

		/* Responsive design */
		@media (max-width: 768px) {
			.professional-single-product-wrapper {
				padding: 20px 0;
			}

			.professional-single-product-container {
				margin: 0 10px;
				border-radius: 12px;
			}

			.woocommerce-breadcrumb {
				padding: 12px 20px;
				margin: 0 -10px 20px -10px;
				font-size: 0.8rem;
			}
		}
	</style>
</div>

<?php
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

