<?php
/**
 * Package class for managing packages
 *
 * @package DSF
 */

namespace DSF;

class Package {
    /**
     * Package ID
     *
     * @var int
     */
    private $id;
    
    /**
     * Package data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Package ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load package from database
     *
     * @param int $id Package ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('packages');
        $package = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$package) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $package;
        
        return true;
    }

    /**
     * Get all packages by service
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_by_service($service_id) {
        global $wpdb;
        
        $table = Database::get_table('packages');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d AND enabled = 1 ORDER BY package_type",
                $service_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get all packages
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        
        $table = Database::get_table('packages');
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY service_id, package_type", ARRAY_A);
    }

    /**
     * Save package
     *
     * @param array $args Package data
     * @return int|bool Package ID or false
     */
    public static function save($args) {
        global $wpdb;
        
        $defaults = [
            'service_id' => 0,
            'package_type' => '',
            'price' => null,
            'description' => '',
            'enabled' => 1,
        ];
        
        $data = wp_parse_args($args, $defaults);
        
        $table = Database::get_table('packages');
        
        if (!empty($data['id'])) {
            // Update existing
            $id = $data['id'];
            unset($data['id']);
            
            $wpdb->update(
                $table,
                $data,
                ['id' => $id],
                ['%d', '%s', '%s', '%s', '%d'],
                ['%d']
            );
            
            return $id;
        } else {
            // Insert new
            $wpdb->insert(
                $table,
                $data,
                ['%d', '%s', '%s', '%s', '%d']
            );
            
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete package
     *
     * @param int $id Package ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('packages');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get package property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set package property
     *
     * @param string $key Property name
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Get package ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get package data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
}
