<?php
/**
 * State class for managing states
 *
 * @package DSF
 */

namespace DSF;

class State {
    /**
     * State ID
     *
     * @var int
     */
    private $id;
    
    /**
     * State data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id State ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load state from database
     *
     * @param int $id State ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('states');
        $state = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$state) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $state;
        
        return true;
    }

    /**
     * Get all states by service
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_by_service($service_id) {
        global $wpdb;
        
        $table = Database::get_table('states');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d AND enabled = 1 ORDER BY state_name",
                $service_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get all states
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        
        $table = Database::get_table('states');
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY service_id, state_name", ARRAY_A);
    }

    /**
     * Save state
     *
     * @param array $args State data
     * @return int|bool State ID or false
     */
    public static function save($args) {
        global $wpdb;
        
        $defaults = [
            'service_id' => 0,
            'state_name' => '',
            'standard_price' => null,
            'premium_price' => null,
            'enabled' => 1,
        ];
        
        $data = wp_parse_args($args, $defaults);
        
        $table = Database::get_table('states');
        
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
     * Delete state
     *
     * @param int $id State ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('states');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get state property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set state property
     *
     * @param string $key Property name
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Get state ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get state data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
}
