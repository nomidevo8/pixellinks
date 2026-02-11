<?php
/**
 * Plugin Name: Dynamic Services Form
 * Plugin URI: https://naumansajjad.infy.uk/
 * Description: Scalable, fully dynamic multi-step form plugin for service pricing and quotes with OOP PHP and custom database tables
 * Version: 1.0.0
 * Author: NomiDev
 * Author URI: https://naumansajjad.infy.uk/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dynamic-services-form
 * Domain Path: /languages
 * Requires Plugins: 
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package DynamicServicesForm
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('DSF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DSF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DSF_PLUGIN_FILE', __FILE__);
define('DSF_PLUGIN_VERSION', '1.0.0.0.1');
// define('DSF_PLUGIN_VERSION', time());
define('DSF_DB_VERSION', '1.0.0');

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    // Only handle our plugin classes
    if (strpos($class, 'DSF\\') !== 0) {
        return;
    }
    
    // Remove the namespace prefix
    $class = str_replace('DSF\\', '', $class);
    
    // Convert namespace to file path
    $file = DSF_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize the plugin
require_once DSF_PLUGIN_DIR . 'includes/Plugin.php';

// Plugin activation/deactivation hooks
register_activation_hook(__FILE__, function () {
    \DSF\Plugin::activate();
});

register_deactivation_hook(__FILE__, function () {
    \DSF\Plugin::deactivate();
});

// Initialize plugin on WordPress init
add_action('plugins_loaded', function () {
    \DSF\Plugin::init();
});
