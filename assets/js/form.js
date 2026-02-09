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
            const pricingModel =
                    $('#dsf-pricing-options-container').attr('data-pricing-model') || null;
            console.log('pricingModel' , pricingModel)
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
         * Get selected location/state name
         */
        getSelectedLocationName: function() {
            return $('#dsf-location option:selected').text() || null;
        },

        /**
         * Get selected package name
         */
        getSelectedPackageName: function() {
            const packageId = $('[name="package_id"]:checked').val();
            if (!packageId) return null;
            return $('[name="package_id"]:checked').closest('label').text().trim() || null;
        },

        /**
         * Get selected portal name
         */
        getSelectedPortalName: function() {
            return $('#dsf-portal option:selected').text() || null;
        },

        /**
         * Get selected portal ID
         */
        getSelectedPortal: function() {
            return $('#dsf-portal').val() || null;
        },

        /**
         * Formute and human readable
         */
        formatHumanReadable: function (value) {
            if (!value) return null;

            return value
                .replace(/[-_]/g, ' ')          
                .replace(/\b\w/g, c => c.toUpperCase()); 
        },

        /**
         * Collect form data based on pricing model
         */
        collectFormData: function() {
            const pricingModel =
            $('#dsf-pricing-options-container').attr('data-pricing-model') || null;
            const baseData = {
                service_id: $('#dsf-service-select').val(),
                service_name: $('#dsf-service-select option:selected').text(),
                service_type: this.formatHumanReadable(
                    $('input[name="service_type"]').val()
                ),
                service_category: this.formatHumanReadable(
                    $('input[name="service_category"]').val()
                ),
                first_name: $('#dsf-first-name').val(),
                last_name: $('#dsf-last-name').val(),
                business_name: $('#dsf-business-name').val(),
                business_address: $('#dsf-business-address').val(),
                phone: $('#dsf-phone').val(),
                email: $('#dsf-email').val(),
                city: $('#dsf-city').val(),
                state: $('#dsf-state').val(),
                zipcode: $('#dsf-zipcode').val(),
                notes: $('#dsf-notes').val(),
                pricing_model: pricingModel,
            };

            // Add pricing-model-specific data
            switch (pricingModel) {
                case 'state_based':
                    baseData.location_id = $('#dsf-location').val() || null;
                    baseData.location_name = this.getSelectedLocationName();
                    if ($('[name="package_id"]').length > 0 && $('[name="package_id"]:checked').val()) {
                        baseData.package_id = $('[name="package_id"]:checked').val();
                        baseData.package_name = this.getSelectedPackageName();
                    }
                    break;

                case 'portal_based':
                    baseData.portal_id = this.getSelectedPortal();
                    baseData.portal_name = this.getSelectedPortalName();
                    break;

                case 'calculator':
                    baseData.user_input_amount = $('#dsf-calculator-amount').val() || null;
                    break;

                case 'fixed_price':
                    // Fixed price doesn't need additional data
                    break;
            }

            return baseData;
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
                        // self.$form.hide();
                        // $('.dsf-success-message').show();
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
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFForm.init();
    });

})(jQuery);