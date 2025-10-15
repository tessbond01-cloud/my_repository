<?php
/**
 * The template for displaying single product content.
 *
 * This template has been completely restructured to a two-column layout with full-width sections below.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined("ABSPATH") || exit();

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action("woocommerce_before_single_product");

if (post_password_required()) {
  echo get_the_password_form(); // WPCS: XSS ok.
  return;
}

// Helper function to get variation stock status
if (!function_exists("get_variation_stock_status")) {
  function get_variation_stock_status($variation_data) {
    if ($variation_data["is_in_stock"]) {
      return $variation_data["backorders_allowed"] ? "tilattavissa" : "varastossa";
    }
    return "loppu";
  }
}

// Helper function to get specific attribute value
if (!function_exists("get_attribute_value")) {
  function get_attribute_value($attributes, $target_attribute) {
    $target = strtolower(trim($target_attribute));
    foreach ($attributes as $attribute_name => $attribute_value) {
      $clean_name = str_replace("attribute_", "", $attribute_name);
      if (stripos($clean_name, "pa_") === 0) {
        $clean_name = substr($clean_name, 3);
      }
      $clean_name = str_replace("-", " ", $clean_name);
      $clean_name = strtolower(trim($clean_name));
      $clean_name = preg_replace("/\s+/", " ", $clean_name);

      if ($clean_name === $target) {
        return $attribute_value;
      }
    }
    return "";
  }
}

// Helper function to format dimensions
if (!function_exists("format_dimensions")) {
  function format_dimensions($attributes) {
    $leveys = get_attribute_value($attributes, "leveys mm");
    $pituus = get_attribute_value($attributes, "pituus mm");

    if ($leveys && $pituus) {
      $leveys_clean = preg_replace("/[^0-9.]/", "", $leveys);
      $pituus_clean = preg_replace("/[^0-9.]/", "", $pituus);
      return $leveys_clean . " × " . $pituus_clean . " mm";
    } elseif ($leveys) {
      $leveys_clean = preg_replace("/[^0-9.]/", "", $leveys);
      return $leveys_clean . " mm (leveys)";
    } elseif ($pituus) {
      $pituus_clean = preg_replace("/[^0-9.]/", "", $pituus);
      return $pituus_clean . " mm (pituus)";
    }

    return "-";
  }
}

// Helper function to get leveys value for sorting
if (!function_exists("get_leveys_value")) {
  function get_leveys_value($attributes) {
    foreach ($attributes as $attribute_name => $attribute_value) {
      $clean_name = str_replace("attribute_", "", $attribute_name);
      if (stripos($clean_name, "pa_") === 0) {
        $clean_name = substr($clean_name, 3);
      }
      $clean_name = str_replace("-", " ", $clean_name);
      $clean_name = strtolower($clean_name);

      if ($clean_name === "leveys mm") {
        return floatval(preg_replace("/[^0-9.]/", "", $attribute_value));
      }
    }
    return 0;
  }
}

$product_color = get_post_meta($product->get_id(), "rgb_color_etu", true);
$product_color_back = get_post_meta($product->get_id(), "rgb_color_taka", true);
$product_ncs = get_post_meta($product->get_id(), "_ncs_color_etu", true);
$product_ncs_back = get_post_meta($product->get_id(), "_ncs_color_taka", true);

// Get all variations and group by thickness
$available_variations = [];
$grouped_variations = [];

if ($product->is_type("variable")) {
  $available_variations = $product->get_available_variations();

  foreach ($available_variations as $variation) {
    $thickness = get_attribute_value($variation["attributes"], "paksuus mm");
    if (!$thickness) {
      $thickness = get_attribute_value($variation["attributes"], "paksuus");
    }
    if (!$thickness) {
      $thickness = "Unknown";
    }

    if (!isset($grouped_variations[$thickness])) {
      $grouped_variations[$thickness] = [];
    }

    $grouped_variations[$thickness][] = $variation;
  }

  uksort($grouped_variations, function ($a, $b) {
    $a_num = floatval(preg_replace("/[^0-9.]/", "", $a));
    $b_num = floatval(preg_replace("/[^0-9.]/", "", $b));
    return $a_num <=> $b_num;
  });

  foreach ($grouped_variations as $thickness => &$variations) {
    usort($variations, function ($a, $b) {
      $leveys_a = get_leveys_value($a["attributes"]);
      $leveys_b = get_leveys_value($b["attributes"]);
      return $leveys_a <=> $leveys_b;
    });
  }
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class("professional-single-product", $product); ?>>
  <div class="professional-product-layout">
    <div class="product-main-container">
      <div class="product-info-column">
        <!-- <?php woocommerce_breadcrumb(); ?> -->

        <div class="main-title-container">
          <?php
          // Add filter to prepend brand to product title
          add_filter('the_title', function($title, $id) use ($product) {
            if ($id === $product->get_id() && in_the_loop() && is_product()) {
              $brand = get_post_meta($product->get_id(), "brand", true);
              if (!empty($brand)) {
                return esc_html($brand) . " " . $title;
              }
            }
            return $title;
          }, 10, 2);
          
          woocommerce_template_single_title();
          
          // Remove filter after use to avoid affecting other titles
          remove_all_filters('the_title', 10);
          ?>
        </div>

        <?php
        $sku = is_object($product) ? $product->get_sku() : "";
        if (!empty($sku)) {
          echo "<div class=\"product-sku\">" . esc_html($sku) . "</div>";
        }
        ?>

        <div class="professional-color-swatch-container">
          <div class="product-color-container">
            <a href="#" class="add-to-wishlist-icon" aria-label="Lisää toivelistaan">
              <i class="far fa-star"></i>
            </a>
            <?php
            $thumbnail_id = get_post_thumbnail_id($product->get_id());
            $gallery_image_ids = $product->get_gallery_image_ids();
            $second_image_id = !empty($gallery_image_ids) ? $gallery_image_ids[0] : null;
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, "large") : "";
            $second_image_url = $second_image_id ? wp_get_attachment_image_url($second_image_id, "large") : "";
            ?>
            <div class="product-color-div product-color-front" style="<?php 
                if ($thumbnail_url) {
                    echo "background-image: url(" . esc_url($thumbnail_url) . "); background-size: cover; background-position: center;";
                } else {
                    echo "background-color: " . esc_attr($product_color) . ";";
                }
            ?>">
              <span class="swatch-label">ETUPUOLI</span>
              <?php if (!empty($product_ncs)): ?>
                <div class="color-info" style="color: #fff;">
                  <span class="ncs-code"><?php echo esc_html($product_ncs); ?></span>
                </div>
              <?php endif; ?>
            </div>
            <div class="product-color-div product-color-back" style="<?php 
                if ($second_image_url) {
                    echo "background-image: url(" . esc_url($second_image_url) . "); background-size: cover; background-position: center;";
                } else {
                    echo "background-color: " . esc_attr($product_color_back) . ";";
                }
            ?>">
              <span class="swatch-label">TAKAPUOLI</span>
              <?php if (!empty($product_ncs_back)): ?>
                <div class="color-info" style="color: #fff;">
                  <span class="ncs-code"><?php echo esc_html($product_ncs_back); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="static-product-description">
          <?php /* TODO: This static description block should be replaced with dynamic content from the product\'s main description field. */ ?>
          <p>Levyn etupuoli on Fundermax Voyager Oak, joka on väriltään ruskea (NCS S2060-R90B). Sen kiiltoaste on puolihimmeä ja peltityyppi on NT Color-mallistosta.</p>
          <p>Levyn takapuoli on Fundermax Voyager Oak, joka on väriltään ruskea (NCS S2060-R90B). Sen kiiltoaste on puolihimmeä ja peltityyppi on NT Color-mallistosta.</p>
          <p>Teknisiltä ominaisuuksilan levy on 6 mm paksu, 1300 mm leveä ja 2800 mm pitkä, jolloin yhden levyn pinta-ala on 3,64 m². Tuotteen paloluokka on B_s2_d0 ja se on iskunkestävä. Arvioitu rakennekesto on 50 vuotta, suositeltu huoltoväli 40 vuotta ja valmistajan myöntämä takuu on 10 vuotta.</p>
          <p>Tuotteen valmistaja on Fundermax. Tuotteelle on käytettävissä 3 eri asennusjärjestelmää: Alpha-järjestelmä, Classic-järjestelmä, Beta järjestelmä.</p>
        </div>

        <div class="professional-pricing-container">
          <div class="pricing-card primary-pricing">
            <div class="pricing-label">Edullisin Levyhinta</div>
            <div class="price-display">
              <?php
              $price = $product->get_price();
              if ($price) {
                $price_float = floatval(str_replace(",", ".", $price));
                echo wc_price($price_float);
              } else {
                echo "N/A";
              }
              ?>
              <span class="price-unit">/levy</span>
            </div>
          </div>
          <div class="pricing-card secondary-pricing">
            <div class="pricing-label">Edullisin neliöhinta</div>
            <div class="price-display">
              <?php
              $m2_price = get_post_meta($product->get_id(), "_m2_price", true);
              if ($m2_price) {
                $m2_price_float = floatval(str_replace(",", ".", $m2_price));
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

      <div class="product-actions-column">
        <!-- <div class="professional-color-swatch-container"></div> -->
        <div class="action-buttons-container">
          <button class="btn btn-secondary action-button-sample">Tilaa mallipalat</button>
          <button class="btn btn-primary action-button-add" data-variation-id="<?php echo esc_attr($variation["variation_id"]); ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">Lisää ostoskoriin</button>
        </div>
        <script>
        jQuery(document).ready(function($) {
          $('.action-button-add').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var variationId = $btn.data('variation-id');
            var productId = $btn.data('product-id');
            // Debug output
            console.log('action-button-add click:', {variationId, productId});
            // Ищем количество рядом с кнопкой, если есть
            var quantity = 1;
            var $qtyInput = $btn.closest('.quantity-input-wrapper').find('.quantity-input');
            if ($qtyInput.length) {
              quantity = $qtyInput.val();
            }
            // Собираем атрибуты вариации из строки, если есть
            var attributes = {};
            var $row = $btn.closest('tr.variation-row');
            if ($row.length) {
              // Можно добавить сбор атрибутов, если нужно
            }
            $.ajax({
              url: professional_woocommerce_ajax.ajax_url,
              type: 'POST',
              data: {
                action: 'custom_add_to_cart',
                product_id: productId,
                variation_id: variationId,
                quantity: quantity,
                attributes: attributes
              },
              success: function(response) {
                console.log('AJAX success response:', response);
                // Treat as success if fragments and cart_hash exist
                if (response && response.fragments && response.cart_hash) {
                  $btn.text('Lisätty!').addClass('added');
                  setTimeout(function() {
                    $btn.text('Lisää ostoskoriin').removeClass('added');
                  }, 2000);
                  // Update WooCommerce mini-cart
                  if (typeof $.fn.wc_cart_fragments_refresh === 'function') {
                    $.fn.wc_cart_fragments_refresh();
                  } else {
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
                  }
                } else {
                  alert('Virhe: ' + (response.data && response.data.message ? response.data.message : 'Tuntematon virhe'));
                }
              },
              error: function(xhr, status, error) {
                console.log('AJAX error response:', xhr.responseText, status, error);
                alert('Verkkovirhe. Yritä uudelleen.');
              }
            });
          });
        });
        </script>
      </div>
    </div>

    <div class="product-full-width-sections">
      <?php if ($product->is_type("variable") && !empty($grouped_variations)): ?>
        <div class="inventory-list-container">
          <h3>Saatavilla olevat vaihtoehdot</h3>
          <?php foreach ($grouped_variations as $thickness => $variations): ?>
            <div class="thickness-group">
              <h4 class="thickness-header">Paksuus: <?php echo esc_html((strpos($thickness, "mm") !== false) ? $thickness : (preg_replace("/[^0-9.]/", "", $thickness) ? preg_replace("/[^0-9.]/", "", $thickness) . " mm" : $thickness)); ?></h4>
              <div class="variations-table-container">
                <table class="inventory-table">
                  <thead>
                    <tr>
                      <th class="dimensions-col">Koot</th>
                      <th class="fire-rating-col">Paloluokka</th>
                      <th class="material-col">Pelti</th>
                      <th class="levyhinta-col">Levyhinta</th>
                      <th class="neliohinta-col">Neliöhinta</th>
                      <th class="availability-col">Saatavuus</th>
                      <th class="add-to-cart-col">Tilaus</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($variations as $variation): ?>
                      <?php
                      $stock_status = get_variation_stock_status($variation);
                      $variation_obj = wc_get_product($variation["variation_id"]);
                      $variation_price = $variation_obj ? floatval($variation_obj->get_price()) : 0;
                      $area_m2 = $variation_obj ? floatval($variation_obj->get_meta("_area_m2", true)) : 0;
                      $nelio_price = ($variation_price && $area_m2) ? $variation_price / $area_m2 : 0;
                      ?>
                      <tr class="variation-row" data-variation-id="<?php echo esc_attr($variation["variation_id"]); ?>">
                        <td class="dimensions" data-label="Leveys × Pituus">
                          <?php echo esc_html(format_dimensions($variation["attributes"])); ?>
                        </td>
                        <td class="fire-rating" data-label="Paloluokka">
                          <?php echo esc_html(get_attribute_value($variation["attributes"], "paloluokka") ?: "-"); ?>
                        </td>
                        <td class="material" data-label="Pelti Materiaali">
                          <?php echo esc_html(get_attribute_value($variation["attributes"], "pelti materiaali") ?: "-"); ?>
                        </td>
                        <td class="levyhinta" data-label="Levyhinta">
                            <?php if ($variation_price) echo wc_price($variation_price); else echo "-"; ?>
                        </td>
                        <td class="neliohinta" data-label="Neliöhinta">
                            <?php if ($nelio_price) echo wc_price($nelio_price); else echo "-"; ?>
                        </td>
                        <td class="availability" data-label="Saatavuus">
                          <span class="stock-status <?php echo esc_attr($stock_status); ?>">
                            <?php echo esc_html($stock_status); ?>
                          </span>
                        </td>
                        <td class="add-to-cart" data-label="Tilaus">
                          <div class="quantity-input-wrapper">
              <input type="number" class="quantity-input" value="1" min="1" data-variation-id="<?php echo esc_attr($variation["variation_id"]); ?>" />
              <button class="add-to-cart-button" 
                data-variation-id="<?php echo esc_attr($variation["variation_id"]); ?>"
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                data-attributes='<?php echo json_encode($variation["attributes"]); ?>'
              >Lisää koriin</button>
