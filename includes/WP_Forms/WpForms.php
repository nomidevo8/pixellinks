<?php
namespace DSF\WP_Forms;
use DSF\Database;

/**
 * WP Forms class for WP FORMS management
 *
 * @package DSF
 */

class WpForms {

    /**
     * WpForms instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get WpForms instance
     *
     * @return self
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'wpforms_process_complete', [ $this, 'process_complete' ], 10, 4 );
    }

    /**
     * Process complete action for WP Forms
     *
     * @param array $fields The form fields.
     * @param array $entry The form entry.
     * @param int $form_data The form data.
     * @param int $entry_id The entry ID.
     */
    public function process_complete( $fields, $entry, $form_data, $entry_id ) {
        // Extract submission data from WP Forms
        $submission_data = $this->extract_submission_data( $fields );
        
        // Save submission to database
        $this->save_submission( $submission_data, $fields, $form_data['id'], $entry_id );
    }

    /**
     * Extract relevant submission data from WP Forms fields
     *
     * @param array $fields Form fields array
     * @return array Extracted submission data
     */
    private function extract_submission_data( $fields ) {
        $data = [
            'first_name'      => '',
            'last_name'       => '',
            'business_name'   => '',
            'email'           => '',
            'phone'           => '',
            'entity_type'     => '',
            'state'           => '',
            'city'            => '',
            'business_address' => '',
            'zipcode'         => '',
            'total_price'     => 0,
            'notes'           => '',
        ];

        // Map WP Forms fields to submission data
        foreach ( $fields as $field ) {
            $field_name = strtolower( $field['name'] ?? '' );
            $field_value = $field['value'] ?? '';

            // First Name
            if ( strpos( $field_name, 'first name' ) !== false ) {
                $data['first_name'] = sanitize_text_field( $field_value );
            }

            // Last Name
            if ( strpos( $field_name, 'last name' ) !== false ) {
                $data['last_name'] = sanitize_text_field( $field_value );
            }

            // Company/Business Name
            if ( strpos( $field_name, 'company name' ) !== false || strpos( $field_name, 'business name' ) !== false ) {
                $data['business_name'] = sanitize_text_field( $field_value );
            }

            // Email
            if ( strpos( $field_name, 'email' ) !== false && filter_var( $field_value, FILTER_VALIDATE_EMAIL ) ) {
                $data['email'] = sanitize_email( $field_value );
            }

            // Phone
            if ( strpos( $field_name, 'phone' ) !== false ) {
                $data['phone'] = sanitize_text_field( $field_value );
            }

            // State (from payment-select field)
            if ( $field['type'] === 'payment-select' && strpos( $field_name, 'state' ) !== false ) {
                $data['state'] = sanitize_text_field( $field['value_choice'] ?? '' );
            }

            // Entity Type
            if ( strpos( $field_name, 'entity' ) !== false ) {
                $data['entity_type'] = sanitize_text_field( $field_value );
            }

            // City
            if ( $field['type'] === 'address' && ! empty( $field['city'] ) ) {
                $data['city'] = sanitize_text_field( $field['city'] );
            }

            // Address
            if ( $field['type'] === 'address' && ! empty( $field['address1'] ) ) {
                $data['business_address'] = sanitize_text_field( $field['address1'] );
                if ( ! empty( $field['address2'] ) ) {
                    $data['business_address'] .= ', ' . sanitize_text_field( $field['address2'] );
                }
            }

            // Zipcode/Postal
            if ( $field['type'] === 'address' && ! empty( $field['postal'] ) ) {
                $data['zipcode'] = sanitize_text_field( $field['postal'] );
            }

            // Total Price (use only the last payment-total field value)
            if ( $field['type'] === 'payment-total' ) {
                $amount = floatval( $field['amount_raw'] ?? 0 );
                $data['total_price'] = $amount;
            }

            // Business Purpose or Notes
            if ( strpos( $field_name, 'business purpose' ) !== false || strpos( $field_name, 'purpose' ) !== false ) {
                $data['notes'] = sanitize_textarea_field( $field_value );
            }
        }

        return $data;
    }

    /**
     * Save submission to database
     *
     * @param array $data Extracted submission data
     * @param array $fields Full form fields
     * @param int $form_id WP Forms form ID
     * @param int $entry_id WP Forms entry ID
     */
    private function save_submission( $data, $fields, $form_id, $entry_id ) {
        global $wpdb;

        // Use a default service ID (you may want to map this to actual services)
        $service_id = 1; // Default service ID, can be customized

        $submission_data = [
            'service_id'       => $service_id,
            'form_data'        => wp_json_encode( [
                'form_id'      => $form_id,
                'entry_id'     => $entry_id,
                'form_source'  => 'wpforms',
                'fields'       => $fields,
                'original_submission' => true
            ] ),
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'business_name'    => $data['business_name'],
            'email'            => $data['email'],
            'phone'            => $data['phone'],
            'entity_type'      => $data['entity_type'],
            'state'            => $data['state'],
            'city'             => $data['city'],
            'business_address' => $data['business_address'],
            'zipcode'          => $data['zipcode'],
            'total_price'      => $data['total_price'],
            'notes'            => $data['notes'],
            'status'           => 'pending',
            'created_at'       => current_time( 'mysql' ),
        ];

        $wpdb->insert(
            Database::get_table( 'submissions' ),
            $submission_data,
            [
                '%d',  // service_id
                '%s',  // form_data
                '%s',  // first_name
                '%s',  // last_name
                '%s',  // business_name
                '%s',  // email
                '%s',  // phone
                '%s',  // entity_type
                '%s',  // state
                '%s',  // city
                '%s',  // business_address
                '%s',  // zipcode
                '%f',  // total_price
                '%s',  // notes
                '%s',  // status
                '%s',  // created_at
            ]
        );

        error_log( 'WP Forms submission saved: Entry ID ' . $entry_id . ', Submission ID: ' . $wpdb->insert_id );
    }

}