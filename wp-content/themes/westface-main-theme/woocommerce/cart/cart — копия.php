<?php
/**
 * Custom Cart Page Template for B2B WooCommerce
 * 
 * This template overrides the default WooCommerce cart template
 * to provide a professional B2B experience with two distinct states:
 * 1. Empty cart state with Finnish messaging
 * 2. Populated cart state with professional table layout
 * 
 * @package WestfaceProfessional
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="woocommerce-cart-form-wrapper">
    <?php if (WC()->cart->is_empty()) : ?>
        <!-- Empty Cart State -->
        <div class="cart-empty-state">
            <div class="cart-empty-content">
                <h2 class="cart-empty-heading"><?php esc_html_e('Ostoskori on tyhjä', 'westface-professional'); ?></h2>
                <div class="cart-empty-actions">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="button wc-backward btn-back-to-shop">
                        <?php esc_html_e('Takaisin kauppaan', 'westface-professional'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <!-- Populated Cart State - B2B Professional Layout -->
        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <?php do_action('woocommerce_before_cart_table'); ?>

            <div class="cart-table-wrapper">
                <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="product-thumbnail"><?php esc_html_e('Kuva', 'westface-professional'); ?></th>
                            <th class="product-name"><?php esc_html_e('Tuote & SKU', 'westface-professional'); ?></th>
                            <th class="product-price"><?php esc_html_e('Yksikköhinta', 'westface-professional'); ?></th>
                            <th class="product-quantity"><?php esc_html_e('Määrä', 'westface-professional'); ?></th>
                            <th class="product-subtotal"><?php esc_html_e('Yhteensä', 'westface-professional'); ?></th>
                            <th class="product-remove"><?php esc_html_e('Poista', 'westface-professional'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php do_action('woocommerce_before_cart_contents'); ?>

                        <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                ?>
                                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

                                    <!-- Product Thumbnail -->
                                    <td class="product-thumbnail">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

                                        if (!$product_permalink) {
                                            echo $thumbnail; // PHPCS: XSS ok.
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                        }
                                        ?>
                                    </td>

                                    <!-- Product Name & SKU -->
                                    <td class="product-name" data-title="<?php esc_attr_e('Tuote', 'westface-professional'); ?>">
                                        <div class="product-info">
                                            <?php
                                            if (!$product_permalink) {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
                                            } else {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                            }

                                            do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                            // Output meta data
                                            echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

                                            // SKU
                                            if ($_product->get_sku()) {
                                                echo '<div class="product-sku"><strong>' . esc_html__('SKU:', 'westface-professional') . '</strong> ' . esc_html($_product->get_sku()) . '</div>';
                                            }

                                            // Backorder notification
                                            if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                            }
                                            ?>
                                        </div>
                                    </td>

                                    <!-- Unit Price -->
                                    <td class="product-price" data-title="<?php esc_attr_e('Yksikköhinta', 'westface-professional'); ?>">
                                        <?php
                                        echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                                        ?>
                                    </td>

                                    <!-- Quantity with +/- buttons -->
                                    <td class="product-quantity" data-title="<?php esc_attr_e('Määrä', 'westface-professional'); ?>">
                                        <div class="quantity-wrapper">
                                            <?php
                                            if ($_product->is_sold_individually()) {
                                                $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                            } else {
                                                $product_quantity = woocommerce_quantity_input(
                                                    array(
                                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                                        'input_value'  => $cart_item['quantity'],
                                                        'max_value'    => $_product->get_max_purchase_quantity(),
                                                        'min_value'    => '0',
                                                        'product_name' => $_product->get_name(),
                                                        'classes'      => array('input-text', 'qty', 'text', 'ajax-quantity'),
                                                    ),
                                                    $_product,
                                                    false
                                                );
                                            }

                                            echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                                            ?>
                                            <div class="quantity-controls">
                                                <button type="button" class="qty-btn qty-minus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
                                                <button type="button" class="qty-btn qty-plus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="product-subtotal" data-title="<?php esc_attr_e('Yhteensä', 'westface-professional'); ?>">
                                        <?php
                                        echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                                        ?>
                                    </td>

                                    <!-- Remove Item -->
                                    <td class="product-remove">
                                        <?php
                                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'woocommerce_cart_item_remove_link',
                                            sprintf(
                                                '<a href="%s" class="remove ajax-remove-item" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">&times;</a>',
                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                esc_html__('Poista tuote korista', 'westface-professional'),
                                                esc_attr($product_id),
                                                esc_attr($_product->get_sku()),
                                                esc_attr($cart_item_key)
                                            ),
                                            $cart_item_key
                                        );
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>

                        <?php do_action('woocommerce_cart_contents'); ?>

                        <tr>
                            <td colspan="6" class="actions">
                                <?php if (wc_coupons_enabled()) { ?>
                                    <div class="coupon">
                                        <label for="coupon_code"><?php esc_html_e('Kuponki:', 'westface-professional'); ?></label>
                                        <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e('Kuponkikoodi', 'westface-professional'); ?>" />
                                        <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e('Käytä kuponkia', 'westface-professional'); ?>"><?php esc_attr_e('Käytä kuponkia', 'westface-professional'); ?></button>
                                        <?php do_action('woocommerce_cart_coupon'); ?>
                                    </div>
                                <?php } ?>

                                <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e('Päivitä ostoskori', 'westface-professional'); ?>"><?php esc_html_e('Päivitä ostoskori', 'westface-professional'); ?></button>

                                <?php do_action('woocommerce_cart_actions'); ?>

                                <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                            </td>
                        </tr>

                        <?php do_action('woocommerce_after_cart_contents'); ?>
                    </tbody>
                </table>
            </div>

            <?php do_action('woocommerce_after_cart_table'); ?>
        </form>

        <!-- Cart Totals Section -->
        <div class="cart-collaterals">
            <?php
            /**
             * Cart collaterals hook.
             *
             * @hooked woocommerce_cross_sell_display
             * @hooked woocommerce_cart_totals - 10
             */
            do_action('woocommerce_cart_collaterals');
            ?>
        </div>
    <?php endif; ?>
</div>

<?php do_action('woocommerce_after_cart'); ?>

<!-- Loading overlay for AJAX operations -->
<div id="cart-loading-overlay" class="cart-loading-overlay" style="display: none;">
    <div class="loading-spinner"></div>
</div>
