<?php
/**
 * Form class for frontend form rendering and handling
 *
 * @package DSF
 */

namespace DSF;

class Form {
    /**
     * Form instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get form instance
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
        // Initialization if needed
    }

    /**
     * Render form
     *
     * @param array $atts Shortcode attributes
     * @return string Form HTML
     */
    public function render($atts = []) {
        $service_type = isset($_GET['service_type']) ? sanitize_text_field($_GET['service_type']) : '';
        $service_category = isset($_GET['service_category']) ? sanitize_text_field($_GET['service_category']) : '';
        $service_name = isset($_GET['service_name']) ? sanitize_text_field($_GET['service_name']) : '';
        
        // Start output buffering
        ob_start();
        ?>
        <div class="dsf-form-wrapper">
            <form id="dsf-form" class="dsf-form">
                <?php wp_nonce_field('dsf_form_nonce', 'dsf_nonce'); ?>
                
                <input type="hidden" name="action" value="dsf_submit_form">
                <input type="hidden" name="service_type" value="<?php echo esc_attr($service_type); ?>">
                <input type="hidden" name="service_category" value="<?php echo esc_attr($service_category); ?>">
                <input type="hidden" name="service_name" value="<?php echo esc_attr($service_name); ?>">
                
                <!-- Step 1: Service Selection -->
                <div class="dsf-step dsf-step-1" data-step="1">
                    <h2><?php esc_html_e('Step 1: Select Service', 'dynamic-services-form'); ?></h2>
                    <?php 
                    if (empty($service_type)) {
                        $this->render_service_selection();
                    } else {
                        echo '<p>' . esc_html__('Service selected via URL parameters', 'dynamic-services-form') . '</p>';
                    }
                    ?>
                    <button type="button" class="dsf-btn dsf-btn-next" data-step="1">
                        <?php esc_html_e('Next', 'dynamic-services-form'); ?>
                    </button>
                </div>
                
                <!-- Step 2: Pricing & Options -->
                <div class="dsf-step dsf-step-2" data-step="2" style="display:none;">
                    <h2><?php esc_html_e('Step 2: Pricing & Options', 'dynamic-services-form'); ?></h2>
                    <div id="dsf-pricing-options"></div>
                    <div class="dsf-button-group">
                        <button type="button" class="dsf-btn dsf-btn-prev" data-step="2">
                            <?php esc_html_e('Back', 'dynamic-services-form'); ?>
                        </button>
                        <button type="button" class="dsf-btn dsf-btn-next" data-step="2">
                            <?php esc_html_e('Next', 'dynamic-services-form'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Static Fields -->
                <div class="dsf-step dsf-step-3" data-step="3" style="display:none;">
                    <h2><?php esc_html_e('Step 3: Contact Information', 'dynamic-services-form'); ?></h2>
                    <div class="dsf-field-group">
                        <label for="dsf-business-name">
                            <?php esc_html_e('Business Name', 'dynamic-services-form'); ?> <span class="required">*</span>
                        </label>
                        <input type="text" id="dsf-business-name" name="business_name" required>
                    </div>
                    
                    <div class="dsf-field-group">
                        <label for="dsf-email">
                            <?php esc_html_e('Email', 'dynamic-services-form'); ?> <span class="required">*</span>
                        </label>
                        <input type="email" id="dsf-email" name="email" required>
                    </div>
                    
                    <div class="dsf-field-group">
                        <label for="dsf-phone">
                            <?php esc_html_e('Phone', 'dynamic-services-form'); ?> <span class="required">*</span>
                        </label>
                        <input type="tel" id="dsf-phone" name="phone" required>
                    </div>
                    
                    <div class="dsf-field-group">
                        <label for="dsf-entity-type">
                            <?php esc_html_e('Entity Type', 'dynamic-services-form'); ?>
                        </label>
                        <select id="dsf-entity-type" name="entity_type">
                            <option value="">-- Select --</option>
                            <option value="sole-proprietor"><?php esc_html_e('Sole Proprietor', 'dynamic-services-form'); ?></option>
                            <option value="partnership"><?php esc_html_e('Partnership', 'dynamic-services-form'); ?></option>
                            <option value="llc"><?php esc_html_e('LLC', 'dynamic-services-form'); ?></option>
                            <option value="corporation"><?php esc_html_e('Corporation', 'dynamic-services-form'); ?></option>
                        </select>
                    </div>
                    
                    <div class="dsf-field-group">
                        <label for="dsf-notes">
                            <?php esc_html_e('Additional Notes', 'dynamic-services-form'); ?>
                        </label>
                        <textarea id="dsf-notes" name="notes" rows="4"></textarea>
                    </div>
                    
                    <div class="dsf-button-group">
                        <button type="button" class="dsf-btn dsf-btn-prev" data-step="3">
                            <?php esc_html_e('Back', 'dynamic-services-form'); ?>
                        </button>
                        <button type="button" class="dsf-btn dsf-btn-next" data-step="3">
                            <?php esc_html_e('Review', 'dynamic-services-form'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Review & Submit -->
                <div class="dsf-step dsf-step-4" data-step="4" style="display:none;">
                    <h2><?php esc_html_e('Step 4: Review & Submit', 'dynamic-services-form'); ?></h2>
                    <div id="dsf-review-summary"></div>
                    <div id="dsf-price-total" class="dsf-price-total">
                        <strong><?php esc_html_e('Total Price: ', 'dynamic-services-form'); ?></strong>
                        <span id="dsf-total-price">$0.00</span>
                    </div>
                    
                    <div class="dsf-button-group">
                        <button type="button" class="dsf-btn dsf-btn-prev" data-step="4">
                            <?php esc_html_e('Back', 'dynamic-services-form'); ?>
                        </button>
                        <button type="submit" class="dsf-btn dsf-btn-submit">
                            <?php esc_html_e('Submit', 'dynamic-services-form'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div class="dsf-success-message" style="display:none;">
                    <h2><?php esc_html_e('Thank You!', 'dynamic-services-form'); ?></h2>
                    <p><?php esc_html_e('Your form has been submitted successfully.', 'dynamic-services-form'); ?></p>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Render service selection options
     */
    private function render_service_selection() {
        $services = Service::get_all();
        $grouped = [];
        
        // Group services by type and category
        foreach ($services as $service) {
            if (!isset($grouped[$service['type']])) {
                $grouped[$service['type']] = [];
            }
            if (!isset($grouped[$service['type']][$service['category']])) {
                $grouped[$service['type']][$service['category']] = [];
            }
            $grouped[$service['type']][$service['category']][] = $service;
        }
        ?>
        <div class="dsf-service-selection">
            <?php foreach ($grouped as $type => $categories) : ?>
                <div class="dsf-service-type">
                    <h3><?php echo esc_html($type); ?></h3>
                    <?php foreach ($categories as $category => $category_services) : ?>
                        <div class="dsf-service-category">
                            <h4><?php echo esc_html($category); ?></h4>
                            <ul>
                                <?php foreach ($category_services as $service) : ?>
                                    <li>
                                        <label>
                                            <input type="radio" name="service_selection" 
                                                   value="<?php echo esc_attr($service['id']); ?>"
                                                   data-type="<?php echo esc_attr($service['type']); ?>"
                                                   data-category="<?php echo esc_attr($service['category']); ?>"
                                                   data-name="<?php echo esc_attr($service['name']); ?>">
                                            <?php echo esc_html($service['name']); ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Get service pricing options via AJAX
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_pricing_options($service_id) {
        $service = new Service($service_id);
        if (!$service->get_id()) {
            return [];
        }

        $pricing_model = $service->get('pricing_model');
        $options = [
            'service_id' => $service_id,
            'pricing_model' => $pricing_model,
            'has_packages' => (bool) $service->get('has_packages'),
            'html' => self::instance()->render_pricing_options($service),
        ];

        return $options;
    }

    /**
     * Render pricing options based on pricing model
     *
     * @param Service $service Service object
     * @return string HTML
     */
    private function render_pricing_options(Service $service) {
        ob_start();
        $pricing_model = $service->get('pricing_model');
        $has_packages = (bool) $service->get('has_packages');
        ?>
        <div class="dsf-pricing-options-container">
            <?php
            switch ($pricing_model) {
                case 'state_based':
                    $this->render_state_based_pricing($service, $has_packages);
                    break;
                case 'portal_based':
                    $this->render_portal_based_pricing($service);
                    break;
                case 'fixed_price':
                    $this->render_fixed_price($service);
                    break;
                case 'calculator':
                    $this->render_calculator($service);
                    break;
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render state-based pricing
     *
     * @param Service $service Service object
     * @param bool $has_packages Whether service has packages
     */
    private function render_state_based_pricing(Service $service, $has_packages) {
        $locations = $service->get_locations();
        ?>
        <div class="dsf-field-group">
            <label for="dsf-state">
                <?php esc_html_e('Select Location', 'dynamic-services-form'); ?> <span class="required">*</span>
            </label>
            <select id="dsf-state" name="state" required>
                <option value="">-- <?php esc_html_e('Select a location', 'dynamic-services-form'); ?> --</option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?php echo esc_attr($location['location_id']); ?>" 
                            data-location-name="<?php echo esc_attr($location['location_name']); ?>"
                            data-standard-price="<?php echo esc_attr($location['standard_price'] ?? ''); ?>"
                            data-premium-price="<?php echo esc_attr($location['premium_price'] ?? ''); ?>">
                        <?php echo esc_html($location['location_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if ($has_packages) : ?>
            <div class="dsf-field-group">
                <label><?php esc_html_e('Select Package', 'dynamic-services-form'); ?> <span class="required">*</span></label>
                <div class="dsf-packages">
                    <?php
                    $packages = $service->get_packages();
                    foreach ($packages as $package) :
                        ?>
                        <div class="dsf-package-option">
                            <label>
                                <input type="radio" name="package" 
                                       value="<?php echo esc_attr($package['id']); ?>"
                                       data-price="<?php echo esc_attr($package['price'] ?? ''); ?>"
                                       required>
                                <strong><?php echo esc_html($package['package_type']); ?></strong>
                                <?php if (!empty($package['description'])) : ?>
                                    <p><?php echo esc_html($package['description']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($package['price'])) : ?>
                                    <span class="price">$<?php echo number_format((float) $package['price'], 2); ?></span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif;
    }

    /**
     * Render portal-based pricing
     *
     * @param Service $service Service object
     */
    private function render_portal_based_pricing(Service $service) {
        $portals = $service->get_portals();
        ?>
        <div class="dsf-field-group">
            <label><?php esc_html_e('Select Portals', 'dynamic-services-form'); ?> <span class="required">*</span></label>
            <div class="dsf-portals">
                <?php foreach ($portals as $portal) : ?>
                    <div class="dsf-portal-option">
                        <label>
                            <input type="checkbox" name="portals[]" 
                                   value="<?php echo esc_attr($portal['id']); ?>"
                                   data-price="<?php echo esc_attr($portal['price'] ?? ''); ?>">
                            <strong><?php echo esc_html($portal['portal_name']); ?></strong>
                            <?php if (!empty($portal['price'])) : ?>
                                <span class="price">$<?php echo number_format((float) $portal['price'], 2); ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render fixed price option
     *
     * @param Service $service Service object
     */
    private function render_fixed_price(Service $service) {
        // For fixed price, we show the price but don't need user selection
        // The price is typically set in service configuration
        ?>
        <div class="dsf-field-group">
            <p><?php esc_html_e('Price is fixed for this service.', 'dynamic-services-form'); ?></p>
        </div>
        <?php
    }

    /**
     * Render calculator
     *
     * @param Service $service Service object
     */
    private function render_calculator(Service $service) {
        ?>
        <div class="dsf-field-group">
            <label for="dsf-calculator-amount">
                <?php esc_html_e('Enter Amount', 'dynamic-services-form'); ?> <span class="required">*</span>
            </label>
            <input type="number" id="dsf-calculator-amount" name="calculator_amount" 
                   step="0.01" min="0" required 
                   placeholder="<?php esc_attr_e('Enter amount', 'dynamic-services-form'); ?>">
        </div>
        <?php
    }
}
