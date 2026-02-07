<?php
/**
 * Form class for frontend form rendering and handling
 * 2-Step Workflow: Step 1 (Service + Pricing) -> Step 2 (Contact Info)
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
     * Render main form - 2 Step workflow
     * Step 1: Service + Pricing Options
     * Step 2: Contact Information
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
            <!-- Progress Bar -->
            <div class="dsf-progress-bar">
                <div class="dsf-progress-item dsf-active">
                    <span class="dsf-progress-number">1</span>
                    <span class="dsf-progress-label"><?php esc_html_e('Service & Pricing', 'dynamic-services-form'); ?></span>
                </div>
                <div class="dsf-progress-line"></div>
                <div class="dsf-progress-item">
                    <span class="dsf-progress-number">2</span>
                    <span class="dsf-progress-label"><?php esc_html_e('Contact Info', 'dynamic-services-form'); ?></span>
                </div>
            </div>

            <form id="dsf-form" class="dsf-form">
                <?php wp_nonce_field('dsf_form_nonce', 'dsf_nonce'); ?>
                
                <input type="hidden" name="action" value="dsf_submit_form">
                <input type="hidden" name="service_type" value="<?php echo esc_attr($service_type); ?>">
                <input type="hidden" name="service_category" value="<?php echo esc_attr($service_category); ?>">
                <input type="hidden" name="service_name" value="<?php echo esc_attr($service_name); ?>">
                
                <!-- STEP 1: Service Selection & Pricing Options -->
                <div class="dsf-step dsf-step-1" data-step="1">
                    <h2 class="dsf-step-title"><?php esc_html_e('Select Your Service & Pricing', 'dynamic-services-form'); ?></h2>
                    
                    <!-- Service Dropdown -->
                    <div class="dsf-field-group">
                        <label for="dsf-service-select">
                            <?php esc_html_e('Service', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                        </label>
                        <select id="dsf-service-select" name="service_id" required>
                            <option value=""><?php esc_html_e('-- Select a Service --', 'dynamic-services-form'); ?></option>
                            <?php $this->render_service_dropdown_options(); ?>
                        </select>
                    </div>

                    <!-- Dynamic Pricing Options Container -->
                    <div id="dsf-pricing-options-container" class="dsf-pricing-options-container">
                        <!-- Content loaded via AJAX after service selection -->
                    </div>

                    <!-- Price Display -->
                    <div class="dsf-price-display">
                        <table class="dsf-price-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Item', 'dynamic-services-form'); ?></th>
                                    <th><?php esc_html_e('Total', 'dynamic-services-form'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="dsf-price-row">
                                    <td id="dsf-price-label">--</td>
                                    <td id="dsf-price-value">--</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="dsf-price-total-row">
                                    <td><?php esc_html_e('Total', 'dynamic-services-form'); ?></td>
                                    <td id="dsf-total-price-display">$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Step Navigation -->
                    <div class="dsf-button-group">
                        <button type="button" class="dsf-btn dsf-btn-primary dsf-btn-next" data-step="1">
                            <?php esc_html_e('Next', 'dynamic-services-form'); ?>
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Contact Information -->
                <div class="dsf-step dsf-step-2" data-step="2" style="display:none;">
                    <h2 class="dsf-step-title"><?php esc_html_e('Contact Information', 'dynamic-services-form'); ?></h2>
                    
                    <div class="dsf-form-grid">
                        <!-- First & Last Name Row -->
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-first-name">
                                <?php esc_html_e('First Name', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-first-name" name="first_name" required>
                        </div>
                        
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-last-name">
                                <?php esc_html_e('Last Name', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-last-name" name="last_name" required>
                        </div>

                        <!-- Business Name -->
                        <div class="dsf-field-group dsf-field-12">
                            <label for="dsf-business-name">
                                <?php esc_html_e('Business Name', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-business-name" name="business_name" required>
                        </div>

                        <!-- Business Address -->
                        <div class="dsf-field-group dsf-field-12">
                            <label for="dsf-business-address">
                                <?php esc_html_e('Business Address', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-business-address" name="business_address" required>
                        </div>

                        <!-- City, State, Zipcode Row -->
                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-city">
                                <?php esc_html_e('City', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-city" name="city" required>
                        </div>

                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-state">
                                <?php esc_html_e('State', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-state" name="state" required>
                        </div>

                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-zipcode">
                                <?php esc_html_e('Zipcode', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-zipcode" name="zipcode" required>
                        </div>

                        <!-- Email & Phone Row -->
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-email">
                                <?php esc_html_e('Email', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="email" id="dsf-email" name="email" required>
                        </div>

                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-phone">
                                <?php esc_html_e('Phone', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="tel" id="dsf-phone" name="phone" required>
                        </div>

                        <!-- Entity Type (Optional) -->
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-entity-type">
                                <?php esc_html_e('Entity Type', 'dynamic-services-form'); ?>
                            </label>
                            <select id="dsf-entity-type" name="entity_type">
                                <option value="">-- <?php esc_html_e('Select', 'dynamic-services-form'); ?> --</option>
                                <option value="sole-proprietor"><?php esc_html_e('Sole Proprietor', 'dynamic-services-form'); ?></option>
                                <option value="partnership"><?php esc_html_e('Partnership', 'dynamic-services-form'); ?></option>
                                <option value="llc"><?php esc_html_e('LLC', 'dynamic-services-form'); ?></option>
                                <option value="corporation"><?php esc_html_e('Corporation', 'dynamic-services-form'); ?></option>
                            </select>
                        </div>

                        <!-- Additional Notes (Optional) -->
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-notes">
                                <?php esc_html_e('Additional Notes', 'dynamic-services-form'); ?>
                            </label>
                            <textarea id="dsf-notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Step Navigation -->
                    <div class="dsf-button-group">
                        <button type="button" class="dsf-btn dsf-btn-secondary dsf-btn-prev" data-step="2">
                            <?php esc_html_e('Back', 'dynamic-services-form'); ?>
                        </button>
                        <button type="submit" class="dsf-btn dsf-btn-primary dsf-btn-submit">
                            <?php esc_html_e('Submit', 'dynamic-services-form'); ?>
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                <div class="dsf-success-message" style="display:none;">
                    <div class="dsf-success-icon">✓</div>
                    <h2><?php esc_html_e('Thank You!', 'dynamic-services-form'); ?></h2>
                    <p><?php esc_html_e('Your form has been submitted successfully. We will be in touch shortly.', 'dynamic-services-form'); ?></p>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Render service dropdown options
     */
    private function render_service_dropdown_options() {
        $services = Service::get_all();
        
        if (empty($services)) {
            return;
        }

        $grouped = [];
        foreach ($services as $service) {
            if (!isset($grouped[$service['type']])) {
                $grouped[$service['type']] = [];
            }
            $grouped[$service['type']][] = $service;
        }

        foreach ($grouped as $type => $type_services) {
            echo '<optgroup label="' . esc_attr($type) . '">';
            foreach ($type_services as $service) {
                // Create a slug from the service name for URL matching
                $slug = sanitize_title($service['name']);
                echo '<option value="' . esc_attr($service['id']) . '" 
                    data-slug="' . esc_attr($slug) . '"
                    data-pricing-model="' . esc_attr($service['pricing_model']) . '"
                    data-has-packages="' . esc_attr($service['has_packages'] ? '1' : '0') . '">';
                echo esc_html($service['category'] . ' - ' . $service['name']);
                echo '</option>';
            }
            echo '</optgroup>';
        }
    }

    /**
     * Get pricing options for a service
     *
     * @param int $service_id Service ID
     * @return array
     */
    public static function get_pricing_options($service_id) {
        $service = new Service($service_id);
        if (!$service->get_id()) {
            return ['success' => false, 'message' => 'Service not found'];
        }

        $pricing_model = $service->get('pricing_model');
        
        ob_start();
        self::instance()->render_pricing_options($service);
        $html = ob_get_clean();

        return [
            'success' => true,
            'service_id' => $service_id,
            'pricing_model' => $pricing_model,
            'html' => $html,
        ];
    }

    /**
     * Render pricing options based on pricing model
     *
     * @param Service $service Service object
     */
    private function render_pricing_options(Service $service) {
        $pricing_model = $service->get('pricing_model');
        
        switch ($pricing_model) {
            case 'state_based':
                $this->render_state_based_pricing($service);
                break;
            case 'portal_based':
                $this->render_portal_based_pricing($service);
                break;
            case 'fixed_price':
                $this->render_fixed_price($service);
                break;
            case 'calculator':
                $this->render_calculator_pricing($service);
                break;
        }
    }

    /**
     * Render state-based pricing options
     *
     * @param Service $service
     */
    private function render_state_based_pricing(Service $service) {
        $locations = $service->get_locations();
        $has_packages = (bool) $service->get('has_packages');
        
        ?>
        <!-- Location/State Selection -->
        <div class="dsf-field-group">
            <label for="dsf-location">
                <?php esc_html_e('Location / State', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
            </label>
            <select id="dsf-location" name="location_id" required>
                <option value=""><?php esc_html_e('-- Select Location --', 'dynamic-services-form'); ?></option>
                <?php foreach ($locations as $location) : ?>
                    <option value="<?php echo esc_attr($location['location_id']); ?>"
                            data-location-name="<?php echo esc_attr($location['location_name']); ?>"
                            data-standard-price="<?php echo esc_attr($location['standard_price'] ?? ''); ?>"
                            data-premium-price="<?php echo esc_attr($location['premium_price'] ?? ''); ?>"
                            data-is-universal="<?php echo esc_attr($location['is_universal'] ?? 0); ?>">
                        <?php echo esc_html($location['location_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($has_packages) : ?>
            <!-- Package Selection -->
            <div class="dsf-field-group">
                <label><?php esc_html_e('Package / Plan', 'dynamic-services-form'); ?> <span class="dsf-required">*</span></label>
                <div class="dsf-packages-container">
                    <?php
                    $packages = $service->get_packages();
                    foreach ($packages as $package) :
                        ?>
                        <label class="dsf-package-item">
                            <input type="radio" name="package_id" 
                                   value="<?php echo esc_attr($package['package_type_id']); ?>"
                                   data-price="<?php echo esc_attr($package['price'] ?? ''); ?>"
                                   required>
                            <span class="dsf-package-name"><?php echo esc_html($package['package_type_name']); ?></span>
                            <?php if (!empty($package['price'])) : ?>
                                <span class="dsf-package-price">$<?php echo number_format((float) $package['price'], 2); ?></span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif;
    }

    /**
     * Render portal-based pricing options
     *
     * @param Service $service
     */
    private function render_portal_based_pricing(Service $service) {
        $portals = $service->get_portals();
        ?>
        <div class="dsf-field-group">
            <label><?php esc_html_e('Select Portals (Multiple Selection)', 'dynamic-services-form'); ?> <span class="dsf-required">*</span></label>
            <div class="dsf-portals-container">
                <?php foreach ($portals as $portal) : ?>
                    <label class="dsf-portal-item">
                        <input type="checkbox" name="portal_ids[]" 
                               value="<?php echo esc_attr($portal['id']); ?>"
                               data-price="<?php echo esc_attr($portal['price'] ?? ''); ?>"
                               data-portal-name="<?php echo esc_attr($portal['portal_name']); ?>">
                        <span class="dsf-portal-name"><?php echo esc_html($portal['portal_name']); ?></span>
                        <?php if (!empty($portal['price'])) : ?>
                            <span class="dsf-portal-price">$<?php echo number_format((float) $portal['price'], 2); ?></span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render fixed price
     *
     * @param Service $service
     */
    private function render_fixed_price(Service $service) {
        $fixed_price = $service->get('fixed_price') ?? 0;
        ?>
        <div class="dsf-field-group dsf-fixed-price-display">
            <p class="dsf-fixed-price-notice">
                <?php esc_html_e('This service has a fixed price.', 'dynamic-services-form'); ?>
            </p>
            <input type="hidden" name="fixed_price" value="<?php echo esc_attr($fixed_price); ?>">
        </div>
        <?php
    }

    /**
     * Render calculator pricing
     *
     * @param Service $service
     */
    private function render_calculator_pricing(Service $service) {
        ?>
        <div class="dsf-field-group">
            <label for="dsf-calculator-amount">
                <?php esc_html_e('Enter Amount', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
            </label>
            <input type="number" id="dsf-calculator-amount" name="calculator_amount" 
                   step="0.01" min="0" required 
                   placeholder="<?php esc_attr_e('e.g., 1000000', 'dynamic-services-form'); ?>">
        </div>

        <!-- Calculator Pricing Tiers Info -->
        <div class="dsf-calculator-tiers">
            <h4><?php esc_html_e('Pricing Tiers:', 'dynamic-services-form'); ?></h4>
            <ul>
                <li>$350,000 - $500,000: <strong>$900.00</strong></li>
                <li>$500,000 - $2,000,000: <strong>$1,500.00</strong></li>
                <li>$2,000,000+: <strong>1% of total amount</strong></li>
            </ul>
        </div>
        <?php
    }
}
