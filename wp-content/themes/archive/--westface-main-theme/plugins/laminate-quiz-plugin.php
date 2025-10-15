<?php
/**
 * Plugin Name: Westface Laminate Quiz
 * Description: Interactive quiz to help visitors choose laminate flooring with sample request functionality
 * Version: 1.0.0
 * Author: Westface Professional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WestfaceLaminateQuiz {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_sample_request', array($this, 'handle_sample_submission'));
        add_action('wp_ajax_nopriv_submit_sample_request', array($this, 'handle_sample_submission'));
        register_activation_hook(__FILE__, array($this, 'create_database_table'));
    }
    
    public function init() {
        add_shortcode('laminate_quiz', array($this, 'display_quiz'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('laminate-quiz-js', plugin_dir_url(__FILE__) . 'laminate-quiz.js', array('jquery'), '1.0.0', true);
        wp_localize_script('laminate-quiz-js', 'laminate_quiz_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('laminate_quiz_nonce')
        ));
    }
    
    public function create_database_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'sample_requests';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            address text,
            quiz_results text,
            recommended_products text,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function display_quiz($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Laminaattiopas - Löydä täydellinen lattia'
        ), $atts);
        
        ob_start();
        ?>
        <div id="laminate-quiz-container" class="quiz-container">
            <div class="quiz-header">
                <h2><i class="fas fa-puzzle-piece"></i> <?php echo esc_html($atts['title']); ?></h2>
                <p>Vastaa muutamaan kysymykseen, niin autamme sinua löytämään sopivan laminaatin.</p>
                <div class="quiz-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="progress-text">Kysymys <span class="current-question">1</span> / <span class="total-questions">5</span></span>
                </div>
            </div>
            
            <div class="quiz-content">
                <!-- Question 1: Room Type -->
                <div class="quiz-question active" data-question="1">
                    <h3>Mihin tilaan laminaatti asennetaan?</h3>
                    <div class="quiz-options">
                        <label class="quiz-option">
                            <input type="radio" name="room_type" value="olohuone">
                            <div class="option-content">
                                <i class="fas fa-couch"></i>
                                <span>Olohuone</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="room_type" value="keittio">
                            <div class="option-content">
                                <i class="fas fa-utensils"></i>
                                <span>Keittiö</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="room_type" value="makuuhuone">
                            <div class="option-content">
                                <i class="fas fa-bed"></i>
                                <span>Makuuhuone</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="room_type" value="kylpyhuone">
                            <div class="option-content">
                                <i class="fas fa-bath"></i>
                                <span>Kylpyhuone</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Question 2: Usage Level -->
                <div class="quiz-question" data-question="2">
                    <h3>Kuinka paljon tilaa käytetään?</h3>
                    <div class="quiz-options">
                        <label class="quiz-option">
                            <input type="radio" name="usage_level" value="vahva">
                            <div class="option-content">
                                <i class="fas fa-running"></i>
                                <span>Vahva käyttö (lapset, lemmikit)</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="usage_level" value="normaali">
                            <div class="option-content">
                                <i class="fas fa-walking"></i>
                                <span>Normaali käyttö</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="usage_level" value="kevyt">
                            <div class="option-content">
                                <i class="fas fa-feather"></i>
                                <span>Kevyt käyttö</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Question 3: Style Preference -->
                <div class="quiz-question" data-question="3">
                    <h3>Millainen tyyli miellyttää?</h3>
                    <div class="quiz-options">
                        <label class="quiz-option">
                            <input type="radio" name="style_preference" value="moderni">
                            <div class="option-content">
                                <i class="fas fa-cube"></i>
                                <span>Moderni ja minimalistinen</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="style_preference" value="klassinen">
                            <div class="option-content">
                                <i class="fas fa-home"></i>
                                <span>Klassinen ja ajaton</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="style_preference" value="rustiikki">
                            <div class="option-content">
                                <i class="fas fa-tree"></i>
                                <span>Rustiikki ja luonnonläheinen</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="style_preference" value="teollinen">
                            <div class="option-content">
                                <i class="fas fa-industry"></i>
                                <span>Teollinen ja urbaani</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Question 4: Color Preference -->
                <div class="quiz-question" data-question="4">
                    <h3>Mikä värimaailma sopii parhaiten?</h3>
                    <div class="quiz-options">
                        <label class="quiz-option">
                            <input type="radio" name="color_preference" value="vaalea">
                            <div class="option-content">
                                <i class="fas fa-sun"></i>
                                <span>Vaalea (valkoinen, beige)</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="color_preference" value="keskitumma">
                            <div class="option-content">
                                <i class="fas fa-adjust"></i>
                                <span>Keskitumma (harmaa, ruskea)</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="color_preference" value="tumma">
                            <div class="option-content">
                                <i class="fas fa-moon"></i>
                                <span>Tumma (musta, tummanruskea)</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Question 5: Budget -->
                <div class="quiz-question" data-question="5">
                    <h3>Mikä on budjettisi neliömetrille?</h3>
                    <div class="quiz-options">
                        <label class="quiz-option">
                            <input type="radio" name="budget" value="edullinen">
                            <div class="option-content">
                                <i class="fas fa-euro-sign"></i>
                                <span>Edullinen (alle 30€/m²)</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="budget" value="keskihinta">
                            <div class="option-content">
                                <i class="fas fa-coins"></i>
                                <span>Keskihinta (30-50€/m²)</span>
                            </div>
                        </label>
                        <label class="quiz-option">
                            <input type="radio" name="budget" value="premium">
                            <div class="option-content">
                                <i class="fas fa-gem"></i>
                                <span>Premium (yli 50€/m²)</span>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Results -->
                <div class="quiz-results" style="display: none;">
                    <div class="results-content">
                        <h3><i class="fas fa-check-circle"></i> Suosituksemme sinulle</h3>
                        <div class="recommended-products"></div>
                        <p>Haluatko tilata mallipalat näistä tuotteista?</p>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="quiz-contact-form" style="display: none;">
                    <h3><i class="fas fa-envelope"></i> Yhteystiedot mallipalojen tilaamiseen</h3>
                    <form id="sample-request-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sample_name">Nimi *</label>
                                <input type="text" id="sample_name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="sample_email">Sähköposti *</label>
                                <input type="email" id="sample_email" name="email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sample_phone">Puhelinnumero</label>
                                <input type="tel" id="sample_phone" name="phone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sample_address">Toimitusosoite *</label>
                            <textarea id="sample_address" name="address" rows="3" required placeholder="Katu, postinumero, kaupunki"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary sample-request-btn">
                            <i class="fas fa-cube"></i> Tilaa mallipalat
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="quiz-navigation">
                <button class="btn btn-secondary quiz-prev" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Edellinen
                </button>
                <button class="btn btn-primary quiz-next">
                    Seuraava <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var currentQuestion = 1;
            var totalQuestions = 5;
            var answers = {};
            
            // Update progress
            function updateProgress() {
                var progress = (currentQuestion / totalQuestions) * 100;
                $('.progress-fill').css('width', progress + '%');
                $('.current-question').text(currentQuestion);
            }
            
            // Show question
            function showQuestion(questionNum) {
                $('.quiz-question').removeClass('active');
                $('.quiz-question[data-question="' + questionNum + '"]').addClass('active');
                
                // Update navigation
                if (questionNum === 1) {
                    $('.quiz-prev').hide();
                } else {
                    $('.quiz-prev').show();
                }
                
                if (questionNum === totalQuestions) {
                    $('.quiz-next').text('Näytä tulokset').find('i').removeClass('fa-arrow-right').addClass('fa-check');
                } else {
                    $('.quiz-next').html('Seuraava <i class="fas fa-arrow-right"></i>');
                }
                
                updateProgress();
            }
            
            // Next question
            $('.quiz-next').on('click', function() {
                var currentQuestionEl = $('.quiz-question[data-question="' + currentQuestion + '"]');
                var selectedOption = currentQuestionEl.find('input[type="radio"]:checked');
                
                if (selectedOption.length === 0) {
                    alert('Valitse vaihtoehto jatkaaksesi.');
                    return;
                }
                
                // Save answer
                answers[selectedOption.attr('name')] = selectedOption.val();
                
                if (currentQuestion < totalQuestions) {
                    currentQuestion++;
                    showQuestion(currentQuestion);
                } else {
                    showResults();
                }
            });
            
            // Previous question
            $('.quiz-prev').on('click', function() {
                if (currentQuestion > 1) {
                    currentQuestion--;
                    showQuestion(currentQuestion);
                }
            });
            
            // Show results
            function showResults() {
                $('.quiz-question').hide();
                $('.quiz-navigation').hide();
                
                // Generate recommendations based on answers
                var recommendations = generateRecommendations(answers);
                $('.recommended-products').html(recommendations);
                $('.quiz-results').show();
                
                // Show contact form after a delay
                setTimeout(function() {
                    $('.quiz-contact-form').slideDown();
                }, 1000);
            }
            
            // Generate recommendations
            function generateRecommendations(answers) {
                var html = '<h4>Suositellut tuotteet sinulle:</h4>';
                
                // Simple recommendation logic
                if (answers.budget === 'premium' && answers.style_preference === 'moderni') {
                    html += '<div class="product-recommendation">';
                    html += '<h5>Premium Moderni Laminaatti</h5>';
                    html += '<p>Korkealaatuinen, minimalistinen design täydelliseen kotiin.</p>';
                    html += '</div>';
                } else if (answers.usage_level === 'vahva') {
                    html += '<div class="product-recommendation">';
                    html += '<h5>Kestävä Perhelaminaatti</h5>';
                    html += '<p>Erittäin kestävä vaihtoehto vilkkaaseen kotiin.</p>';
                    html += '</div>';
                } else {
                    html += '<div class="product-recommendation">';
                    html += '<h5>Klassinen Laminaatti</h5>';
                    html += '<p>Ajaton valinta, joka sopii kaikkiin koteihin.</p>';
                    html += '</div>';
                }
                
                return html;
            }
            
            // Handle sample request form
            $('#sample-request-form').on('submit', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $button = $form.find('.sample-request-btn');
                
                // Show loading
                $button.addClass('loading').prop('disabled', true);
                
                var formData = {
                    action: 'submit_sample_request',
                    nonce: laminate_quiz_ajax.nonce,
                    name: $('#sample_name').val(),
                    email: $('#sample_email').val(),
                    phone: $('#sample_phone').val(),
                    address: $('#sample_address').val(),
                    quiz_results: JSON.stringify(answers),
                    recommended_products: $('.recommended-products').html()
                };
                
                $.ajax({
                    url: laminate_quiz_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('Kiitos! Mallipalapyyntösi on lähetetty. Lähetämme mallipalat pian.');
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
            
            // Initialize
            showQuestion(1);
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function handle_sample_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'laminate_quiz_nonce')) {
            wp_die('Security check failed');
        }
        
        // Sanitize input data
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_textarea_field($_POST['address']);
        $quiz_results = sanitize_text_field($_POST['quiz_results']);
        $recommended_products = wp_kses_post($_POST['recommended_products']);
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($address)) {
            wp_send_json_error(array('message' => 'Nimi, sähköposti ja osoite ovat pakollisia.'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Virheellinen sähköpostiosoite.'));
        }
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'sample_requests';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'quiz_results' => $quiz_results,
                'recommended_products' => $recommended_products,
                'submission_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Tietojen tallennus epäonnistui.'));
        }
        
        // Send email notification to admin
        $admin_email = get_option('admin_email');
        $subject = 'Mallipalapyyntö';
        
        $email_message = "Uusi mallipalapyyntö on saapunut:\n\n";
        $email_message .= "Nimi: {$name}\n";
        $email_message .= "Sähköposti: {$email}\n";
        $email_message .= "Puhelinnumero: {$phone}\n";
        $email_message .= "Toimitusosoite: {$address}\n\n";
        $email_message .= "Suositellut tuotteet:\n" . strip_tags($recommended_products) . "\n\n";
        $email_message .= "Lähetetty: " . current_time('d.m.Y H:i');
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        );
        
        wp_mail($admin_email, $subject, $email_message, $headers);
        
        wp_send_json_success(array('message' => 'Mallipalapyyntö lähetetty onnistuneesti.'));
    }
}

// Initialize the plugin
new WestfaceLaminateQuiz();
?>

