<?php
/**
 * ServiceLocationPricing model for service-location pricing relationships
 *
 * @package DSF
 */

namespace DSF;

class ServiceLocationPricing {
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
        $table = Database::get_table('service_location_pricing');
        
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
     * Get pricing by service and location
     *
     * @param int $service_id Service ID
     * @param int $location_id Location ID
     * @return array|null
     */
    public static function get_by_service_and_location($service_id, $location_id) {
        global $wpdb;
        $table = Database::get_table('service_location_pricing');
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d AND location_id = %d",
                $service_id,
                $location_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get all pricing for a service
     *
     * @param int $service_id Service ID
     * @param bool $enabled Get only enabled pricing
     * @return array
     */
    public static function get_by_service($service_id, $enabled = true) {
        global $wpdb;
        $table = Database::get_table('service_location_pricing');
        $locations_table = Database::get_table('locations');
        
        $query = "SELECT slp.*, l.location_name, l.location_code, l.location_type
                  FROM {$table} slp
                  INNER JOIN {$locations_table} l ON slp.location_id = l.id
                  WHERE slp.service_id = %d";
        
        $params = [$service_id];
        
        if ($enabled) {
            $query .= " AND slp.enabled = %d";
            $params[] = 1;
        }
        
        $query .= " ORDER BY l.location_name ASC";
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $params),
            ARRAY_A
        );
    }

    /**
     * Get all pricing for a location (across all services)
     *
     * @param int $location_id Location ID
     * @param bool $enabled Get only enabled pricing
     * @return array
     */
    public static function get_by_location($location_id, $enabled = true) {
        global $wpdb;
        $table = Database::get_table('service_location_pricing');
        $services_table = Database::get_table('services');
        
        $query = "SELECT slp.*, s.name as service_name, s.type, s.category
                  FROM {$table} slp
                  INNER JOIN {$services_table} s ON slp.service_id = s.id
                  WHERE slp.location_id = %d";
        
        $params = [$location_id];
        
        if ($enabled) {
            $query .= " AND slp.enabled = %d";
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
        $table = Database::get_table('service_location_pricing');
        $services_table = Database::get_table('services');
        $locations_table = Database::get_table('locations');
        
        $query = "SELECT slp.*, s.name as service_name, l.location_name
                  FROM {$table} slp
                  INNER JOIN {$services_table} s ON slp.service_id = s.id
                  INNER JOIN {$locations_table} l ON slp.location_id = l.id
                  ORDER BY s.name, l.location_name";
        
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
        $table = Database::get_table('service_location_pricing');
        
        $defaults = [
            'id' => 0,
            'service_id' => 0,
            'location_id' => 0,
            'standard_price' => null,
            'premium_price' => null,
            'is_universal' => 0,
            'enabled' => 1,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $data = [
            'service_id' => intval($args['service_id']),
            'location_id' => intval($args['location_id']),
            'standard_price' => $args['standard_price'] !== null ? floatval($args['standard_price']) : null,
            'premium_price' => $args['premium_price'] !== null ? floatval($args['premium_price']) : null,
            'is_universal' => intval($args['is_universal']),
            'enabled' => intval($args['enabled']),
        ];
        
        $format = ['%d', '%d', '%f', '%f', '%d', '%d'];
        
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
        $table = Database::get_table('service_location_pricing');
        
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
        $table = Database::get_table('service_location_pricing');
        
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
