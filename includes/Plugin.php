<?php
/**
 * Main Plugin Class
 *
 * @package DSF
 */

namespace DSF;
// use DSF\WP_Forms\WpForms;

class Plugin {
    /**
     * Instance of the plugin
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return self
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Create database tables
        Database::create_tables();
        
        // Create default email templates
        EmailTemplate::create_default_templates();
        
        // Set plugin version
        update_option('dsf_plugin_version', DSF_PLUGIN_VERSION);
        update_option('dsf_db_version', DSF_DB_VERSION);
        
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Initialize the plugin
     */
    public static function init() {
        self::instance()->setup();
                // Create database tables
        // Database::create_tables();
        // Database::ensure_payment_columns();
                // Create default email templates
        // EmailTemplate::create_default_templates();
    }

    /**
     * Setup the plugin
     */
    private function setup() {
        // Load plugin text domain
        load_plugin_textdomain('dynamic-services-form', false, dirname(plugin_basename(DSF_PLUGIN_FILE)) . '/languages');
        
        // Initialize database
        Database::instance();
        
        // Initialize admin if in admin area
        if (is_admin()) {
            Admin::instance();
        }
        
        // Initialize frontend form handler
        Form::instance();
        
        // Initialize AJAX handlers
        Ajax::instance();

        // Initialize WP Forms integration
        \DSF\WP_Forms\WpForms::instance();
        
        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Register shortcode
        add_shortcode('dynamic_service_form', [$this, 'render_form_shortcode']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Enqueue SweetAlert2 library
        wp_enqueue_style(
            'sweetalert2-style',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
            [],
            '11'
        );
        
        wp_enqueue_script(
            'sweetalert2-script',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [],
            '11',
            true
        );
        
        wp_enqueue_style(
            'dsf-frontend-style',
            DSF_PLUGIN_URL . 'assets/css/form.css',
            [],
            DSF_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'dsf-frontend-script',
            DSF_PLUGIN_URL . 'assets/js/form.min.js',
            ['jquery', 'sweetalert2-script'],
            DSF_PLUGIN_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('dsf-frontend-script', 'dsfFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dsf_form_nonce'),
        ]);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        // Only enqueue on DSF admin pages
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Load only on Dynamic Services pages (main + all submenus)
        if (strpos($screen->id, 'dsf') === false) {
            return;
        }

        wp_enqueue_style(
            'dsf-admin-style',
            DSF_PLUGIN_URL . 'assets/css/admin.css',
            [],
            DSF_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'dsf-admin-script',
            DSF_PLUGIN_URL . 'assets/js/admin.min.js',
            ['jquery'],
            DSF_PLUGIN_VERSION,
            true
        );
    }

    /**
     * Render form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string Form HTML
     */
    public function render_form_shortcode($atts) {
        return Form::instance()->render($atts);
    }
}
