<?php
/**
 * Ajax class for handling form submissions
 *
 * @package DSF
 */

namespace DSF;

class Ajax {
    /**
     * Ajax instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get ajax instance
     *
     * @return self
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX actions for public/authenticated users
        add_action('wp_ajax_dsf_get_pricing_options', [$this, 'get_pricing_options']);
        add_action('wp_ajax_nopriv_dsf_get_pricing_options', [$this, 'get_pricing_options']);
        
        add_action('wp_ajax_dsf_calculate_price', [$this, 'calculate_price']);
        add_action('wp_ajax_nopriv_dsf_calculate_price', [$this, 'calculate_price']);
        
        add_action('wp_ajax_dsf_submit_form', [$this, 'submit_form']);
        add_action('wp_ajax_nopriv_dsf_submit_form', [$this, 'submit_form']);
        
        add_action('wp_ajax_dsf_get_review_summary', [$this, 'get_review_summary']);
        add_action('wp_ajax_nopriv_dsf_get_review_summary', [$this, 'get_review_summary']);
    }

    /**
     * Get pricing options for a service
     */
    public function get_pricing_options() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        
        if (!$service_id) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        $service = new Service($service_id);
        if (!$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        $options = Form::get_pricing_options($service->get_id());
        wp_send_json_success($options);
    }

    /**
     * Calculate total price based on form inputs
     */
    public function calculate_price() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $location_id = isset($_POST['location_id']) ? intval($_POST['location_id']) : 0;
        $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
        $portal_id = isset($_POST['portal_id']) ? intval($_POST['portal_id']) : 0;
        $calculator_amount = isset($_POST['calculator_amount']) ? floatval($_POST['calculator_amount']) : 0;
        
        $service = new Service($service_id);
        if (!$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        $total_price = 0;
        $price_label = 'Service Fee';
        $pricing_model = $service->get('pricing_model');
        
        switch ($pricing_model) {
            case 'state_based':
                if ($location_id) {
                    $location = new Location($location_id);
                    if ($location->get_id()) {
                        $price_label = $location->get('name');
                    }
                    
                    if ($service->get('has_packages') && $package_id) {
                        // Package pricing model: get price from ServicePackagePricing
                        $package_pricing = ServicePackagePricing::get_by_service_and_package_type($service->get_id(), $package_id);
                        if ($package_pricing) {
                            $total_price = floatval($package_pricing['price'] ?? 0);
                            $price_label = $package_pricing['package_type_name'] ?? 'Package';
                        }
                    } else {
                        // No packages: use location-based pricing (universal or standard tier)
                        $pricing = ServiceLocationPricing::get_by_service_and_location($service->get_id(), $location_id);
                        if ($pricing) {
                            $total_price = floatval($pricing['standard_price'] ?? 0);
                            $price_label = $pricing['location_name'] ?? 'Service Fee';
                        }
                    }
                }
                break;
                
            case 'portal_based':
                $portal_id = isset($_POST['portal_id']) ? intval($_POST['portal_id']) : 0;
                if ($portal_id) {
                    $portal = new Portal($portal_id);
                    if ($portal->get_id()) {
                        $total_price = floatval($portal->get('price') ?? 0);
                        $price_label = $portal->get('portal_name') ?? 'Portal Fee';
                    }
                }
                break;
                
            case 'fixed_price':
                // Get fixed price from service configuration
                $fixed_price = $service->get('fixed_price') ?? 0;
                $total_price = floatval($fixed_price);
                $price_label = 'Fixed Price';
                break;
                
            case 'calculator':
                // Calculator with tiered pricing
                $total_price = $this->calculate_tiered_price($calculator_amount);
                $price_label = 'Calculated Fee';
                break;
        }
        
        wp_send_json_success([
            'total_price' => round($total_price, 2),
            'price_label' => $price_label,
        ]);
    }

    /**
     * Get review summary
     */
    public function get_review_summary() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $form_data = isset($_POST['form_data']) ? wp_unslash($_POST['form_data']) : [];
        
        // Parse form data
        $service_id = isset($form_data['service_id']) ? intval($form_data['service_id']) : 0;
        
        $service = new Service($service_id);
        if (!$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        ob_start();
        ?>
        <div class="dsf-review-item">
            <h3><?php esc_html_e('Selected Service', 'dynamic-services-form'); ?></h3>
            <p>
                <strong><?php echo esc_html($service->get('type')); ?> - <?php echo esc_html($service->get('category')); ?></strong><br>
                <?php echo esc_html($service->get('name')); ?>
            </p>
        </div>
        
        <div class="dsf-review-item">
            <h3><?php esc_html_e('Pricing Details', 'dynamic-services-form'); ?></h3>
            <div id="dsf-pricing-details"></div>
        </div>
        
        <div class="dsf-review-item">
            <h3><?php esc_html_e('Contact Information', 'dynamic-services-form'); ?></h3>
            <p>
                <strong><?php esc_html_e('Business Name:', 'dynamic-services-form'); ?></strong> 
                <span><?php echo isset($form_data['business_name']) ? esc_html($form_data['business_name']) : '--'; ?></span><br>
                <strong><?php esc_html_e('Email:', 'dynamic-services-form'); ?></strong> 
                <span><?php echo isset($form_data['email']) ? esc_html($form_data['email']) : '--'; ?></span><br>
                <strong><?php esc_html_e('Phone:', 'dynamic-services-form'); ?></strong> 
                <span><?php echo isset($form_data['phone']) ? esc_html($form_data['phone']) : '--'; ?></span><br>
                <strong><?php esc_html_e('Entity Type:', 'dynamic-services-form'); ?></strong> 
                <span><?php echo isset($form_data['entity_type']) ? esc_html($form_data['entity_type']) : '--'; ?></span>
            </p>
        </div>
        <?php
        
        wp_send_json_success([
            'html' => ob_get_clean(),
        ]);
    }

