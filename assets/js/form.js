/**
 * Dynamic Services Form - Frontend JavaScript
 * 2-Step Workflow: Service + Pricing -> Contact Info
 */

(function($) {
    'use strict';

    const DSFForm = {
        currentStep: 1,
        totalSteps: 2,
        formData: {},
        selectedService: null,

        /**
         * Initialize the form
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.autoLoadServiceFromUrl();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.$form = $('#dsf-form');
            this.$serviceSelect = $('#dsf-service-select');
            this.$locationSelect = $('#dsf-location');
            this.$packageRadios = $('[name="package_id"]');
            this.$portalCheckboxes = $('[name="portal_ids[]"]');
            this.$calculatorAmount = $('#dsf-calculator-amount');
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Service selection from dropdown
            this.$serviceSelect.on('change', function() {
                const serviceId = $(this).val();
                if (serviceId) {
                    self.loadPricingOptions(serviceId);
                } else {
                    $('#dsf-pricing-options-container').empty();
                    self.updatePriceDisplay(0);
                }
            });

            // Next/Previous button clicks
            $(document).on('click', '.dsf-btn-next', function(e) {
                e.preventDefault();
                const step = $(this).data('step');
                self.validateAndMoveNext(step);
            });

            $(document).on('click', '.dsf-btn-prev', function(e) {
                e.preventDefault();
                const step = $(this).data('step');
                self.movePrevious(step);
            });

            // Form submission
            this.$form.on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });

            // Real-time price calculation
            $(document).on('change', '#dsf-location, [name="package_id"], [name="portal_ids[]"], #dsf-calculator-amount', function() {
                self.calculatePrice();
            });
        },

        /**
         * Load pricing options via AJAX
         */
        loadPricingOptions: function(serviceId) {
            const self = this;
            const data = {
                action: 'dsf_get_pricing_options',
                nonce: dsfFrontend.nonce,
                service_id: serviceId,
            };

            $.ajax({
                url: dsfFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#dsf-pricing-options-container').html(response.data.html);
                        self.cacheElements();
                        self.bindEvents();
                        self.calculatePrice();
                    } else {
                        alert('Error loading pricing options');
                    }
                },
                error: function() {
                    alert('Error loading pricing options');
                },
            });
        },

        /**
         * Validate current step and move to next
         */
        validateAndMoveNext: function(step) {
            if (!this.validateStep(step)) {
                return;
            }
            this.moveNext();
        },

        /**
         * Validate current step
         */
        validateStep: function(step) {
            let isValid = true;

            switch (step) {
                case 1: // Service + Pricing
                    if (!this.$serviceSelect.val()) {
                        alert(dsfFrontend.validateMessages?.selectService || 'Please select a service');
                        isValid = false;
                    } else if (!this.validatePricingStep()) {
                        isValid = false;
                    }
                    break;

                case 2: // Contact information
                    if (!this.validateContactInfo()) {
                        isValid = false;
                    }
                    break;
            }

            return isValid;
        },

        /**
         * Validate pricing step based on pricing model
         */
        validatePricingStep: function() {
            const pricingModel = this.getPricingModel();

            switch (pricingModel) {
                case 'state_based':
                    if (!this.$locationSelect.val()) {
                        alert('Please select a location');
                        return false;
                    }
                    if (this.hasPackages() && !$('[name="package_id"]:checked').val()) {
                        alert('Please select a package');
                        return false;
                    }
                    break;

                case 'portal_based':
                    if ($('[name="portal_ids[]"]:checked').length === 0) {
                        alert('Please select at least one portal');
                        return false;
                    }
                    break;

                case 'calculator':
                    if (!this.$calculatorAmount.val() || this.$calculatorAmount.val() <= 0) {
                        alert('Please enter a valid amount');
                        return false;
                    }
                    break;
            }

            return true;
        },

        /**
         * Validate contact information
         */
        validateContactInfo: function() {
            const requiredFields = ['first_name', 'last_name', 'business_name', 'business_address', 'city', 'state', 'zipcode', 'email', 'phone'];
            
            for (let field of requiredFields) {
                const value = $('#dsf-' + field.replace(/_/g, '-')).val().trim();
                if (!value) {
                    alert('Please fill in all required fields');
                    return false;
                }
            }

            if (!this.isValidEmail($('#dsf-email').val())) {
                alert('Please enter a valid email');
                return false;
            }

            return true;
        },

        /**
         * Check if email is valid
         */
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Move to next step
         */
        moveNext: function() {
            if (this.currentStep < this.totalSteps) {
                $('.dsf-step').hide();
                this.currentStep++;
                $('.dsf-step-' + this.currentStep).show();
                this.updateProgressBar();
            }
        },

        /**
         * Move to previous step
         */
        movePrevious: function(step) {
            if (this.currentStep > 1) {
                $('.dsf-step').hide();
                this.currentStep--;
                $('.dsf-step-' + this.currentStep).show();
                this.updateProgressBar();
            }
        },

        /**
         * Update progress bar
         */
        updateProgressBar: function() {
            $('.dsf-progress-item').removeClass('dsf-active');
            for (let i = 1; i <= this.currentStep; i++) {
                $('.dsf-progress-item').eq(i - 1).addClass('dsf-active');
            }
        },

        /**
         * Calculate and display total price
         */
        calculatePrice: function() {
            const self = this;
            const serviceId = this.$serviceSelect.val();

            if (!serviceId) {
                this.updatePriceDisplay(0);
                return;
            }

            const data = {
                action: 'dsf_calculate_price',
                nonce: dsfFrontend.nonce,
                service_id: serviceId,
                location_id: this.$locationSelect.val() || 0,
                package_id: $('[name="package_id"]:checked').val() || 0,
                portal_ids: this.getSelectedPortals(),
                calculator_amount: this.$calculatorAmount.val() || 0,
            };

            $.ajax({
                url: dsfFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        const totalPrice = response.data.total_price;
                        const priceLabel = response.data.price_label || 'Service Fee';
                        self.updatePriceDisplay(totalPrice, priceLabel);
                    }
                },
            });
        },

        /**
         * Update price display in the price table
         */
        updatePriceDisplay: function(totalPrice, label = 'Service Fee') {
            const formattedPrice = '$' + parseFloat(totalPrice).toFixed(2);
            $('#dsf-price-label').text(label);
            $('#dsf-price-value').text(formattedPrice);
            $('#dsf-total-price-display').text(formattedPrice);
        },

        /**
         * Get selected portals
         */
        getSelectedPortals: function() {
            return $.map($('[name="portal_ids[]"]:checked'), function(el) {
                return $(el).val();
            });
        },

        /**
         * Collect form data
         */
        collectFormData: function() {
            return {
                service_id: this.$serviceSelect.val(),
                location_id: this.$locationSelect.val() || null,
                package_id: $('[name="package_id"]:checked').val() || null,
                portal_ids: this.getSelectedPortals(),
                calculator_amount: this.$calculatorAmount.val() || null,
                first_name: $('#dsf-first-name').val(),
                last_name: $('#dsf-last-name').val(),
                business_name: $('#dsf-business-name').val(),
                business_address: $('#dsf-business-address').val(),
                phone: $('#dsf-phone').val(),
                email: $('#dsf-email').val(),
                city: $('#dsf-city').val(),
                state: $('#dsf-state').val(),
                zipcode: $('#dsf-zipcode').val(),
                entity_type: $('#dsf-entity-type').val(),
                notes: $('#dsf-notes').val(),
            };
        },

        /**
         * Submit form
         */
        submitForm: function() {
            const self = this;
            const formData = this.collectFormData();

            const ajaxData = {
                action: 'dsf_submit_form',
                nonce: dsfFrontend.nonce,
                ...formData,
            };

            $.ajax({
                url: dsfFrontend.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function(response) {
                    if (response.success) {
                        // Hide form and show success message
                        self.$form.hide();
                        $('.dsf-success-message').show();
                        console.log('Form submitted successfully:', response.data);
                    } else {
                        alert(response.data.message || 'Error submitting form');
                    }
                },
                error: function() {
                    alert('Error submitting form. Please try again.');
                },
            });
        },

        /**
         * Get pricing model from visible options
         */
        getPricingModel: function() {
            if (this.$locationSelect.length && this.$locationSelect.is(':visible')) {
                return 'state_based';
            }
            if (this.$portalCheckboxes.length && this.$portalCheckboxes.is(':visible')) {
                return 'portal_based';
            }
            if (this.$calculatorAmount.length && this.$calculatorAmount.is(':visible')) {
                return 'calculator';
            }
            return 'fixed_price';
        },

        /**
         * Check if service has packages
         */
        hasPackages: function() {
            return $('[name="package_id"]').length > 0;
        },

        /**
         * Get URL parameter by name
         */
        getUrlParameter: function(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        },

        /**
         * Auto-load service from URL parameters
         * Handles: service_type, service_category, service_name, pkg
         */
        autoLoadServiceFromUrl: function() {
            const self = this;
            
            // Get parameters from URL
            const serviceType = this.getUrlParameter('service_type');
            const serviceCategory = this.getUrlParameter('service_category');
            const serviceName = this.getUrlParameter('service_name');
            const packageName = this.getUrlParameter('pkg');
            
            // Type and Category are required to find service
            if (!serviceType || !serviceCategory) {
                return;
            }
            
            // Find service by type, category, and optionally name
            this.findAndSelectService(serviceType, serviceCategory, serviceName, packageName);
        },

        /**
         * Find service matching type/category/name and select it
         */
        findAndSelectService: function(type, category, name, packageName) {
            const self = this;
            
            // Get all options and find matching service
            let selectedServiceId = null;
            
            this.$serviceSelect.find('option').each(function() {
                const $option = $(this);
                const optionText = $option.text();
                
                // Check if option text matches the category pattern
                // Text format is "Category - Name"
                if (optionText.includes(category)) {
                    // If name is not provided, select first match of type/category
                    if (!name) {
                        selectedServiceId = $option.val();
                        return false; // Break loop
                    }
                    
                    // If name is provided, match exact name in the option
                    if (optionText.includes(name) || optionText.toLowerCase().includes(name.toLowerCase())) {
                        selectedServiceId = $option.val();
                        return false; // Break loop
                    }
                }
            });
            
            // If service found, select it and load pricing
            if (selectedServiceId) {
                this.$serviceSelect.val(selectedServiceId);
                
                // Load pricing options via AJAX
                setTimeout(function() {
                    self.loadPricingOptions(selectedServiceId);
                    
                    // If package name is provided, auto-select it after pricing loads
                    if (packageName) {
                        setTimeout(function() {
                            self.autoSelectPackage(packageName);
                        }, 500);
                    }
                }, 100);
            }
        },

        /**
         * Auto-select package by name after pricing loads
         */
        autoSelectPackage: function(packageName) {
            // Find and select the package radio/checkbox matching the name
            $('[name="package_id"]').each(function() {
                const $input = $(this);
                const $label = $input.closest('.dsf-package-card, .dsf-package-option').find('label, .dsf-package-name, .dsf-label-text');
                const labelText = $label.text().toLowerCase();
                
                if (labelText.includes(packageName.toLowerCase())) {
                    $input.prop('checked', true).trigger('change');
                    return false; // Break loop
                }
            });
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFForm.init();
    });

})(jQuery);

