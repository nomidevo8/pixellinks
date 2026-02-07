<?php
/**
 * PackageType model for normalized package types
 *
 * @package DSF
 */

namespace DSF;

class PackageType {
    /**
     * PackageType data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id PackageType ID (optional)
     */
    public function __construct($id = 0) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load package type by ID
     *
     * @param int $id PackageType ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        $table = Database::get_table('package_types');
        
        $package_type = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($package_type) {
            $this->data = $package_type;
            return true;
        }
        
        return false;
    }

    /**
     * Get package type by name
     *
     * @param string $name Package type name
     * @return array|null
     */
    public static function get_by_name($name) {
        global $wpdb;
        $table = Database::get_table('package_types');
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE package_type_name = %s",
                $name
            ),
            ARRAY_A
        );
    }

    /**
     * Get all package types
     *
     * @param bool $enabled Get only enabled package types
     * @return array
     */
    public static function get_all($enabled = true) {
        global $wpdb;
        $table = Database::get_table('package_types');
        
        $query = "SELECT * FROM {$table}";
        $params = [];
        
        if ($enabled) {
            $query .= " WHERE enabled = %d";
            $params[] = 1;
        }
        
        $query .= " ORDER BY package_type_name ASC";
        
        if (empty($params)) {
            return $wpdb->get_results($query, ARRAY_A);
        }
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $params),
            ARRAY_A
        );
    }

    /**
     * Save package type
     *
     * @param array $args PackageType arguments
     * @return int|false PackageType ID or false on failure
     */
    public static function save($args = []) {
        global $wpdb;
        $table = Database::get_table('package_types');
        
        $defaults = [
            'id' => 0,
            'package_type_name' => '',
            'description' => '',
            'enabled' => 1,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $data = [
            'package_type_name' => sanitize_text_field($args['package_type_name']),
            'description' => sanitize_textarea_field($args['description']),
            'enabled' => intval($args['enabled']),
        ];
        
        if ($args['id']) {
            $wpdb->update($table, $data, ['id' => $args['id']], ['%s', '%s', '%d'], ['%d']);
            return $args['id'];
        } else {
            $wpdb->insert($table, $data, ['%s', '%s', '%d']);
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete package type
     *
     * @param int $id PackageType ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = Database::get_table('package_types');
        
        return $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function get_id() {
        return intval($this->data['id'] ?? 0);
    }

    /**
     * Get specific data
     *
     * @param string $key Data key
     * @return mixed
     */
    public function get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * Get all data
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
}
