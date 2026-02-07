/**
 * Dynamic Services Form - Frontend JavaScript
 */

(function($) {
    'use strict';

    const DSFForm = {
        currentStep: 1,
        totalSteps: 4,
        formData: {},
        selectedService: null,

        /**
         * Initialize the form
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.initializeService();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.$form = $('#dsf-form');
            this.$serviceSelection = $('[name="service_selection"]');
            this.$stateSelect = $('#dsf-state');
            this.$packageRadios = $('[name="package"]');
            this.$portalCheckboxes = $('[name="portals[]"]');
            this.$calculatorAmount = $('#dsf-calculator-amount');
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Service selection
            this.$serviceSelection.on('change', function() {
                self.onServiceSelected($(this));
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
            this.$stateSelect.on('change', function() {
                self.calculatePrice();
            });

            this.$packageRadios.on('change', function() {
                self.calculatePrice();
            });

            this.$portalCheckboxes.on('change', function() {
                self.calculatePrice();
            });

            this.$calculatorAmount.on('change', function() {
                self.calculatePrice();
            });
        },

        /**
         * Initialize service if URL parameters exist
         */
        initializeService: function() {
            const serviceType = $('[name="service_type"]').val();
            const serviceCategory = $('[name="service_category"]').val();
            const serviceName = $('[name="service_name"]').val();

            if (serviceType && serviceCategory && serviceName) {
                // Service is set via URL parameters, skip step 1
                this.loadPricingOptions();
            }
        },

        /**
         * Handle service selection
         */
        onServiceSelected: function($radio) {
            this.selectedService = {
                id: $radio.val(),
                type: $radio.data('type'),
                category: $radio.data('category'),
                name: $radio.data('name'),
            };

            // Update hidden fields
            $('[name="service_type"]').val(this.selectedService.type);
            $('[name="service_category"]').val(this.selectedService.category);
            $('[name="service_name"]').val(this.selectedService.name);
        },

        /**
         * Validate current step and move to next
         */
        validateAndMoveNext: function(step) {
            if (!this.validateStep(step)) {
                return;
            }

            if (step === 1) {
                this.loadPricingOptions();
            } else if (step === 3) {
                this.loadReviewSummary();
            }

            this.moveNext();
        },

        /**
         * Validate current step
         */
        validateStep: function(step) {
            let isValid = true;

            switch (step) {
                case 1: // Service selection
                    if ($('[name="service_selection"]:checked').length === 0) {
                        alert(dsfFrontend.validateMessages?.selectService || 'Please select a service');
                        isValid = false;
                    }
                    break;

                case 2: // Pricing options
                    if (!this.validatePricingStep()) {
                        isValid = false;
                    }
                    break;

                case 3: // Contact information
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
                    if (!this.$stateSelect.val()) {
                        alert('Please select a state');
                        return false;
                    }
                    if (this.hasPackages() && !$('[name="package"]:checked').val()) {
                        alert('Please select a package');
                        return false;
                    }
                    break;

                case 'portal_based':
                    if ($('[name="portals[]"]:checked').length === 0) {
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
            const businessName = $('#dsf-business-name').val().trim();
            const email = $('#dsf-email').val().trim();
            const phone = $('#dsf-phone').val().trim();

            if (!businessName) {
                alert('Please enter business name');
                return false;
            }

            if (!this.isValidEmail(email)) {
                alert('Please enter a valid email');
                return false;
            }

            if (!phone) {
                alert('Please enter phone number');
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
            }
        },

        /**
         * Load pricing options via AJAX
         */
        loadPricingOptions: function() {
            const self = this;
            const data = {
                action: 'dsf_get_pricing_options',
                nonce: dsfFrontend.nonce,
                service_type: $('[name="service_type"]').val(),
                service_category: $('[name="service_category"]').val(),
                service_name: $('[name="service_name"]').val(),
            };

            $.ajax({
                url: dsfFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        const options = response.data;
                        $('#dsf-pricing-options').html(options.html);
                        self.cacheElements();
                        self.bindEvents();
                    }
                },
            });
        },

        /**
         * Calculate and display total price
         */
        calculatePrice: function() {
            const self = this;
            const serviceType = $('[name="service_type"]').val();
            const serviceCategory = $('[name="service_category"]').val();
            const serviceName = $('[name="service_name"]').val();

            const data = {
                action: 'dsf_calculate_price',
                nonce: dsfFrontend.nonce,
                service_type: serviceType,
                service_category: serviceCategory,
                service_name: serviceName,
                state_id: this.$stateSelect.val() || 0,
                package_id: $('[name="package"]:checked').val() || 0,
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
                        self.updatePriceDisplay(totalPrice);
                    }
                },
            });
        },

        /**
         * Update price display
         */
        updatePriceDisplay: function(totalPrice) {
            const formattedPrice = '$' + parseFloat(totalPrice).toFixed(2);
            $('#dsf-total-price').text(formattedPrice);
        },

        /**
         * Get selected portals
         */
        getSelectedPortals: function() {
            return $.map($('[name="portals[]"]:checked'), function(el) {
                return $(el).val();
            });
        },

        /**
         * Load review summary
         */
        loadReviewSummary: function() {
            const self = this;
            const formData = this.collectFormData();

            const data = {
                action: 'dsf_get_review_summary',
                nonce: dsfFrontend.nonce,
                form_data: formData,
            };

            $.ajax({
                url: dsfFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#dsf-review-summary').html(response.data.html);
                        self.calculatePrice();
                    }
                },
            });
        },

        /**
         * Collect form data
         */
        collectFormData: function() {
            return {
                service_type: $('[name="service_type"]').val(),
                service_category: $('[name="service_category"]').val(),
                service_name: $('[name="service_name"]').val(),
                state: this.$stateSelect.val() || null,
                package: $('[name="package"]:checked').val() || null,
                portals: this.getSelectedPortals(),
                calculator_amount: this.$calculatorAmount.val() || null,
                business_name: $('#dsf-business-name').val(),
                email: $('#dsf-email').val(),
                phone: $('#dsf-phone').val(),
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

                        // Optional: Redirect or perform other actions
                        console.log('Form submitted successfully:', response.data);
                    } else {
                        alert(response.data.message || 'Error submitting form');
                    }
                },
                error: function() {
                    alert('Error submitting form');
                },
            });
        },

        /**
         * Get pricing model
         */
        getPricingModel: function() {
            // This would typically come from the loaded pricing options
            // For now, we'll infer it from what's visible
            if (this.$stateSelect.length) {
                return 'state_based';
            }
            if (this.$portalCheckboxes.length) {
                return 'portal_based';
            }
            if (this.$calculatorAmount.length) {
                return 'calculator';
            }
            return 'fixed_price';
        },

        /**
         * Check if service has packages
         */
        hasPackages: function() {
            return $('[name="package"]').length > 0;
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFForm.init();
    });

})(jQuery);
