<?php
/**
 * Database class for table management
 *
 * @package DSF
 */

namespace DSF;

class Database {
    /**
     * Database instance
     *
     * @var self
     */
    private static $instance = null;
    
    /**
     * Get database instance
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
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Services table
        $services_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_services (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(100) NOT NULL,
            category VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            fixed_price DECIMAL(10,2) DEFAULT NULL,
            pricing_model VARCHAR(50) NOT NULL DEFAULT 'state_based',
            has_packages TINYINT(1) NOT NULL DEFAULT 0,
            description LONGTEXT,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_service (type, category, name),
            KEY type_category (type, category),
            KEY pricing_model (pricing_model),
            KEY enabled (enabled)
        ) $charset_collate;";
        
        // PackageTypes table (normalized - stores package types once)
        $package_types_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_package_types (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            package_type_name VARCHAR(100) NOT NULL UNIQUE,
            description LONGTEXT,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY enabled (enabled)
        ) $charset_collate;";
        
        // Service-PackageType Pricing table (junction/pivot table)
        $service_package_pricing_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_service_package_pricing (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            package_type_id BIGINT UNSIGNED NOT NULL,
            price DECIMAL(10, 2),
            description LONGTEXT,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_service_package (service_id, package_type_id),
            KEY service_id (service_id),
            KEY package_type_id (package_type_id),
            KEY enabled (enabled),
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}dsf_services(id) ON DELETE CASCADE,
            FOREIGN KEY (package_type_id) REFERENCES {$wpdb->prefix}dsf_package_types(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Locations table (normalized - stores states/regions once)
        $locations_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_locations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            location_name VARCHAR(100) NOT NULL,
            location_code VARCHAR(10),
            location_type VARCHAR(50) NOT NULL DEFAULT 'state',
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_location (location_name, location_type),
            KEY location_type (location_type),
            KEY enabled (enabled)
        ) $charset_collate;";
        
        // Service-Location Pricing table (junction/pivot table)
        $service_location_pricing_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_service_location_pricing (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            location_id BIGINT UNSIGNED NOT NULL,
            standard_price DECIMAL(10, 2),
            premium_price DECIMAL(10, 2),
            is_universal TINYINT(1) NOT NULL DEFAULT 0,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_service_location (service_id, location_id),
            KEY service_id (service_id),
            KEY location_id (location_id),
            KEY is_universal (is_universal),
            KEY enabled (enabled),
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}dsf_services(id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES {$wpdb->prefix}dsf_locations(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Portals table
        $portals_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_portals (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            portal_name VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2),
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY service_id (service_id),
            KEY portal_name (portal_name),
            KEY enabled (enabled),
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}dsf_services(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Submissions table
        $submissions_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_submissions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            form_data LONGTEXT NOT NULL,
            total_price DECIMAL(10, 2),
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            business_name VARCHAR(255),
            business_address VARCHAR(255),
            phone VARCHAR(20),
            email VARCHAR(255),
            city VARCHAR(100),
            state VARCHAR(50),
            zipcode VARCHAR(10),
            entity_type VARCHAR(100),
            notes LONGTEXT,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY service_id (service_id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Email Templates table
        $email_templates_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_email_templates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            template_name VARCHAR(255) NOT NULL UNIQUE,
            template_slug VARCHAR(255) NOT NULL UNIQUE,
            template_subject VARCHAR(255) NOT NULL,
            template_html LONGTEXT NOT NULL,
            template_css LONGTEXT,
            description LONGTEXT,
            is_default TINYINT(1) NOT NULL DEFAULT 0,
            enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY is_default (is_default),
            KEY enabled (enabled)
        ) $charset_collate;";

        // WP Forms Submissions table (separate from regular submissions)
        $wpforms_submissions_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dsf_wpforms_submissions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            form_id BIGINT UNSIGNED NOT NULL,
            entry_id BIGINT UNSIGNED NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            business_name VARCHAR(255),
            business_address VARCHAR(255),
            phone VARCHAR(20),
            email VARCHAR(255),
            city VARCHAR(100),
            state VARCHAR(50),
            zipcode VARCHAR(10),
            entity_type VARCHAR(100),
            total_price DECIMAL(10, 2),
            notes LONGTEXT,
            form_data LONGTEXT,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY form_id (form_id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Execute table creation
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($services_table);
        dbDelta($package_types_table);
        dbDelta($service_package_pricing_table);
        dbDelta($locations_table);
        dbDelta($service_location_pricing_table);
        dbDelta($portals_table);
        dbDelta($submissions_table);
        dbDelta($email_templates_table);
        dbDelta($wpforms_submissions_table);
    }

    /**
     * Drop all plugin tables on uninstall
     */
    public static function drop_tables() {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_wpforms_submissions");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_email_templates");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_submissions");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_portals");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_service_package_pricing");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_package_types");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_service_location_pricing");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_locations");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dsf_services");
    }

    /**
     * Get table name
     *
     * @param string $table Table name
     * @return string Full table name with prefix
     */
    public static function get_table($table) {
        global $wpdb;
        return $wpdb->prefix . 'dsf_' . $table;
    }
}
