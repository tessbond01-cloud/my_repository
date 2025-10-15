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
        wp_enqueue_script('laminate-quiz-js', get_template_directory_uri() . '/plugins/laminate-quiz.js', array('jquery'), '1.0.0', true);
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
                <h2 style="color:#000;">Tilauskysely tähän!</h2>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
new WestfaceLaminateQuiz();
?>

