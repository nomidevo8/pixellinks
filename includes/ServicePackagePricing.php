<?php
/**
 * ServicePackagePricing model for service-package pricing relationships
 *
 * @package DSF
 */

namespace DSF;

class ServicePackagePricing {
    /**
     * Pricing data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Pricing ID (optional)
     */
    public function __construct($id = 0) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load pricing by ID
     *
     * @param int $id Pricing ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        
        $pricing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($pricing) {
            $this->data = $pricing;
            return true;
        }
        
        return false;
    }

    /**
     * Get pricing by service and package type
     *
     * @param int $service_id Service ID
     * @param int $package_type_id Package type ID
     * @return array|null
     */
    public static function get_by_service_and_package_type($service_id, $package_type_id) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d AND package_type_id = %d",
                $service_id,
                $package_type_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get all packages for a service
     *
     * @param int $service_id Service ID
     * @param bool $enabled Get only enabled packages
     * @return array
     */
    public static function get_by_service($service_id, $enabled = true) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        $package_types_table = Database::get_table('package_types');
        
        $query = "SELECT spp.*, pt.package_type_name
                  FROM {$table} spp
                  INNER JOIN {$package_types_table} pt ON spp.package_type_id = pt.id
                  WHERE spp.service_id = %d";
        
        $params = [$service_id];
        
        if ($enabled) {
            $query .= " AND spp.enabled = %d";
            $params[] = 1;
        }
        
        $query .= " ORDER BY pt.package_type_name ASC";
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $params),
            ARRAY_A
        );
    }

    /**
     * Get all pricing for a package type (across all services)
     *
     * @param int $package_type_id Package type ID
     * @param bool $enabled Get only enabled pricing
     * @return array
     */
    public static function get_by_package_type($package_type_id, $enabled = true) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        $services_table = Database::get_table('services');
        
        $query = "SELECT spp.*, s.name as service_name, s.type, s.category
                  FROM {$table} spp
                  INNER JOIN {$services_table} s ON spp.service_id = s.id
                  WHERE spp.package_type_id = %d";
        
        $params = [$package_type_id];
        
        if ($enabled) {
            $query .= " AND spp.enabled = %d";
            $params[] = 1;
        }
        
        $query .= " ORDER BY s.type, s.category, s.name ASC";
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $params),
            ARRAY_A
        );
    }

    /**
     * Get all pricing records (with optional pagination)
     *
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public static function get_all($limit = 0, $offset = 0) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        $services_table = Database::get_table('services');
        $package_types_table = Database::get_table('package_types');
        
        $query = "SELECT spp.*, s.name as service_name, pt.package_type_name
                  FROM {$table} spp
                  INNER JOIN {$services_table} s ON spp.service_id = s.id
                  INNER JOIN {$package_types_table} pt ON spp.package_type_id = pt.id
                  ORDER BY s.name, pt.package_type_name";
        
        if ($limit) {
            $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Save pricing
     *
     * @param array $args Pricing arguments
     * @return int|false Pricing ID or false
     */
    public static function save($args = []) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        
        $defaults = [
            'id' => 0,
            'service_id' => 0,
            'package_type_id' => 0,
            'price' => null,
            'description' => '',
            'enabled' => 1,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $data = [
            'service_id' => intval($args['service_id']),
            'package_type_id' => intval($args['package_type_id']),
            'price' => $args['price'] !== null ? floatval($args['price']) : null,
            'description' => sanitize_textarea_field($args['description']),
            'enabled' => intval($args['enabled']),
        ];
        
        $format = ['%d', '%d', '%f', '%s', '%d'];
        
        if ($args['id']) {
            $wpdb->update($table, $data, ['id' => $args['id']], $format, ['%d']);
            return $args['id'];
        } else {
            $wpdb->insert($table, $data, $format);
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete pricing
     *
     * @param int $id Pricing ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        
        return $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Delete all pricing for a service
     *
     * @param int $service_id Service ID
     * @return bool
     */
    public static function delete_by_service($service_id) {
        global $wpdb;
        $table = Database::get_table('service_package_pricing');
        
        return $wpdb->delete($table, ['service_id' => $service_id], ['%d']);
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