<script>
jQuery(document).ready(function($) {
  $('.add-to-cart-button').on('click', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var variationId = $btn.data('variation-id');
    var productId = $btn.data('product-id');
    var quantity = $btn.closest('.quantity-input-wrapper').find('.quantity-input').val();
    var attributes = $btn.data('attributes');
    $.ajax({
      url: wc_add_to_cart_params.ajax_url,
      type: 'POST',
      data: {
        action: 'custom_add_to_cart',
        product_id: productId,
        variation_id: variationId,
        quantity: quantity,
        attributes: attributes
      },
      success: function(response) {
        // Treat as success if fragments and cart_hash exist
        if (response && response.fragments && response.cart_hash) {
          $btn.text('Lisätty!').addClass('added');
          setTimeout(function() {
            $btn.text('Lisää koriin').removeClass('added');
          }, 2000);
          // Update WooCommerce mini-cart
          if (typeof $.fn.wc_cart_fragments_refresh === 'function') {
            $.fn.wc_cart_fragments_refresh();
          } else {
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $btn]);
          }
        } else {
          alert('Virhe: ' + (response.data && response.data.message ? response.data.message : 'Tuntematon virhe'));
        }
      },
      error: function(xhr, status, error) {
        console.log('AJAX error response:', xhr.responseText, status, error);
        alert('Verkkovirhe. Yritä uudelleen.');
      }
    });
  });
});
</script>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="professional-specifications">
        <style>
          .spec-columns {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
          }
          .spec-column-1, .spec-column-2 {
            flex: 1;
            min-width: 300px;
          }
        </style>
        <div class="spec-columns">
          <div class="spec-column-1">
            <?php
            // Define meta labels
            $meta_labels = array(
              '_front_name' => 'Nimi',
              '_ncs_color_etu' => 'NCS-väri',
              '_surface_finish' => 'Pintastruktuuri',
              '_product_pattern' => 'Pintakuvio',
              'mallisto' => 'Mallisto',
              '_back_name' => 'Nimi',
              '_ncs_color_taka' => 'NCS-väri',
              '_available_lengths_mm' => 'Saatavilla olevat pituudet',
              '_available_heights_mm' => 'Saatavilla olevat korkeudet',
              '_area_m2' => 'Ala',
              '_available_thicknesses_mm' => 'Saatavilla olevat paksuudet',
              '_fire_rating' => 'Paloluokka',
              '_impact_resistance' => 'Iskunkestävyys',
              '_structural_durability' => 'Rakenteellinen kestävyys',
              '_maintenance_interval' => 'Huoltoväli',
              '_warranty' => 'Takuu',
              'brand' => 'Valmistaja',
              '_color_durability' => 'Värin kestävyys',
            );

            // Group definitions (order matters)
            $group_front = array(
              '_front_name',
              '_ncs_color_etu',
              '_surface_finish',
              '_product_pattern',
              'mallisto',
            );
            $group_back  = array(
              '_back_name',
              '_ncs_color_taka',
              '_surface_finish',
              '_product_pattern',
              'mallisto',
            );
            $group_tech  = array(
              '_available_lengths_mm',
              '_available_heights_mm',
              '_area_m2',
              '_available_thicknesses_mm',
              '_fire_rating',
              '_impact_resistance',
              '_structural_durability',
              '_maintenance_interval',
              '_warranty'
            );
            $group_other = array(
              'brand',
              '_color_durability',
            );

            // Helper to render value with minimal formatting
            function wf_format_meta_value($key, $value)
            {
              if ($value === '' || $value === null) {
                return '';
              }
              $normalized = is_string($value) ? str_replace(',', '.', $value) : $value;

              if ($key === '_area_m2') {
                $num = floatval($normalized);
                return number_format($num, 2, ',', '') . ' m²';
              }

              if ($key === '_width_mm' || $key === '_height_mm' || $key === '_available_lengths_mm' || $key === '_available_heights_mm' || $key === '_available_thicknesses_mm') {
                $num = floatval($normalized);
                return number_format($num, 0, ',', '') . ' mm';
              }

              if (in_array($key, array('_structural_durability', '_warranty', '_maintenance_interval'), true)) {
                $val = trim((string) $value);
                if (!preg_match('/\\bvuotta$/iu', $val)) {
                  $val .= ' vuotta';
                }
                return esc_html($val);
              }
              if ($key === '_delivery_time') {
                $val = trim((string) $value);
                if (!preg_match('/\\bpäivää$/iu', $val)) {
                  $val .= ' päivää';
                }
                return esc_html($val);
              }
              if ($key === 'rgb_color_etu' || $key === 'rgb_color_taka' || $key === '_vkoodi' || $key === '_vkoodi_taka') {
                $color = trim($value);
                $chip  = '<span style="display:inline-block;width:12px;height:12px;border:1px solid #ddd;border-radius:2px;margin-right:6px;vertical-align:middle;background:'
                  . esc_attr($color) . '"></span>';
                return $chip . '<span>' . esc_html($value) . '</span>';
              }
              return esc_html($value);
            }

            // Helper to format attribute values based on their label
            function wf_format_attribute_value($label, $value)
            {
              $val = trim((string) $value);
              if (preg_match('/paksu.*\\(mm\\)/i', (string) $label)) {
                $parts = preg_split('/\\s*,\\s*/', $val);
                foreach ($parts as &$p) {
                  if ($p !== '' && !preg_match('/mm\\b/i', $p)) {
                    $p .= ' mm';
                  }
                }
                $val = implode(', ', $parts);
              }
              return esc_html($val);
            }

            // Utility: check if any meta in a list has a value
            $has_any_meta = function (array $keys) use ($product) {
              foreach ($keys as $k) {
                $val = get_post_meta($product->get_id(), $k, true);
                if (!empty($val) || $val === '0' || $val === 0) {
                  return true;
                }
              }
              return false;
            };

            // Get product attributes and categorize them
            $attributes = $product->get_attributes();
            $attr_groups = array(
              'front' => array(),
              'back'  => array(),
              'tech'  => array(),
              'other' => array(),
            );

            $thickness_attribute = null;
            $area_displayed = false;

            foreach ($attributes as $attribute) {
              if ($attribute->get_variation()) {
                $name  = $attribute->get_name();
                $label = wc_attribute_label($name);
                $terms = wc_get_product_terms($product->get_id(), $name, array('fields' => 'names'));
                $value = implode(', ', $terms);

                $attr_data = array(
                  'name'  => $name,
                  'label' => $label,
                  'value' => $value,
                );

                $lower_label = strtolower($label);
                if (strpos($lower_label, 'paksu') !== false) {
                  // $thickness_attribute = $attr_data;
                } elseif (strpos($lower_label, 'pintastruktuuri') !== false || 
                         strpos($lower_label, 'kiilto') !== false ||
                         strpos($lower_label, 'surface') !== false) {
                  $attr_groups['front'][] = $attr_data;
                } elseif (strpos($lower_label, 'taka') !== false || 
                         strpos($lower_label, 'back') !== false ||
                         strpos($lower_label, 'mallisto') !== false) {
                  $attr_groups['back'][] = $attr_data;
                } elseif (strpos($lower_label, 'koot') !== false || 
                         strpos($lower_label, 'palo') !== false ||
                         strpos($lower_label, 'fire') !== false ||
                         strpos($lower_label, 'size') !== false) {
                  // $attr_groups['tech'][] = $attr_data;
                } else {
                  // $attr_groups['other'][] = $attr_data;
                }
              }
            }

            // Render Front Side Attributes section
            if ($has_any_meta($group_front) || !empty($attr_groups['front'])) {
              echo '<h4>Etupuoli <b>Fundermax Voyager oak</b></h4>';
              echo '<div class="spec-group front-specs">';
              foreach ($group_front as $key) {
                $value = get_post_meta($product->get_id(), $key, true);
                if (!empty($value) || $value === '0' || $value === 0) {
                  $formatted = wf_format_meta_value($key, $value);
                  if ($formatted !== '') {
                    echo '<div class="spec-item">';
                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                    echo '<span class="spec-value">' . $formatted . '</span>';
                    echo '</div>';
                  }
                }
              }
              foreach ($attr_groups['front'] as $attr) {
                $formatted = wf_format_attribute__value($attr['label'], $attr['value']);
                echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                echo '<span class="spec-value">' . $formatted . '</span>';
                echo '</div>';
              }
              echo '</div>';
            }

            // Render Back Side Attributes section
            if ($has_any_meta($group_back) || !empty($attr_groups['back'])) {
              echo '<h4>Takapuoli <b>Fundermax Voyager oak</b></h4>';
              echo '<div class="spec-group back-specs">';
              foreach ($group_back as $key) {
                $value = get_post_meta($product->get_id(), $key, true);
                if (!empty($value) || $value === '0' || $value === 0) {
                  $formatted = wf_format_meta_value($key, $value);
                  if ($formatted !== '') {
                    echo '<div class="spec-item">';
                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                    echo '<span class="spec-value">' . $formatted . '</span>';
                    echo '</div>';
                  }
                }
              }
              foreach ($attr_groups['back'] as $attr) {
                $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                echo '<span class="spec-value">' . $formatted . '</span>';
                echo '</div>';
              }
              echo '</div>';
            }
            ?>
          </div>
          <div class="spec-column-2">
            <?php
            // Render Technical Specifications section
            if ($has_any_meta($group_tech) || !empty($attr_groups['tech']) || $thickness_attribute) {
              echo '<h4>Tekniset tiedot</h4>';
              echo '<div class="spec-group tech-specs">';
              if (!$area_displayed && $thickness_attribute) {
                $formatted_thickness = wf_format_attribute_value($thickness_attribute['label'], $thickness_attribute['value']);
                echo '<div class="spec-item" data-attribute="paksuus">';
                echo '<span class="spec-label">' . esc_html($thickness_attribute['label']) . ':</span>';
                echo '<span class="spec-value">' . $formatted_thickness . '</span>';
                echo '</div>';
              }
              $area_displayed = false;
              foreach ($group_tech as $key) {
                if ($key === '_m2_price') {
                  continue;
                }

                $value = get_post_meta($product->get_id(), $key, true);
                if (!empty($value) || $value === '0' || $value === 0) {
                  $formatted = wf_format_meta_value($key, $value);
                  if ($formatted !== '') {
                    echo '<div class="spec-item">';
                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                    echo '<span class="spec-value">' . $formatted . '</span>';
                    echo '</div>';
                  }
                }
              }

              foreach ($attr_groups['tech'] as $attr) {
                $formatted = wf_format_attribute_value($attr['label'], $attr['value']);
                echo '<div class="spec-item" data-attribute="' . esc_attr($attr['name']) . '">';
                echo '<span class="spec-label">' . esc_html($attr['label']) . ':</span>';
                echo '<span class="spec-value">' . $formatted . '</span>';
                echo '</div>';
              }
              echo '</div>';
            }

            // Render Other Information section
            if ($has_any_meta($group_other) || !empty($attr_groups['other'])) {
              echo '<h4>Muut tiedot</h4>';
              echo '<div class="spec-group other-specs">';
              foreach ($group_other as $key) {
                $value = get_post_meta($product->get_id(), $key, true);
                if (!empty($value) || $value === '0' || $value === 0) {
                  $formatted = wf_format_meta_value($key, $value);
                  if ($formatted !== '') {
                    echo '<div class="spec-item">';
                    echo '<span class="spec-label">' . esc_html($meta_labels[$key]) . ':</span>';
                    echo '<span class="spec-value">' . $formatted . '</span>';
                    echo '</div>';
                  }
                }
              }
              echo '</div>';
            }
            ?>
          </div>
        </div>
      </div>

      <?php do_action("woocommerce_after_single_product_summary"); ?>

      <div class="producer-details-section">
        <?php /* TODO: Replace static text with dynamic producer information (e.g., from a custom taxonomy or brand plugin). */ ?>
        <h2>Valmistaja: Fundermax</h2>
        <p>Tähän tulee Fundermaxin kuvaus. Fundermax on tunnettu korkealaatuisista ja kestävistä julkisivulevyistään ja sisustusmateriaaleistaan. Yritys on sitoutunut innovaatioon ja kestävään kehitykseen tuotannossaan.</p>
      </div>

      <div class="collection-details-section">
        <?php /* TODO: Replace static text with dynamic collection information. */ ?>
        <h2>Mallisto: NT Color</h2>
        <p>Tähän tulee NT Color -malliston lyhyt kuvaus. Mallisto tarjoaa laajan valikoiman eloisia ja kestäviä värejä moderniin arkkitehtuuriin ja sisustukseen.</p>
      </div>

    </div>
  </div>
</div>

<?php do_action("woocommerce_after_single_product"); ?>

