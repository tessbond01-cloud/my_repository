<?php
/*
Template Name: Kassa
*/
get_header();
?>
<style>
.kassa-container {
  max-width: 700px;
  margin: 40px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  padding: 32px 24px;
  font-family: 'Inter', Arial, sans-serif;
}
.kassa-title {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 24px;
  color: #222;
}
.kassa-section {
  margin-bottom: 32px;
}
.kassa-label {
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
}
.kassa-input, .kassa-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 1rem;
}
.kassa-cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 24px;
}
.kassa-cart-table th, .kassa-cart-table td {
  padding: 10px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.kassa-cart-table th {
  background: #f7f7f7;
  font-weight: 600;
}
.kassa-total {
  font-size: 1.2rem;
  font-weight: 700;
  text-align: right;
  margin-bottom: 24px;
}
.kassa-btn {
  background: #222;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 14px 32px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.kassa-btn:hover {
  background: #444;
}
.kassa-privacy {
  font-size: 0.95rem;
  color: #444;
  margin-bottom: 18px;
}
.kassa-terms {
  margin-bottom: 18px;
}
.kassa-terms label {
  font-size: 0.98rem;
  color: #222;
}
</style>
<div class="kassa-container">
  <div class="kassa-title">Kassa</div>
  <form method="post" action="">
    <div class="kassa-section">
      <label class="kassa-label">Etunimi *</label>
      <input type="text" class="kassa-input" name="billing_first_name" required>
      <label class="kassa-label">Sukunimi</label>
      <input type="text" class="kassa-input" name="billing_last_name" required>
      <label class="kassa-label">Maa / Alue</label>
      <select class="kassa-select" name="billing_country" required>
        <option value="FI">Suomi</option>
        <option value="SE">Ruotsi</option>
        <option value="EE">Viro</option>
        <option value="">Muu</option>
      </select>
      <label class="kassa-label">Katuosoite</label>
      <input type="text" class="kassa-input" name="billing_address_1" required>
      <label class="kassa-label">Postinumero</label>
      <input type="text" class="kassa-input" name="billing_postcode" required>
      <label class="kassa-label">Postitoimipaikka</label>
      <input type="text" class="kassa-input" name="billing_city" required>
      <label class="kassa-label">Puhelin</label>
      <input type="tel" class="kassa-input" name="billing_phone" required>
      <label class="kassa-label">Sähköpostiosoite</label>
      <input type="email" class="kassa-input" name="billing_email" required>
    </div>
    <div class="kassa-section">
      <table class="kassa-cart-table">
        <thead>
          <tr>
            <th>Tuote</th>
            <th>Määrä</th>
            <th>Yhteensä</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            echo '<tr>';
            echo '<td>' . esc_html($product->get_name()) . '</td>';
            echo '<td>' . intval($cart_item['quantity']) . '</td>';
            echo '<td>' . wc_price($product->get_price() * $cart_item['quantity']) . '</td>';
            echo '</tr>';
          }
          ?>
        </tbody>
      </table>
      <div class="kassa-total">
        <?php echo 'Yhteensä: ' . WC()->cart->get_total(); ?>
      </div>
    </div>
    <div class="kassa-privacy">
      Camu Oy (Rekisterinpitäjä) sitoutuu suojelemaan henkilötietojasi ja oikeuttasi päättää niiden käytöstä. Voimassa olevat lait ja asetukset määrittävät tietosuojakäytänteemme. Lue lisää tietosuojaselosteestamme
    </div>
    <div class="kassa-terms">
      <label>
        <input type="checkbox" name="accept_terms" required>
        Olen lukenut verkkosivuston tilaus- ja sopimusehdot ja hyväksyn ne. *
      </label>
    </div>
    <button type="submit" class="kassa-btn">Lähetä tilaus</button>
  </form>
</div>
<?php get_footer(); ?>