    /**
     * Submit form
     */
    public function submit_form() {
        check_ajax_referer('dsf_form_nonce', 'nonce');
        
        $service_type = isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : '';
        $service_category = isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : '';
        $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        
        // Load service
        if ($service_id) {
            $service = new Service($service_id);
        } else {
            $service = Service::get_by_identifier($service_type, $service_category, $service_name);
        }
        
        if (!$service || !$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'business_name', 'business_address', 'phone', 'email', 'city', 'state', 'zipcode'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['message' => sprintf('Field %s is required', $field)]);
            }
        }
        
        // Sanitize form data
        $form_data = [
            'location_id' => isset($_POST['location_id']) ? intval($_POST['location_id']) : null,
            'package_id' => isset($_POST['package_id']) ? intval($_POST['package_id']) : null,
            'portal_id' => isset($_POST['portal_id']) ? intval($_POST['portal_id']) : null,
            'calculator_amount' => isset($_POST['calculator_amount']) ? floatval($_POST['calculator_amount']) : null,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'business_name' => sanitize_text_field($_POST['business_name']),
            'business_address' => sanitize_text_field($_POST['business_address']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'zipcode' => sanitize_text_field($_POST['zipcode']),
            'entity_type' => isset($_POST['entity_type']) ? sanitize_text_field($_POST['entity_type']) : '',
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '',
        ];
        
        // Calculate total price
        $total_price = $this->calculate_total_price($service->get_id(), $form_data);
        
        // Save submission
        global $wpdb;
        $result = $wpdb->insert(
            Database::get_table('submissions'),
            [
                'service_id' => $service->get_id(),
                'form_data' => wp_json_encode($form_data),
                'total_price' => $total_price,
                'first_name' => $form_data['first_name'],
                'last_name' => $form_data['last_name'],
                'business_name' => $form_data['business_name'],
                'business_address' => $form_data['business_address'],
                'phone' => $form_data['phone'],
                'email' => $form_data['email'],
                'city' => $form_data['city'],
                'state' => $form_data['state'],
                'zipcode' => $form_data['zipcode'],
                'entity_type' => $form_data['entity_type'],
                'notes' => $form_data['notes'],
                'status' => 'pending',
            ],
            ['%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$result) {
            wp_send_json_error(['message' => 'Failed to submit form']);
        }
        
        // Do something with the submission (e.g., send email)
        do_action('dsf_form_submitted', $wpdb->insert_id, $service, $form_data, $total_price);
        
        wp_send_json_success([
            'message' => 'Form submitted successfully',
            'submission_id' => $wpdb->insert_id,
        ]);
    }

    /**
     * Calculate total price based on service and form data
     *
     * @param int $service_id Service ID
     * @param array $form_data Form data
     * @return float Total price
     */
    private function calculate_total_price($service_id, $form_data) {
        $service = new Service($service_id);
        $pricing_model = $service->get('pricing_model');
        $total_price = 0;
        
        switch ($pricing_model) {
            case 'state_based':
                if (!empty($form_data['location_id'])) {
                    if ($service->get('has_packages') && !empty($form_data['package_id'])) {
                        // Package pricing: get price from ServicePackagePricing
                        $package_pricing = ServicePackagePricing::get_by_service_and_package_type($service_id, $form_data['package_id']);
                        if ($package_pricing) {
                            $total_price = floatval($package_pricing['price'] ?? 0);
                        }
                    } else {
                        // Location pricing: use standard_price tier
                        $pricing = ServiceLocationPricing::get_by_service_and_location($service_id, $form_data['location_id']);
                        if ($pricing) {
                            $total_price = floatval($pricing['standard_price'] ?? 0);
                        }
                    }
                }
                break;
                
            case 'portal_based':
                if (!empty($form_data['portal_id'])) {
                    $portal = new Portal($form_data['portal_id']);
                    if ($portal->get_id()) {
                        $total_price = floatval($portal->get('price') ?? 0);
                    }
                }
                break;
                
            case 'calculator':
                $total_price = $this->calculate_tiered_price($form_data['calculator_amount'] ?? 0);
                break;
        }
        
        return round($total_price, 2);
    }

    /**
     * Calculate price based on tiered calculator pricing
     * Tiers:
     * - $350,000 - $500,000: $900.00
     * - $500,000 - $2,000,000: $1,500.00
     * - $2,000,000+: 1% of total amount
     *
     * @param float $amount The amount entered by user
     * @return float Calculated price
     */
    private function calculate_tiered_price($amount) {
        if ($amount < 350000) {
            return 0; // Below minimum threshold
        } else if ($amount >= 350000 && $amount < 500000) {
            return 900.00;
        } else if ($amount >= 500000 && $amount < 2000000) {
            return 1500.00;
        } else {
            // $2,000,000+: 1% of total amount
            return $amount * 0.01;
        }
    }
}
