<?php
/**
 * Location model for states/regions
 *
 * @package DSF
 */

namespace DSF;

class Location {
    /**
     * Location data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Location ID (optional)
     */
    public function __construct($id = 0) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load location by ID
     *
     * @param int $id Location ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        $table = Database::get_table('locations');
        
        $location = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($location) {
            $this->data = $location;
            return true;
        }
        
        return false;
    }

    /**
     * Get location by name and type
     *
     * @param string $name Location name
     * @param string $type Location type (state, region, etc.)
     * @return array|null
     */
    public static function get_by_name($name, $type = 'state') {
        global $wpdb;
        $table = Database::get_table('locations');
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE location_name = %s AND location_type = %s",
                $name,
                $type
            ),
            ARRAY_A
        );
    }

    /**
     * Get all locations
     *
     * @param string $type Filter by location type (optional)
     * @param bool $enabled Get only enabled locations
     * @return array
     */
    public static function get_all($type = null, $enabled = true) {
        global $wpdb;
        $table = Database::get_table('locations');
        
        $query = "SELECT * FROM {$table}";
        $params = [];
        
        if ($type) {
            $query .= " WHERE location_type = %s";
            $params[] = $type;
        }
        
        if ($enabled) {
            $query .= ($type ? " AND" : " WHERE") . " enabled = %d";
            $params[] = 1;
        }
        
        $query .= " ORDER BY location_name ASC";
        
        if (empty($params)) {
            return $wpdb->get_results($query, ARRAY_A);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $params),
            ARRAY_A
        );
    }

    /**
     * Save location
     *
     * @param array $args Location arguments
     * @return int|false Location ID or false on failure
     */
    public static function save($args = []) {
        global $wpdb;
        $table = Database::get_table('locations');
        
        $defaults = [
            'id' => 0,
            'location_name' => '',
            'location_code' => '',
            'location_type' => 'state',
            'enabled' => 1,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $data = [
            'location_name' => sanitize_text_field($args['location_name']),
            'location_code' => sanitize_text_field($args['location_code']),
            'location_type' => sanitize_text_field($args['location_type']),
            'enabled' => intval($args['enabled']),
        ];
        
        if ($args['id']) {
            $wpdb->update($table, $data, ['id' => $args['id']], ['%s', '%s', '%s', '%d'], ['%d']);
            return $args['id'];
        } else {
            $wpdb->insert($table, $data, ['%s', '%s', '%s', '%d']);
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete location
     *
     * @param int $id Location ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = Database::get_table('locations');
        
        return $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get location ID
     *
     * @return int
     */
    public function get_id() {
        return intval($this->data['id'] ?? 0);
    }

    /**
     * Get specific location data
     *
     * @param string $key Data key
     * @return mixed
     */
    public function get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * Get all location data
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
}
