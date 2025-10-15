<?php
/**
 * Custom Professional Product Category Template
 *
 * Enhanced category page with professional design,
 * category-specific features, and improved navigation.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     4.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get current category
$current_category = get_queried_object();

get_header( 'shop' ); ?>

<div class="professional-category-wrapper">
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

	<div class="professional-category-container">
		
		<!-- Professional Category Hero -->
		<div class="professional-category-hero">
			<div class="category-hero-content">
				<div class="category-icon">
					<i class="fas fa-layer-group"></i>
				</div>
				<h1 class="category-title"><?php echo esc_html($current_category->name); ?></h1>
				<?php if ($current_category->description) : ?>
					<div class="category-description">
						<?php echo wp_kses_post($current_category->description); ?>
					</div>
				<?php endif; ?>
				
				<!-- Category Stats -->
				<div class="category-stats">
					<div class="stat-item">
						<span class="stat-number"><?php echo esc_html($current_category->count); ?></span>
						<span class="stat-label">Tuotetta</span>
					</div>
					<div class="stat-item">
						<span class="stat-number"><?php echo count(get_term_children($current_category->term_id, 'product_cat')); ?></span>
						<span class="stat-label">Alakategoriaa</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Category Navigation -->
		<?php
		$child_categories = get_term_children($current_category->term_id, 'product_cat');
		if (!empty($child_categories)) :
		?>
		<div class="professional-category-navigation">
			<h3>Alakategoriat</h3>
			<div class="subcategory-grid">
				<?php foreach ($child_categories as $child_id) :
					$child_category = get_term($child_id, 'product_cat');
					if ($child_category && !is_wp_error($child_category)) :
						$category_link = get_term_link($child_category);
						$category_image = get_term_meta($child_category->term_id, 'thumbnail_id', true);
				?>
					<a href="<?php echo esc_url($category_link); ?>" class="subcategory-card">
						<div class="subcategory-icon">
							<?php if ($category_image) : ?>
								<?php echo wp_get_attachment_image($category_image, 'thumbnail'); ?>
							<?php else : ?>
								<i class="fas fa-folder"></i>
							<?php endif; ?>
						</div>
						<h4><?php echo esc_html($child_category->name); ?></h4>
						<span class="product-count"><?php echo esc_html($child_category->count); ?> tuotetta</span>
					</a>
				<?php endif; endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- Professional Products Section -->
		<?php if ( woocommerce_product_loop() ) : ?>

			<!-- Professional Shop Controls -->
			<div class="professional-shop-controls">
				<div class="shop-controls-left">
				
				</div>
				<div class="shop-controls-right">
					<!-- Custom View Toggle
					<div class="view-toggle">
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
					<p>Tässä kategoriassa ei ole vielä tuotteita. Tutustu muihin kategorioihimme tai palaa myöhemmin.</p>
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
		<div class="professional-category-features">
			<h3>Miksi valita <?php echo esc_html($current_category->name); ?>?</h3>
			<div class="category-features-grid">
				<div class="feature-card">
					<i class="fas fa-award"></i>
					<h4>Laadukas tuote</h4>
					<p>Korkealaatuiset materiaalit ja huolellinen valmistus</p>
				</div>
				<div class="feature-card">
					<i class="fas fa-shipping-fast"></i>
					<h4>Nopea toimitus</h4>
					<p>Toimitus 1-3 arkipäivässä suoraan varastostamme</p>
				</div>
				<div class="feature-card">
					<i class="fas fa-tools"></i>
					<h4>Asiantunteva asennus</h4>
					<p>Ammattitaitoinen asennus ja käyttöönotto</p>
				</div>
				<div class="feature-card">
					<i class="fas fa-headset"></i>
					<h4>Asiakaspalvelu</h4>
					<p>Ystävällinen asiakaspalvelu ja tekninen tuki</p>
				</div>
			</div>
		</div>

		<!-- Related Categories -->
		<?php
		$parent_id = $current_category->parent;
		if ($parent_id) {
			$sibling_categories = get_terms(array(
				'taxonomy' => 'product_cat',
				'parent' => $parent_id,
				'exclude' => array($current_category->term_id),
				'hide_empty' => true,
				'number' => 4
			));
		} else {
			$sibling_categories = get_terms(array(
				'taxonomy' => 'product_cat',
				'parent' => 0,
				'exclude' => array($current_category->term_id),
				'hide_empty' => true,
				'number' => 4
			));
		}

		if (!empty($sibling_categories) && !is_wp_error($sibling_categories)) :
		?>
		<div class="professional-related-categories">
			<h3>Muut kategoriat</h3>
			<div class="related-categories-grid">
				<?php foreach ($sibling_categories as $related_category) :
					$category_link = get_term_link($related_category);
					$category_image = get_term_meta($related_category->term_id, 'thumbnail_id', true);
				?>
					<a href="<?php echo esc_url($category_link); ?>" class="related-category-card">
						<div class="category-image">
							<?php if ($category_image) : ?>
								<?php echo wp_get_attachment_image($category_image, 'medium'); ?>
							<?php else : ?>
								<div class="category-placeholder">
									<i class="fas fa-folder"></i>
								</div>
							<?php endif; ?>
						</div>
						<div class="category-info">
							<h4><?php echo esc_html($related_category->name); ?></h4>
							<span class="product-count"><?php echo esc_html($related_category->count); ?> tuotetta</span>
						</div>
					</a>
				<?php endforeach; ?>
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

