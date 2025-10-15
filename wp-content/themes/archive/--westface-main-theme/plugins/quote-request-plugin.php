<?php
/**
 * Plugin Name: Westface Quote Request
 * Description: Custom form for quote requests with database storage and email notifications
 * Version: 1.0.0
 * Author: Westface Professional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WestfaceQuoteRequest {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_quote_request', array($this, 'handle_quote_submission'));
        add_action('wp_ajax_nopriv_submit_quote_request', array($this, 'handle_quote_submission'));
        register_activation_hook(__FILE__, array($this, 'create_database_table'));
    }
    
    public function init() {
        add_shortcode('quote_request_form', array($this, 'display_quote_form'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('quote-request-js', plugin_dir_url(__FILE__) . 'quote-request.js', array('jquery'), '1.0.0', true);
        wp_localize_script('quote-request-js', 'quote_request_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('quote_request_nonce')
        ));
    }
    
    public function create_database_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'quote_requests';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            product_category varchar(100),
            message text,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function display_quote_form($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Jätä tarjouspyyntö',
            'button_text' => 'Lähetä tarjouspyyntö'
        ), $atts);
        
        ob_start();
        ?>
        <div id="quote-request-modal" class="quote-modal-overlay" style="display: none;">
            <div class="quote-modal-content">
                <div class="quote-modal-header">
                    <h2><i class="fas fa-file-invoice"></i> <?php echo esc_html($atts['title']); ?></h2>
                    <button class="quote-modal-close">&times;</button>
                </div>
                
                <div class="quote-modal-body">
                    <p>Täytä alla olevat tiedot, niin otamme yhteyttä tarjouksen kanssa.</p>
                    
                    <form id="quote-request-form" class="quote-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="quote_name">Nimi *</label>
                                <input type="text" id="quote_name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="quote_email">Sähköposti *</label>
                                <input type="email" id="quote_email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="quote_phone">Puhelinnumero</label>
                                <input type="tel" id="quote_phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="quote_category">Tuotekategoria</label>
                                <select id="quote_category" name="product_category">
                                    <option value="">Valitse kategoria</option>
                                    <option value="Järjestelmät">Järjestelmät</option>
                                    <option value="Pintamateriaalit">Pintamateriaalit</option>
                                    <option value="RANGAT">RANGAT</option>
                                    <option value="Laminaatti">Laminaatti</option>
                                    <option value="Muu">Muu</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="quote_message">Lisätiedot</label>
                            <textarea id="quote_message" name="message" rows="4" placeholder="Kerro lisää tarpeistasi..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> <?php echo esc_html($atts['button_text']); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>        
        <script>
        jQuery(document).ready(function($) {
            // Open modal when quote request buttons are clicked
            $(document).on('click', '.quote-request-btn, a[href="#contact"]', function(e) {
                e.preventDefault();
                $('#quote-request-modal').fadeIn(300);
            });
            
            // Close modal
            $(document).on('click', '.quote-modal-close, .quote-modal-overlay', function(e) {
                if (e.target === this) {
                    $('#quote-request-modal').fadeOut(300);
                }
            });
            
            // Handle form submission
            $('#quote-request-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $form.find('button[type="submit"]');
                
                // Show loading state
                $button.addClass('loading').prop('disabled', true);
                
                // Collect form data
                var formData = {
                    action: 'submit_quote_request',
                    nonce: quote_request_ajax.nonce,
                    name: $('#quote_name').val(),
                    email: $('#quote_email').val(),
                    phone: $('#quote_phone').val(),
                    product_category: $('#quote_category').val(),
                    message: $('#quote_message').val()
                };
                
                // Submit via AJAX
                $.ajax({
                    url: quote_request_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('Kiitos! Tarjouspyyntösi on lähetetty. Otamme yhteyttä pian.');
                            $('#quote-request-modal').fadeOut(300);
                            $form[0].reset();
                        } else {
                            alert('Virhe: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Virhe lähetyksessä. Yritä uudelleen.');
                    },
                    complete: function() {
                        $button.removeClass('loading').prop('disabled', false);
                    }
                });
            });
            
            // ESC key to close modal
            $(document).on('keyup', function(e) {
                if (e.keyCode === 27) {
                    $('#quote-request-modal').fadeOut(300);
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function handle_quote_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'quote_request_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sanitize input data
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $product_category = sanitize_text_field($_POST['product_category']);
        $message = sanitize_textarea_field($_POST['message']);
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            wp_send_json_error(array('message' => 'Nimi ja sähköposti ovat pakollisia.'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Virheellinen sähköpostiosoite.'));
        }
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'quote_requests';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'product_category' => $product_category,
                'message' => $message,
                'submission_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Tietojen tallennus epäonnistui.'));
        }
        
        // Send email notification to admin
        $admin_email = get_option('admin_email');
        $subject = 'Uusi tarjouspyyntö';
        
        $email_message = "Uusi tarjouspyyntö on saapunut:\n\n";
        $email_message .= "Nimi: {$name}\n";
        $email_message .= "Sähköposti: {$email}\n";
        $email_message .= "Puhelinnumero: {$phone}\n";
        $email_message .= "Tuotekategoria: {$product_category}\n";
        $email_message .= "Viesti: {$message}\n";
        $email_message .= "\nLähetetty: " . current_time('d.m.Y H:i');
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        );
        
        wp_mail($admin_email, $subject, $email_message, $headers);
        
        wp_send_json_success(array('message' => 'Tarjouspyyntö lähetetty onnistuneesti.'));
    }
}

// Initialize the plugin
new WestfaceQuoteRequest();

// Add the shortcode to footer automatically
add_action('wp_footer', function() {
    echo do_shortcode('[quote_request_form]');
});
?>

