<?php
/**
 * Service class for managing services
 *
 * @package DSF
 */

namespace DSF;

class Service {
    /**
     * Service ID
     *
     * @var int
     */
    private $id;
    
    /**
     * Service data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Service ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load service from database
     *
     * @param int $id Service ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('services');
        $service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$service) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $service;
        
        return true;
    }

    /**
     * Get service by type, category, and name
     *
     * @param string $type Service type
     * @param string $category Service category
     * @param string $name Service name
     * @return Service|null
     */
    public static function get_by_identifier($type, $category, $name) {
        global $wpdb;
        
        $table = Database::get_table('services');
        $service = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE type = %s AND category = %s AND name = %s AND enabled = 1",
                $type,
                $category,
                $name
            ),
            ARRAY_A
        );
        
        if (!$service) {
            return null;
        }
        
        $instance = new self();
        $instance->id = $service['id'];
        $instance->data = $service;
        
        return $instance;
    }

    /**
     * Get all services
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        
        $table = Database::get_table('services');
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY type, category, name", ARRAY_A);
    }

    /**
     * Save service
     *
     * @param array $args Service data
     * @return int|bool Service ID or false
     */
    public static function save($args) {
        global $wpdb;
        
        $defaults = [
            'type' => '',
            'category' => '',
            'name' => '',
            'pricing_model' => 'state_based',
            'has_packages' => 0,
            'description' => '',
            'enabled' => 1,
        ];
        
        $data = wp_parse_args($args, $defaults);
        
        $table = Database::get_table('services');
        
        if (!empty($data['id'])) {
            // Update existing
            $id = $data['id'];
            unset($data['id']);
            
            $wpdb->update(
                $table,
                $data,
                ['id' => $id],
                ['%s', '%s', '%s', '%s', '%d', '%s', '%d'],
                ['%d']
            );
            
            return $id;
        } else {
            // Insert new
            $wpdb->insert(
                $table,
                $data,
                ['%s', '%s', '%s', '%s', '%d', '%s', '%d']
            );
            
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete service
     *
     * @param int $id Service ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('services');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get service property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set service property
     *
     * @param string $key Property name
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Get service ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get service data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
    /**
     * Get all packages for this service
     * Uses the normalized package types with service-package pricing
     *
     * @return array
     */
    public function get_packages() {
        return ServicePackagePricing::get_by_service($this->id);
    }

    /**
     * Get all locations with pricing for this service
     * Uses the normalized locations table with service-location pricing
     *
     * @return array
     */
    public function get_locations() {
        return ServiceLocationPricing::get_by_service($this->id);
    }

    /**
     * Get all states for this service (alias for backward compatibility)
     *
     * @deprecated Use get_locations() instead
     * @return array
     */
    public function get_states() {
        return $this->get_locations();
    }

    /**
     * Get all portals for this service
     *
     * @return array
     */
    public function get_portals() {
        return Portal::get_by_service($this->id);
    }

    /**
     * Get service with all related data
     *
     * @return array Service data with packages, locations, portals
     */
    public function get_full_data() {
        return [
            'id' => $this->id,
            'type' => $this->get('type'),
            'category' => $this->get('category'),
            'name' => $this->get('name'),
            'pricing_model' => $this->get('pricing_model'),
            'has_packages' => (bool) $this->get('has_packages'),
            'description' => $this->get('description'),
            'packages' => $this->get_packages(),
            'locations' => $this->get_locations(),
            'portals' => $this->get_portals(),
        ];
    }
}
