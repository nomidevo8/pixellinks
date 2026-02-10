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
        
        // Fetch all services (with full details) for this type + category from backend
        $services_data = [];
        if ($service_type && $service_category) {
            $services_data = Service::get_by_type_and_category($service_type, $service_category);
        }

        // Start output buffering
        ob_start();
        ?>
        <?php if ($service_type && $service_category) : ?>
        <!-- Embed services data in JavaScript -->
        <script type="text/javascript">
            var DSF_SERVICES_DATA = <?php echo wp_json_encode($services_data); ?>;
        </script>
        
        <!-- DEBUG: Backend data for type & category -->
        <?php if (defined('WP_DEBUG') && WP_DEBUG) :?>
         
            <div class="dsf-debug-backend-data" style="background:#f5f5f5; padding:1rem; margin-bottom:1rem; border:1px solid #ccc; font-family:monospace; font-size:12px;">
                <strong>Backend data for type="<?php
                echo esc_attr($service_type); ?>" &amp; category="<?php echo esc_attr($service_category); ?>":</strong>
                <?php if (empty($services_data)) : ?>
                <p style="margin:0.5rem 0 0;">No services found for this type and category.</p>
                <?php else : ?>
                <p style="margin:0.5rem 0 0;">Fetched <?php echo count($services_data); ?> service(s) with full details (pricing model, packages, locations, portals):</p>
                <pre style="margin:0.5rem 0 0; white-space:pre-wrap; word-break:break-all;"><?php echo esc_html(print_r($this->format_services_for_display($services_data), true)); ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?> 

        <?php endif; ?>
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
                    <div class="dsf-field-group dsf-field-service">
                        <label for="dsf-service-select">
                            <?php esc_html_e('Service', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                        </label>
                        <select id="dsf-service-select" name="service_id">
                            <option value=""><?php esc_html_e('-- Select a Service --', 'dynamic-services-form'); ?></option>
                            <?php $this->render_filtered_service_options($services_data); ?>
                        </select>
                    </div>

                    <!-- Dynamic Pricing Options Container -->
                    <div id="dsf-pricing-options-container" class="dsf-pricing-options-container" data-pricing-model="">
                        <!-- Content loaded client-side after service selection -->
                    </div>

                    <!-- Price Display -->
                    <div class="dsf-price-display">
                        <table class="dsf-price-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Total', 'dynamic-services-form'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                              <tr class="dsf-price-total-row">
                                    <td id="dsf-total-price-display">$0.00</td>
                                </tr>
                            </tbody>
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
                            <input type="text" id="dsf-first-name" name="first_name">
                        </div>
                        
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-last-name">
                                <?php esc_html_e('Last Name', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-last-name" name="last_name">
                        </div>

                        <!-- Business Name -->
                        <div class="dsf-field-group dsf-field-12">
                            <label for="dsf-business-name">
                                <?php esc_html_e('Business Name', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-business-name" name="business_name">
                        </div>

                        <!-- Business Address -->
                        <div class="dsf-field-group dsf-field-12">
                            <label for="dsf-business-address">
                                <?php esc_html_e('Business Address', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-business-address" name="business_address">
                        </div>

                        <!-- City, State, Zipcode Row -->
                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-city">
                                <?php esc_html_e('City', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-city" name="city">
                        </div>

                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-state">
                                <?php esc_html_e('State', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-state" name="state">
                        </div>

                        <div class="dsf-field-group dsf-field-4">
                            <label for="dsf-zipcode">
                                <?php esc_html_e('Zipcode', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="text" id="dsf-zipcode" name="zipcode">
                        </div>

                        <!-- Email & Phone Row -->
                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-email">
                                <?php esc_html_e('Email', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="email" id="dsf-email" name="email">
                        </div>

                        <div class="dsf-field-group dsf-field-6">
                            <label for="dsf-phone">
                                <?php esc_html_e('Phone', 'dynamic-services-form'); ?> <span class="dsf-required">*</span>
                            </label>
                            <input type="tel" id="dsf-phone" name="phone">
                        </div>

                        <!-- Entity Type removed -->

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

            
            </form>

                <!-- Success Message -->
            <div class="dsf-success-message" style="display:none;">
                <div class="dsf-success-icon">✓</div>
                <h2><?php esc_html_e('Thank You!', 'dynamic-services-form'); ?></h2>
                <p><?php esc_html_e('Your form has been submitted successfully. We will be in touch shortly.', 'dynamic-services-form'); ?></p>
            </div>
        </div>

        <!-- Styles for Searchable Select -->
        <style>
            /* Searchable Select Styling */
            .dsf-select-wrapper {
                position: relative;
                display: block;
                width: 100%;
            }

            .dsf-select-wrapper select {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%231a9b8e' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 14px;
            }

            .dsf-select-wrapper select:hover {
                border-color: #1a9b8e;
                box-shadow: 0 2px 8px rgba(26, 155, 142, 0.1);
            }

            .dsf-select-wrapper select:focus {
                outline: none;
                border-color: #1a9b8e;
                box-shadow: 0 0 0 3px rgba(26, 155, 142, 0.1);
            }

            /* Custom Dropdown - Inline style */
            .dsf-select-dropdown-wrapper {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 2px solid #1a9b8e;
                border-top: none;
                border-radius: 0 0 6px 6px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
                z-index: 9999;
                display: none;
                max-height: 400px;
                flex-direction: column;
                margin-top: -2px;
            }

            .dsf-select-dropdown-wrapper.active {
                display: flex;
            }

            .dsf-select-search {
                padding: 12px 15px;
                border-bottom: 1px solid #e0e0e0;
                background: #fafafa;
                flex-shrink: 0;
            }

            .dsf-select-search input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                font-size: 14px;
                font-family: inherit;
                transition: all 0.2s ease;
                box-sizing: border-box;
            }

            .dsf-select-search input:focus {
                outline: none;
                border-color: #1a9b8e;
                box-shadow: inset 0 0 0 2px rgba(26, 155, 142, 0.1);
            }

            .dsf-select-options {
                flex: 1;
                overflow-y: auto;
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .dsf-select-options::-webkit-scrollbar {
                width: 6px;
            }

            .dsf-select-options::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .dsf-select-options::-webkit-scrollbar-thumb {
                background: #1a9b8e;
                border-radius: 3px;
            }

            .dsf-select-options::-webkit-scrollbar-thumb:hover {
                background: #158076;
            }

            .dsf-select-option {
                padding: 11px 15px;
                cursor: pointer;
                transition: all 0.2s ease;
                border-bottom: 1px solid #f5f5f5;
                color: #2c3e50;
                font-size: 14px;
            }

            .dsf-select-option:hover {
                background: #f0f8f6;
                color: #1a9b8e;
                padding-left: 18px;
            }

            .dsf-select-option.selected {
                background: #e8f5f3;
                color: #1a9b8e;
                font-weight: 500;
                border-left: 3px solid #1a9b8e;
                padding-left: 15px;
            }

            .dsf-select-option.hidden {
                display: none;
            }

            .dsf-select-option:last-child {
                border-bottom: none;
            }

            .dsf-select-no-results {
                padding: 30px 15px;
                text-align: center;
                color: #7f8c8d;
                font-size: 14px;
                border-bottom: none;
            }
        </style>

        <!-- Inline JavaScript for Form Handling -->
        <script type="text/javascript">
        (function() {
            'use strict';

            // Wait for DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initForm);
            } else {
                initForm();
            }

            function initForm() {
                // Initialize searchable selects
                initSearchableSelects();

                // Handle service selection change
                var serviceSelect = document.getElementById('dsf-service-select');
                if (serviceSelect) {
                    serviceSelect.addEventListener('change', function() {
                        var serviceId = parseInt(this.value);
                        if (!serviceId) {
                            document.getElementById('dsf-pricing-options-container').innerHTML = '';
                            resetPriceDisplay();
                            return;
                        }

                        // Find service in embedded data
                        var service = null;
                        if (typeof DSF_SERVICES_DATA !== 'undefined') {
                            for (var i = 0; i < DSF_SERVICES_DATA.length; i++) {
                                if (DSF_SERVICES_DATA[i].id === serviceId) {
                                    service = DSF_SERVICES_DATA[i];
                                    break;
                                }
                            }
                        }
                        if (!service) {
                            console.error('Service not found in embedded data');
                            return;
                        }

                        // Render pricing options based on pricing model
                        renderPricingOptions(service);
                    });
                }

                // Auto-select service from URL parameters
                autoLoadServiceFromUrl();

                // Handle location selection (for state_based)
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'dsf-location') {
                        updatePriceDisplay();
                    }
                });

                // Handle package selection (for state_based with packages)
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.name === 'package_id') {
                        updatePriceDisplay();
                    }
                });

                // Handle portal selection (for portal_based)
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'dsf-portal') {
                        updatePriceDisplay();
                    }
                });

                // Handle calculator amount input
                document.addEventListener('input', function(e) {
                    if (e.target && e.target.id === 'dsf-calculator-amount') {
                        updatePriceDisplay();
                    }
                });
            }

            /**
             * Initialize searchable select functionality for all select elements
             */
            function initSearchableSelects() {
                var selects = document.querySelectorAll('select');
                
                selects.forEach(function(selectElement) {
                    // Skip if already initialized
                    if (selectElement.dataset.searchable === 'true') return;
                    
                    selectElement.dataset.searchable = 'true';
                    
                    // Wrap select in a container if not already wrapped
                    if (!selectElement.parentElement.classList.contains('dsf-select-wrapper')) {
                        var wrapper = document.createElement('div');
                        wrapper.className = 'dsf-select-wrapper';
                        selectElement.parentNode.insertBefore(wrapper, selectElement);
                        wrapper.appendChild(selectElement);
                    }
                    
                    // Handle select click to show dropdown
                    selectElement.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Close any open dropdowns
                        closeAllSelects();
                        
                        // Show this dropdown
                        showSelectDropdown(selectElement);
                    });
                });
            }

            /**
             * Close all open select dropdowns
             */
            function closeAllSelects() {
                var openDropdowns = document.querySelectorAll('.dsf-select-dropdown-wrapper.active');
                openDropdowns.forEach(function(dropdown) {
                    dropdown.classList.remove('active');
                    setTimeout(function() {
                        if (dropdown.parentElement) {
                            dropdown.parentElement.removeChild(dropdown);
                        }
                    }, 200);
                });
            }

            /**
             * Show searchable select dropdown
             */
            function showSelectDropdown(selectElement) {
                var wrapper = selectElement.parentElement;
                var options = selectElement.querySelectorAll('option');
                
                // Create dropdown wrapper
                var dropdownWrapper = document.createElement('div');
                dropdownWrapper.className = 'dsf-select-dropdown-wrapper';
                
                // Search input
                var searchDiv = document.createElement('div');
                searchDiv.className = 'dsf-select-search';
                var searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.placeholder = 'Search...';
                searchInput.className = 'dsf-select-search-input';
                searchDiv.appendChild(searchInput);
                dropdownWrapper.appendChild(searchDiv);
                
                // Options list
                var optionsList = document.createElement('ul');
                optionsList.className = 'dsf-select-options';
                
                options.forEach(function(option, index) {
                    if (!option.value && option.text) {
                        // Create label item for non-value options
                        var label = document.createElement('li');
                        label.className = 'dsf-select-option';
                        label.textContent = option.text;
                        label.style.fontWeight = '600';
                        label.style.cursor = 'default';
                        label.style.color = '#1a9b8e';
                        label.style.pointerEvents = 'none';
                        label.style.paddingTop = '8px';
                        label.style.paddingBottom = '8px';
                        optionsList.appendChild(label);
                    } else if (option.value) {
                        // Create selectable option
                        var li = document.createElement('li');
                        li.className = 'dsf-select-option';
                        li.textContent = option.text;
                        li.dataset.value = option.value;
                        li.dataset.index = index;
                        
                        if (selectElement.value === option.value) {
                            li.classList.add('selected');
                        }
                        
                        li.addEventListener('click', function(e) {
                            e.stopPropagation();
                            selectElement.value = option.value;
                            closeSelectDropdown(dropdownWrapper);
                            
                            // Trigger change event
                            if (selectElement.dispatchEvent) {
                                var changeEvent = new Event('change', { bubbles: true });
                                selectElement.dispatchEvent(changeEvent);
                            }
                        });
                        
                        optionsList.appendChild(li);
                    }
                });
                
                dropdownWrapper.appendChild(optionsList);
                
                // Add dropdown to wrapper
                wrapper.appendChild(dropdownWrapper);
                
                // Trigger active state with slight delay for animation
                setTimeout(function() {
                    dropdownWrapper.classList.add('active');
                }, 10);
                
                // Focus search input
                setTimeout(function() {
                    searchInput.focus();
                }, 50);
                
                // Handle search
                searchInput.addEventListener('input', function(e) {
                    var searchTerm = e.target.value.toLowerCase();
                    var items = optionsList.querySelectorAll('.dsf-select-option');
                    var visibleCount = 0;
                    
                    items.forEach(function(item) {
                        // Skip label items
                        if (item.style.pointerEvents === 'none') {
                            return;
                        }
                        
                        var text = item.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            item.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            item.classList.add('hidden');
                        }
                    });
                    
                    // Show no results message
                    var noResults = optionsList.querySelector('.dsf-select-no-results');
                    if (visibleCount === 0) {
                        if (!noResults) {
                            noResults = document.createElement('li');
                            noResults.className = 'dsf-select-no-results';
                            noResults.textContent = 'No results found';
                            optionsList.appendChild(noResults);
                        }
                    } else {
                        if (noResults) {
                            noResults.remove();
                        }
                    }
                });
                
                // Close on Escape key
                var escapeHandler = function(e) {
                    if (e.key === 'Escape') {
                        closeSelectDropdown(dropdownWrapper);
                        document.removeEventListener('keydown', escapeHandler);
                    }
                };
                document.addEventListener('keydown', escapeHandler);
                
                // Close when clicking outside
                var outsideHandler = function(e) {
                    if (!wrapper.contains(e.target)) {
                        closeSelectDropdown(dropdownWrapper);
                        document.removeEventListener('click', outsideHandler);
                    }
                };
                document.addEventListener('click', outsideHandler);
            }

            /**
             * Close select dropdown
             */
            function closeSelectDropdown(dropdownWrapper) {
                dropdownWrapper.classList.remove('active');
                setTimeout(function() {
                    if (dropdownWrapper.parentElement) {
                        dropdownWrapper.parentElement.removeChild(dropdownWrapper);
                    }
                }, 200);
            }

            /**
             * Render pricing options based on service pricing model
             */
            function renderPricingOptions(service) {
                var html = '';
                document
                .getElementById('dsf-pricing-options-container')
                .dataset.pricingModel = service.pricing_model;
                switch (service.pricing_model) {
                    case 'state_based':
                        html = renderStateBased(service);
                        break;
                    case 'portal_based':
                        html = renderPortalBased(service);
                        break;
                    case 'fixed_price':
                        html = renderFixedPrice(service);
                        break;
                    case 'calculator':
                        html = renderCalculator(service);
                        break;
                }

                document.getElementById('dsf-pricing-options-container').innerHTML = html;
                
                // Reinitialize searchable selects for newly created elements
                setTimeout(function() {
                    initSearchableSelects();
                }, 100);
                
                resetPriceDisplay();
            }

            /**
             * Render state-based pricing HTML
             */
            function renderStateBased(service) {
                var html = '<div class="dsf-field-group dsf-field-states">';
                html += '<label for="dsf-location">Location / State <span class="dsf-required">*</span></label>';
                html += '<select id="dsf-location" name="location_id">';
                html += '<option value="">-- Select Location --</option>';
                
                for (var i = 0; i < service.locations.length; i++) {
                    var location = service.locations[i];

                    // Decide display price (standard first)
                    var priceLabel = '';
                    if (location.standard_price && parseFloat(location.standard_price) > 0) {
                        priceLabel = ' — $' + parseFloat(location.standard_price).toFixed(2);
                    }

                    html += '<option value="' + escapeHtml(location.location_id) + '" ';
                    html += 'data-location-name="' + escapeHtml(location.location_name) + '" ';
                    html += 'data-standard-price="' + escapeHtml(location.standard_price || '') + '" ';
                    html += 'data-premium-price="' + escapeHtml(location.premium_price || '') + '" ';
                    html += 'data-is-universal="' + escapeHtml(location.is_universal || 0) + '">';
                    html += escapeHtml(location.location_name) + priceLabel;
                    html += '</option>';
                }
                
                html += '</select></div>';

                // Add packages if service has them
                if (service.has_packages && service.packages && service.packages.length > 0) {
                    // Sort packages: Standard first, Premium second
                    service.packages.sort(function(a, b) {
                        const order = { 'Standard': 1, 'Premium': 2 };
                        const aOrder = order[a.package_type_name] || 99;
                        const bOrder = order[b.package_type_name] || 99;
                        return aOrder - bOrder;
                    });

                    html += '<div class="dsf-field-group">';
                    html += '<label>Package / Plan <span class="dsf-required">*</span></label>';
                    html += '<div class="dsf-packages-container">';

                    for (var j = 0; j < service.packages.length; j++) {
                        var pkg = service.packages[j];
                        html += '<label class="dsf-package-item">';
                        html += '<input type="radio" name="package_id" ';
                        html += 'value="' + escapeHtml(pkg.package_type_id) + '" ';
                        html += 'data-price="' + escapeHtml(pkg.price || '') + '" ';
                        html += 'data-package-name="' + escapeHtml(pkg.package_type_name) + '" ';
                        html += 'required>';
                        html += '<span class="dsf-package-name">' + escapeHtml(pkg.package_type_name) + '</span>';

                        if (pkg.price) {
                            html += '<span class="dsf-package-price">$' + parseFloat(pkg.price).toFixed(2) + '</span>';
                        }

                        html += '</label>';
                    }

                    html += '</div></div>';
                }


                return html;
            }

            /**
             * Render portal-based pricing HTML
             */
            function renderPortalBased(service) {
                var html = '<div class="dsf-field-group dsf-field-portal">';
                html += '<label for="dsf-portal">Select Portal <span class="dsf-required">*</span></label>';
                html += '<select id="dsf-portal" name="portal_id">';
                html += '<option value="">-- Select Portal --</option>';
                
                if (service.portals && service.portals.length > 0) {
                    for (var i = 0; i < service.portals.length; i++) {
                        var portal = service.portals[i];
                        var price = portal.price ? ' - $' + parseFloat(portal.price).toFixed(2) : '';
                        html += '<option value="' + escapeHtml(portal.id) + '" ';
                        html += 'data-price="' + escapeHtml(portal.price || '') + '">';
                        html += escapeHtml(portal.portal_name) + price;
                        html += '</option>';
                    }
                }
                
                html += '</select></div>';
                return html;
            }

            /**
             * Render fixed price HTML
             */
            function renderFixedPrice(service) {
                var fixedPrice = service.fixed_price || 0;
                var html = '<div class="dsf-field-group dsf-fixed-price-display">';
                html += '<p class="dsf-fixed-price-notice">This service has a fixed price.</p>';
                html += '<input type="hidden" name="fixed_price" value="' + escapeHtml(fixedPrice) + '">';
                html += '</div>';
                
                // Update price display immediately
                setTimeout(function() {
                    document.getElementById('dsf-total-price-display').textContent = '$' + parseFloat(fixedPrice).toFixed(2);
                }, 100);
                
                return html;
            }

            /**
             * Render calculator pricing HTML
             */
            function renderCalculator(service) {
                var html = '<div class="dsf-field-group">';
                html += '<label for="dsf-calculator-amount">Enter Amount <span class="dsf-required">*</span></label>';
                html += '<input type="number" id="dsf-calculator-amount" name="calculator_amount" ';
                html += 'step="0.01" min="0" placeholder="e.g., 1000000">';
                html += '</div>';
                
                html += '<div class="dsf-calculator-tiers">';
                html += '<h4>Pricing Tiers:</h4>';
                html += '<ul>';
                html += '<li>$350,000 - $500,000: <strong>$900.00</strong></li>';
                html += '<li>$500,000 - $2,000,000: <strong>$1,500.00</strong></li>';
                html += '<li>$2,000,000+: <strong>1% of total amount</strong></li>';
                html += '</ul></div>';
                
                return html;
            }

            /**
             * Update price display based on selected options
             */
            function updatePriceDisplay() {
                var serviceSelect = document.getElementById('dsf-service-select');
                if (!serviceSelect || !serviceSelect.value) {
                    resetPriceDisplay();
                    return;
                }

                var serviceId = parseInt(serviceSelect.value);
                var service = null;
                
                if (typeof DSF_SERVICES_DATA !== 'undefined') {
                    for (var i = 0; i < DSF_SERVICES_DATA.length; i++) {
                        if (DSF_SERVICES_DATA[i].id === serviceId) {
                            service = DSF_SERVICES_DATA[i];
                            break;
                        }
                    }
                }

                if (!service) {
                    resetPriceDisplay();
                    return;
                }

                var totalPrice = 0;
                var priceLabel = service.name;

                switch (service.pricing_model) {
                    case 'state_based':
                        totalPrice = calculateStateBased(service);
                        break;
                    case 'portal_based':
                        totalPrice = calculatePortalBased();
                        break;
                    case 'fixed_price':
                        totalPrice = parseFloat(service.fixed_price || 0);
                        break;
                    case 'calculator':
                        totalPrice = calculateTiered();
                        break;
                }

                document.getElementById('dsf-total-price-display').textContent = '$' + totalPrice.toFixed(2);
            }

            /**
             * Calculate price for state-based pricing model
             */
            function calculateStateBased(service) {
                var total = 0;

                // Get location price
                var locationSelect = document.getElementById('dsf-location');
                if (locationSelect && locationSelect.value) {
                    var selectedOption = locationSelect.options[locationSelect.selectedIndex];
                    var standardPrice = parseFloat(selectedOption.getAttribute('data-standard-price') || 0);
                    total += standardPrice;
                }

                // Get package price if applicable
                if (service.has_packages) {
                    var packageRadio = document.querySelector('input[name="package_id"]:checked');
                    if (packageRadio) {
                        var packagePrice = parseFloat(packageRadio.getAttribute('data-price') || 0);
                        total += packagePrice;
                    }
                }

                return total;
            }

            /**
             * Calculate price for portal-based pricing model
             */
            function calculatePortalBased() {
                var total = 0;
                var portalSelect = document.getElementById('dsf-portal');
                
                if (portalSelect && portalSelect.value) {
                    var selectedOption = portalSelect.options[portalSelect.selectedIndex];
                    var price = parseFloat(selectedOption.getAttribute('data-price') || 0);
                    total += price;
                }

                return total;
            }

            /**
             * Calculate price for calculator pricing model
             */
            function calculateTiered() {
                var amountInput = document.getElementById('dsf-calculator-amount');
                if (!amountInput || !amountInput.value) {
                    return 0;
                }

                var amount = parseFloat(amountInput.value);
                
                if (amount >= 350000 && amount <= 500000) {
                    return 900.00;
                } else if (amount > 500000 && amount <= 2000000) {
                    return 1500.00;
                } else if (amount > 2000000) {
                    return amount * 0.01; // 1% of total amount
                }

                return 0;
            }

            /**
             * Reset price display to default
             */
            function resetPriceDisplay() {
                
                document.getElementById('dsf-total-price-display').textContent = '$0.00';
            }

            /**
             * Escape HTML to prevent XSS
             */
            function escapeHtml(text) {
                if (text === null || text === undefined) {
                    return '';
                }
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            /**
             * Get URL parameter by name
             */
            function getUrlParameter(param) {
                var urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }

            /**
             * Auto-load service from URL parameters
             */
            function autoLoadServiceFromUrl() {
                var serviceName = getUrlParameter('service_name');
                var packageName = getUrlParameter('pkg');
                
                if (!serviceName) {
                    return;
                }
                // Find and select service by name
                findAndSelectService(serviceName, packageName);
            }

            /**
             * Find service matching name and select it
             * Handles: kebab-case, snake_case, spaces, mixed case
             * Works with DB names stored as-is (e.g., "reseller-certificate", "director-service-address")
             */
            function findAndSelectService(serviceName, packageName) {
                var serviceSelect = document.getElementById('dsf-service-select');
                if (!serviceSelect) {
                    return;
                }
                
                var selectedServiceId = null;
                var options = serviceSelect.options;
                
                // Normalize the search name: lowercase only (keep hyphens/underscores)
                var normalizedSearchName = serviceName.toLowerCase().trim();
                
                // Also create a space-separated version for matching
                var spaceSearchName = normalizedSearchName.replace(/-/g, ' ').replace(/_/g, ' ');
                
                // Search through all options to find matching service
                for (var i = 0; i < options.length; i++) {
                    var option = options[i];
                    
                    // Skip empty options
                    if (!option.value) {
                        continue;
                    }
                    
                    // Get option text (format: "Service Name" or "Category - Service Name")
                    var optionText = option.text;
                    
                    // Extract service name if format is "Category - Service Name"
                    var servicePart = optionText;
                    if (optionText.indexOf(' - ') !== -1) {
                        var parts = optionText.split(' - ');
                        servicePart = parts[parts.length - 1].trim();
                    }
                    
                    // Normalize option text (lowercase)
                    var normalizedOptionText = servicePart.toLowerCase().trim();
                    
                    // Create space-separated version of option text
                    var spaceOptionText = normalizedOptionText.replace(/-/g, ' ').replace(/_/g, ' ');
                    
                    // Try multiple matching strategies (in order of priority):
                    
                    // 1. EXACT match (highest priority) - matches "reseller-certificate" with "reseller-certificate"
                    if (normalizedOptionText === normalizedSearchName) {
                        selectedServiceId = option.value;
                        break;
                    }
                    
                    // 2. EXACT match with spaces - matches "reseller certificate" with "reseller-certificate"
                    if (spaceOptionText === spaceSearchName) {
                        selectedServiceId = option.value;
                        break;
                    }
                    
                    // 3. Contains match (lower priority) - for partial matches
                    if (!selectedServiceId) {
                        if (normalizedOptionText.indexOf(normalizedSearchName) !== -1 || 
                            spaceOptionText.indexOf(spaceSearchName) !== -1) {
                            selectedServiceId = option.value;
                            // Don't break - keep looking for exact match
                        }
                    }
                }
                
                // If service found, select it and load pricing
                if (selectedServiceId) {
                    serviceSelect.value = selectedServiceId;
                    // Trigger change event to load pricing options
                    if (serviceSelect.dispatchEvent) {
                        var event = new Event('change', { bubbles: true });
                        serviceSelect.dispatchEvent(event);
                    } else {
                        // IE fallback
                        serviceSelect.fireEvent('onchange');
                    }
                    
                    // If package name is provided, auto-select it after pricing loads
                    if (packageName) {
                        setTimeout(function() {
                            autoSelectPackage(packageName);
                        }, 500);
                    }
                } else {
                    console.warn('Service not found for name: ' + serviceName);
                }
            }

            /**
             * Auto-select package by name after pricing loads
             * Handles: kebab-case, snake_case, spaces, mixed case, partial matches
             * Works with package names like "standard", "premium", "enterprise"
             */
            function autoSelectPackage(packageName) {
                var packageInputs = document.querySelectorAll('[name="package_id"]');
                
                // Normalize the search name (lowercase only, keep hyphens)
                var normalizedSearchName = packageName.toLowerCase().trim();
                
                // Also create space-separated version
                var spaceSearchName = normalizedSearchName.replace(/-/g, ' ').replace(/_/g, ' ');
                
                var foundExactMatch = false;
                var partialMatchInput = null;
                
                for (var i = 0; i < packageInputs.length; i++) {
                    var input = packageInputs[i];
                    var packageNameAttr = input.getAttribute('data-package-name');
                    
                    if (packageNameAttr) {
                        // Normalize the package name from data attribute
                        var normalizedPackageName = packageNameAttr.toLowerCase().trim();
                        var spacePackageName = normalizedPackageName.replace(/-/g, ' ').replace(/_/g, ' ');
                        
                        // Check for EXACT match first (highest priority)
                        if (normalizedPackageName === normalizedSearchName || 
                            spacePackageName === spaceSearchName) {
                            input.checked = true;
                            foundExactMatch = true;
                            
                            // Trigger change event to update price
                            if (input.dispatchEvent) {
                                var event = new Event('change', { bubbles: true });
                                input.dispatchEvent(event);
                            }
                            break;
                        }
                        
                        // Store partial match (only if no exact match found yet)
                        if (!foundExactMatch && !partialMatchInput) {
                            if (normalizedPackageName.indexOf(normalizedSearchName) !== -1 || 
                                spacePackageName.indexOf(spaceSearchName) !== -1 ||
                                normalizedSearchName.indexOf(normalizedPackageName) !== -1 ||
                                spaceSearchName.indexOf(spacePackageName) !== -1) {
                                partialMatchInput = input;
                            }
                        }
                    }
                }
                
                // If no exact match found, use partial match
                if (!foundExactMatch && partialMatchInput) {
                    partialMatchInput.checked = true;
                    
                    // Trigger change event to update price
                    if (partialMatchInput.dispatchEvent) {
                        var event = new Event('change', { bubbles: true });
                        partialMatchInput.dispatchEvent(event);
                    }
                }
            }
        })();
        </script>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Format full services data for debug/display (pricing model + related details)
     *
     * @param array $services_data Array from Service::get_by_type_and_category()
     * @return array Readable structure: per service shows pricing_model and packages/locations/portals as applicable
     */
    private function format_services_for_display($services_data) {
        $out = [];
        foreach ($services_data as $s) {
            $item = [
                'id' => $s['id'],
                'type' => $s['type'],
                'category' => $s['category'],
                'name' => $s['name'],
                'pricing_model' => $s['pricing_model'],
                'has_packages' => $s['has_packages'],
                'description' => $s['description'],
            ];
            if ($s['pricing_model'] === 'state_based') {
                $item['locations'] = $s['locations'];
                if (!empty($s['has_packages'])) {
                    $item['packages'] = $s['packages'];
                }
            } elseif ($s['pricing_model'] === 'portal_based') {
                $item['portals'] = $s['portals'];
            } elseif ($s['pricing_model'] === 'fixed_price') {
                $item['note'] = 'Fixed price (stored in code or options; no column in DB yet)';
            } elseif ($s['pricing_model'] === 'calculator') {
                $item['note'] = 'Calculator: tiered by amount in code ($350k–$500k, $500k–$2M, $2M+)';
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * Render service dropdown options from filtered data
     * 
     * @param array $services_data Pre-filtered services data
     */
    private function render_filtered_service_options($services_data) {
        if (empty($services_data)) {
            return;
        }

        foreach ($services_data as $service) {
            $slug = sanitize_title($service['name']);

            // Format label: kebab-case → Title Case
            $label = ucwords(str_replace('-', ' ', $service['name']));

            echo '<option value="' . esc_attr($service['id']) . '" ';
            echo 'data-slug="' . esc_attr($slug) . '" ';
            echo 'data-pricing-model="' . esc_attr($service['pricing_model']) . '" ';
            echo 'data-has-packages="' . esc_attr($service['has_packages'] ? '1' : '0') . '">';
            echo esc_html($label);
            echo '</option>';
        }
    }

}