/**
 * Dynamic Services Form - Frontend JavaScript
 * 2-Step Workflow: Service + Pricing -> Contact Info
 * Note: Service selection and pricing are now handled inline in Form.php
 */

(function($) {
    'use strict';

    const DSFForm = {
        currentStep: 1,
        totalSteps: 2,

        /**
         * Initialize the form
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.$form = $('#dsf-form');
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

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
                    if (!$('#dsf-service-select').val()) {
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
                    if (!$('#dsf-location').val()) {
                        alert('Please select a location');
                        return false;
                    }
                    if ($('[name="package_id"]').length > 0 && !$('[name="package_id"]:checked').val()) {
                        alert('Please select a package');
                        return false;
                    }
                    break;

                case 'portal_based':
                    if (!$('#dsf-portal').val()) {
                        alert('Please select a portal');
                        return false;
                    }
                    break;

                case 'calculator':
                    if (!$('#dsf-calculator-amount').val() || $('#dsf-calculator-amount').val() <= 0) {
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
         * Get selected portal
         */
        getSelectedPortal: function() {
            return $('#dsf-portal').val() || null;
        },

        /**
         * Collect form data
         */
        collectFormData: function() {
            return {
                service_id: $('#dsf-service-select').val(),
                location_id: $('#dsf-location').val() || null,
                package_id: $('[name="package_id"]:checked').val() || null,
                portal_id: this.getSelectedPortal(),
                calculator_amount: $('#dsf-calculator-amount').val() || null,
                first_name: $('#dsf-first-name').val(),
                last_name: $('#dsf-last-name').val(),
                business_name: $('#dsf-business-name').val(),
                business_address: $('#dsf-business-address').val(),
                phone: $('#dsf-phone').val(),
                email: $('#dsf-email').val(),
                city: $('#dsf-city').val(),
                state: $('#dsf-state').val(),
                zipcode: $('#dsf-zipcode').val(),
                // entity_type removed
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
            if ($('#dsf-location').length && $('#dsf-location').is(':visible')) {
                return 'state_based';
            }
            if ($('#dsf-portal').length && $('#dsf-portal').is(':visible')) {
                return 'portal_based';
            }
            if ($('#dsf-calculator-amount').length && $('#dsf-calculator-amount').is(':visible')) {
                return 'calculator';
            }
            return 'fixed_price';
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFForm.init();
    });

})(jQuery);