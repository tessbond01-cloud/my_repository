<?php
/**
 * Custom Professional Up-sells Template
 *
 * This template is responsible for displaying product up-sells on the single product page.
 * It uses a professional, responsive grid layout and leverages the custom `content-product.php`
 * for a consistent look and feel.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( $upsells ) : ?>

	<section class="up-sells upsells products professional-product-grid">

		<?php
		$heading = apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;', 'woocommerce' ) );

		if ( $heading ) :
			?>
			<h6 class="professional-section-title"><?php echo esc_html( $heading ); ?></h6>
		<?php endif; ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php foreach ( $upsells as $upsell ) : ?>

				<?php
				$post_object = get_post( $upsell->get_id() );
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
