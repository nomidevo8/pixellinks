<?php
/**
 * Plugin Uninstall Handler
 *
 * Fired when the plugin is uninstalled.
 * This file is required to be named uninstall.php per WordPress conventions.
 *
 * @package DSF
 */

// Exit if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load the autoloader
require_once dirname( __FILE__ ) . '/dynamic-services-form.php';

// Drop all plugin tables
\DSF\Database::drop_tables();

// Delete plugin options
delete_option( 'dsf_plugin_version' );
delete_option( 'dsf_db_version' );
