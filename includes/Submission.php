<?php
/**
 * Submission class for managing form submissions
 *
 * @package DSF
 */

namespace DSF;

class Submission {
    /**
     * Submission ID
     *
     * @var int
     */
    private $id;
    
    /**
     * Submission data
     *
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     *
     * @param int $id Submission ID (optional)
     */
    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Load submission from database
     *
     * @param int $id Submission ID
     * @return bool
     */
    public function load($id) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        $submission = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$submission) {
            return false;
        }
        
        $this->id = $id;
        $this->data = $submission;
        
        return true;
    }

    /**
     * Get all submissions
     *
     * @param int $limit Limit number of results
     * @param int $offset Offset for pagination
     * @return array
     */
    public static function get_all($limit = 50, $offset = 0) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Get submissions by service
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_by_service($service_id) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE service_id = %d ORDER BY created_at DESC",
                $service_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get submissions by email
     *
     * @param string $email Email address
     * @return array
     */
    public static function get_by_email($email) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE email = %s ORDER BY created_at DESC",
                $email
            ),
            ARRAY_A
        );
    }

    /**
     * Get submissions by status
     *
     * @param string $status Status
     * @return array
     */
    public static function get_by_status($status) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC",
                $status
            ),
            ARRAY_A
        );
    }

    /**
     * Update submission status
     *
     * @param int $id Submission ID
     * @param string $status New status
     * @return bool
     */
    public static function update_status($id, $status) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (bool) $wpdb->update(
            $table,
            ['status' => $status, 'updated_at' => current_time('mysql')],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );
    }

    /**
     * Delete submission
     *
     * @param int $id Submission ID
     * @return bool
     */
    public static function delete($id) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (bool) $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    /**
     * Get submission property
     *
     * @param string $key Property name
     * @return mixed
     */
    public function get($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Set submission property
     *
     * @param string $key Property name
     * @param mixed $value Property value
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Get submission ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get submission data as array
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Get form data decoded from JSON
     *
     * @return array
     */
    public function get_form_data() {
        $form_data_json = $this->get('form_data');
        if ($form_data_json) {
            return json_decode($form_data_json, true);
        }
        return [];
    }

    /**
     * Get service for this submission
     *
     * @return Service|null
     */
    public function get_service() {
        $service_id = $this->get('service_id');
        if ($service_id) {
            return new Service($service_id);
        }
        return null;
    }

    /**
     * Count total submissions
     *
     * @return int
     */
    public static function count_all() {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * Count submissions by status
     *
     * @param string $status Status to count
     * @return int
     */
    public static function count_by_status($status) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE status = %s", $status)
        );
    }

    /**
     * Get total revenue from submissions
     *
     * @return float
     */
    public static function get_total_revenue() {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (float) $wpdb->get_var("SELECT SUM(total_price) FROM {$table}");
    }

    /**
     * Get revenue by service
     *
     * @param int $service_id Service ID
     * @return float
     */
    public static function get_service_revenue($service_id) {
        global $wpdb;
        
        $table = Database::get_table('submissions');
        return (float) $wpdb->get_var(
            $wpdb->prepare("SELECT SUM(total_price) FROM {$table} WHERE service_id = %d", $service_id)
        );
    }
}
