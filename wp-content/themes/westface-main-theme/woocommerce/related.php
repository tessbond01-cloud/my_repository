<?php
/**
 * Custom Professional Related Products Template
 *
 * This template is responsible for displaying related products on the single product page.
 * It uses a professional, responsive grid layout and leverages the custom `content-product.php`
 * for a consistent look and feel.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( $related_products ) : ?>

	<section class="related products professional-product-grid">

		<?php
		$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

		if ( $heading ) :
			?>
			<h2 class="professional-section-title"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php foreach ( $related_products as $related_product ) : ?>

				<?php
				$post_object = get_post( $related_product->get_id() );
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$GLOBALS['post'] = $post_object; // Setup post data for wc_get_template_part.
				setup_postdata( $post_object );

				wc_get_template_part( 'content', 'product' );
				?>

			<?php endforeach; ?>

		<?php woocommerce_product_loop_end(); ?>

	</section>
	<?php
endif;

wp_reset_postdata();
