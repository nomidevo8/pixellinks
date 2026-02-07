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
        
        $service_type = isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : '';
        $service_category = isset($_POST['service_category']) ? sanitize_text_field($_POST['service_category']) : '';
        $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        
        // Load service either by ID or by identifiers
        if ($service_id) {
            $service = new Service($service_id);
        } else {
            $service = Service::get_by_identifier($service_type, $service_category, $service_name);
        }
        
        if (!$service || !$service->get_id()) {
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
        $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
        $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
        $portal_ids = isset($_POST['portal_ids']) ? array_map('intval', (array) $_POST['portal_ids']) : [];
        $calculator_amount = isset($_POST['calculator_amount']) ? floatval($_POST['calculator_amount']) : 0;
        
        $service = new Service($service_id);
        if (!$service->get_id()) {
            wp_send_json_error(['message' => 'Service not found']);
        }
        
        $total_price = 0;
        $pricing_model = $service->get('pricing_model');
        $breakdown = [];
        
        switch ($pricing_model) {
            case 'state_based':
                if ($state_id) {
                    // state_id is actually location_id in the new schema
                    $pricing = ServiceLocationPricing::get_by_service_and_location($service->get_id(), $state_id);
                    
                    if ($pricing) {
                        if ($service->get('has_packages') && $package_id) {
                            $package = new Package($package_id);
                            $location_price = ($package->get('package_type') === 'Premium' && $pricing['premium_price'] !== null)
                                ? floatval($pricing['premium_price'])
                                : floatval($pricing['standard_price'] ?? 0);
                            $total_price = $location_price;
                            $breakdown['location'] = $pricing['location_name'];
                            $breakdown['package'] = $package->get('package_type');
                            $breakdown['price'] = $location_price;
                        } else {
                            $total_price = floatval($pricing['standard_price'] ?? 0);
                            $breakdown['location'] = $pricing['location_name'];
                            $breakdown['price'] = $total_price;
                        }
                    }
                }
                break;
                
            case 'portal_based':
                foreach ($portal_ids as $portal_id) {
                    $portal = new Portal($portal_id);
                    if ($portal->get_id()) {
                        $price = floatval($portal->get('price') ?? 0);
                        $total_price += $price;
                        $breakdown['portals'][] = [
                            'name' => $portal->get('portal_name'),
                            'price' => $price,
                        ];
                    }
                }
                break;
                
            case 'fixed_price':
                // TODO: Get fixed price from service configuration
                $total_price = 0;
                break;
                
            case 'calculator':
                // TODO: Implement pricing formula based on calculator amount
                $total_price = $calculator_amount;
                break;
        }
        
        wp_send_json_success([
            'total_price' => round($total_price, 2),
            'breakdown' => $breakdown,
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
        $required_fields = ['business_name', 'email', 'phone'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['message' => sprintf('Field %s is required', $field)]);
            }
        }
        
        // Sanitize form data
        $form_data = [
            'state' => isset($_POST['state']) ? intval($_POST['state']) : null,
            'package' => isset($_POST['package']) ? intval($_POST['package']) : null,
            'portals' => isset($_POST['portals']) ? array_map('intval', (array) $_POST['portals']) : [],
            'calculator_amount' => isset($_POST['calculator_amount']) ? floatval($_POST['calculator_amount']) : null,
            'business_name' => sanitize_text_field($_POST['business_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
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
                'business_name' => $form_data['business_name'],
                'email' => $form_data['email'],
                'phone' => $form_data['phone'],
                'entity_type' => $form_data['entity_type'],
                'notes' => $form_data['notes'],
                'status' => 'pending',
            ],
            ['%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s']
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
                if (!empty($form_data['state'])) {
                    // form_data['state'] contains location_id
                    $pricing = ServiceLocationPricing::get_by_service_and_location($service_id, $form_data['state']);
                    
                    if ($pricing) {
                        if ($service->get('has_packages') && !empty($form_data['package'])) {
                            $package = new Package($form_data['package']);
                            $total_price = ($package->get('package_type') === 'Premium' && $pricing['premium_price'] !== null)
                                ? floatval($pricing['premium_price'])
                                : floatval($pricing['standard_price'] ?? 0);
                        } else {
                            $total_price = floatval($pricing['standard_price'] ?? 0);
                        }
                    }
                }
                break;
                
            case 'portal_based':
                foreach ($form_data['portals'] as $portal_id) {
                    $portal = new Portal($portal_id);
                    if ($portal->get_id()) {
                        $total_price += floatval($portal->get('price') ?? 0);
                    }
                }
                break;
                
            case 'calculator':
                $total_price = floatval($form_data['calculator_amount'] ?? 0);
                break;
        }
        
        return round($total_price, 2);
    }
}
