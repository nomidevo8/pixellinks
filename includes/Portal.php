<?php
/**
 * Portal class for managing portals
 *
 * @package DSF
 */

namespace DSF;

class Portal {
    /**
     * Portal ID
     *
     * @var int
     */
    private $id;
    
    /**
     * Portal data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Portal ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load portal from database
     *
     * @param int $id Portal ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('portals');
        $portal = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$portal) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $portal;
        
        return true;
    }

    /**
     * Get all portals by service
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_by_service($service_id) {
        global $wpdb;
        
        $table = Database::get_table('portals');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d AND enabled = 1 ORDER BY portal_name",
                $service_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get all portals
     *
     * @return array
     */
    public static function get_all() {
        global $wpdb;
        
        $table = Database::get_table('portals');
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY service_id, portal_name", ARRAY_A);
    }

    /**
     * Save portal
     *
     * @param array $args Portal data
     * @return int|bool Portal ID or false
     */
    public static function save($args) {
        global $wpdb;
        
        $defaults = [
            'service_id' => 0,
            'portal_name' => '',
            'price' => null,
            'enabled' => 1,
        ];
        
        $data = wp_parse_args($args, $defaults);
        
        $table = Database::get_table('portals');
        
        if (!empty($data['id'])) {
            // Update existing
            $id = $data['id'];
            unset($data['id']);
            
            $wpdb->update(
                $table,
                $data,
                ['id' => $id],
                ['%d', '%s', '%s', '%d'],
                ['%d']
            );
            
            return $id;
        } else {
            // Insert new
            $wpdb->insert(
                $table,
                $data,
                ['%d', '%s', '%s', '%d']
            );
            
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete portal
     *
     * @param int $id Portal ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('portals');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get portal property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set portal property
     *
     * @param string $key Property name
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Get portal ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get portal data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
}
