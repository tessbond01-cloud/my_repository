<?php
/**
 * Custom Professional Archive Product Template
 *
 * Enhanced shop and category pages with professional design,
 * improved filtering, and modern layout.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div class="professional-archive-wrapper">
	<?php
	/**
	 * Hook: woocommerce_before_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked woocommerce_breadcrumb - 20
	 * @hooked WC_Structured_Data::generate_website_data() - 30
	 */
	do_action( 'woocommerce_before_main_content' );
	?>

	<div class="professional-archive-container">
		<?php if ( woocommerce_product_loop() ) : ?>

			<!-- Professional Shop Controls -->
			<div class="professional-shop-controls">
				<div class="shop-controls-left">
				<?php
					/**
					 * Hook: woocommerce_shop_loop_header.
					 *
					 * @since 8.6.0
					 *
					 * @hooked woocommerce_product_taxonomy_archive_header - 10
					 */
					do_action( 'woocommerce_shop_loop_header' );
					?>
					
					<!-- Custom Category Description -->
					<?php if (is_product_category()) : ?>
						<?php $category = get_queried_object(); ?>
						<?php if ($category && $category->description) : ?>
							<div class="professional-category-description">
								<div class="category-description-content">
									<?php echo wp_kses_post($category->description); ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="shop-controls-right">
					<!-- Custom View Toggle -->
					<!-- <div class="view-toggle">
						<button class="view-btn grid-view active" data-view="grid" title="Grid View">
							<i class="fas fa-th"></i>
						</button>
						<button class="view-btn list-view" data-view="list" title="List View">
							<i class="fas fa-list"></i>
						</button>
					</div> -->
					
					<!-- Professional Ordering -->
					<div class="professional-ordering">
						<?php
						add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
						do_action( 'woocommerce_before_shop_loop' );
						?>
					</div>
				</div>
			</div>

			<!-- Product Categories Section (Only on main shop page) -->
			<?php if (is_shop() && !is_product_category() && !is_product_tag()) : ?>
			<div class="professional-categories-section">
				<div class="categories-header">
					<h2><i class="fas fa-th-large"></i> Tuotekategoriat</h2>
					<p>Selaa tuotteitamme kategorioittain</p>
				</div>
				
				<div class="categories-grid">
					<?php
					$product_categories = get_terms(array(
						'taxonomy' => 'product_cat',
						'hide_empty' => true,
						'parent' => 0, // Only top-level categories
						'number' => 6, // Limit to 6 categories
						'exclude' => array(15) // Exclude 'Uncategorised' if needed
					));
					
					if ($product_categories && !is_wp_error($product_categories)) :
						foreach ($product_categories as $category) :
							$category_link = get_term_link($category);
							$category_image = get_term_meta($category->term_id, 'thumbnail_id', true);
							$category_image_url = $category_image ? wp_get_attachment_image_url($category_image, 'medium') : '';
					?>
					<div class="category-card">
						<a href="<?php echo esc_url($category_link); ?>" class="category-link">
							<div class="category-image">
								<?php if ($category_image_url) : ?>
									<img src="<?php echo esc_url($category_image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
								<?php else : ?>
									<div class="category-placeholder">
										<i class="fas fa-cube"></i>
									</div>
								<?php endif; ?>
								
							</div>
							<div class="category-info">
								<h3><?php echo esc_html($category->name); ?></h3>
								<?php if ($category->description) : ?>
									<p><?php echo esc_html(wp_trim_words($category->description, 15)); ?></p>
								<?php endif; ?>
								<span class="category-count"><?php echo $category->count; ?> tuotetta</span>

							</div>
						</a>
					</div>
					<?php 
						endforeach;
					endif;
					?>
				</div>
			</div>
			<?php endif; ?>

			<!-- Professional Product Grid -->
			<div class="professional-products-container" data-view="grid">
				<?php
				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();

						/**
						 * Hook: woocommerce_shop_loop.
						 */
						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();
				?>
			</div>

			<!-- Professional Pagination -->
			<div class="professional-pagination">
				<?php
				/**
				 * Hook: woocommerce_after_shop_loop.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
				?>
			</div>

		<?php else : ?>

			<!-- Professional No Products Found -->
			<div class="professional-no-products">
				<div class="no-products-content">
					<i class="fas fa-search"></i>
					<h3>Tuotteita ei löytynyt</h3>
					<p>Valitettavasti hakuehdoillasi ei löytynyt tuotteita. Kokeile muuttaa hakuehtoja tai selaa kaikkia tuotteitamme.</p>
					<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
						<i class="fas fa-arrow-left"></i>
						Takaisin kauppaan
					</a>
				</div>
			</div>

			<?php
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
			?>

		<?php endif; ?>

		<!-- Professional Category Features -->
		<?php if (is_product_category()) : ?>
			<div class="professional-category-features">
				<div class="category-features-grid">
					<div class="feature-card">
						<i class="fas fa-shipping-fast"></i>
						<h4>Nopea toimitus</h4>
						<p>Toimitus 1-3 arkipäivässä</p>
					</div>
					<div class="feature-card">
						<i class="fas fa-award"></i>
						<h4>Laadukas tuote</h4>
						<p>Korkealaatuiset materiaalit</p>
					</div>
					<div class="feature-card">
						<i class="fas fa-tools"></i>
						<h4>Asiantunteva asennus</h4>
						<p>Ammattitaitoinen asennus</p>
					</div>
					<div class="feature-card">
						<i class="fas fa-phone-alt"></i>
						<h4>Asiakaspalvelu</h4>
						<p>Ystävällinen asiakaspalvelu</p>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>

	<?php
	/**
	 * Hook: woocommerce_after_main_content.
	 *
	 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'woocommerce_after_main_content' );
	?>

	

	<!-- Professional JavaScript -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// View toggle functionality
			const viewButtons = document.querySelectorAll('.view-btn');
			const productsContainer = document.querySelector('.professional-products-container');
			
			viewButtons.forEach(button => {
				button.addEventListener('click', function() {
					const view = this.dataset.view;
					
					// Update active button
					viewButtons.forEach(btn => btn.classList.remove('active'));
					this.classList.add('active');
					
					// Update container view
					if (productsContainer) {
						productsContainer.setAttribute('data-view', view);
					}
				});
			});
		});
	</script>
</div>

<?php get_footer( 'shop' ); ?>

