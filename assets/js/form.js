/**
 * Dynamic Services Form - Frontend JavaScript
 * 2-Step Workflow: Service + Pricing -> Contact Info
 */

(function($) {
    'use strict';

    const DSFForm = {
        currentStep: 1,
        totalSteps: 2,
        isSubmitting: false,

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
                // Always validate step 2 on submit
                const step = parseInt(
                    self.$form.find('.dsf-step:visible').data('step'),
                    10
                );

                if (!self.validateStep(step)) {
                    return; 
                }

                self.submitForm();
            });
            // Clear errors on input
            $(document).on('input change', '.dsf-step-2 input, .dsf-step-2 textarea', function() {
                const fieldId = $(this).attr('id').replace('dsf-', '');
                DSFForm.clearFieldError(fieldId);
            });
        },

        /**
         * Show inline error below a field
         */
        showFieldError: function(fieldId, message) {
            const $input = $('#dsf-' + fieldId.replace(/_/g, '-'));
            const $group = $input.closest('.dsf-field-group');

            // Remove existing error
            $group.find('.dsf-error-message').remove();

            $group.addClass('dsf-has-error');
            $input.addClass('dsf-input-error');

            $group.append(
                '<div class="dsf-error-message">' + message + '</div>'
            );
        },

        /**
         * Clear error from a field
         */
        clearFieldError: function(fieldId) {
            const $input = $('#dsf-' + fieldId.replace(/_/g, '-'));
            const $group = $input.closest('.dsf-field-group');

            $group.removeClass('dsf-has-error');
            $input.removeClass('dsf-input-error');
            $group.find('.dsf-error-message').remove();
        },


        /**
         * Validate current step and move to next
         */
        validateAndMoveNext: function(step) {
            console.log('step', step);
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
                case 1: 
                    if (!$('#dsf-service-select').val()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Service Required',
                            text: dsfFrontend.validateMessages?.selectService || 'Please select a service',
                            confirmButtonColor: '#3498db',
                            confirmButtonText: 'OK'
                        });
                        isValid = false;
                    } else if (!this.validatePricingStep()) {
                        isValid = false;
                    }
                    break;

                case 2: 
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
            switch (pricingModel) {
                case 'state_based':
                    if (!$('#dsf-location').val()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Location Required',
                            text: 'Please select a location',
                            confirmButtonColor: '#3498db',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    if ($('[name="package_id"]').length > 0 && !$('[name="package_id"]:checked').val()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Package Required',
                            text: 'Please select a package',
                            confirmButtonColor: '#3498db',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    break;

                case 'portal_based':
                    if (!$('#dsf-portal').val()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Portal Required',
                            text: 'Please select a portal',
                            confirmButtonColor: '#3498db',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    break;

                case 'calculator':
                    const amount = parseFloat($('#dsf-calculator-amount').val());

                    if (isNaN(amount) || amount <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Valid Amount Required',
                            text: 'Please enter a valid amount',
                            confirmButtonColor: '#3498db'
                        });
                        return false;
                    }

                    if (amount < 350000) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Minimum Amount Required',
                            text: 'The minimum allowed amount is $350,000.',
                            confirmButtonColor: '#e74c3c'
                        });
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
            let isValid = true;

            const requiredFields = [
                'first_name',
                'last_name',
                'business_name',
                'business_address',
                'city',
                'state',
                'zipcode',
                'email',
                'phone'
            ];

            // Custom messages for each field
            const fieldMessages = {
                first_name: 'Please enter your First Name',
                last_name: 'Please enter your Last Name',
                business_name: 'Please enter your Business Name',
                business_address: 'Please enter your Business Address',
                city: 'Please enter your City',
                state: 'Please enter your State',
                zipcode: 'Please enter your Zipcode',
                email: 'Please enter a valid Email Address',
                phone: 'Please enter your Phone Number'
            };

            // Clear old errors first
            requiredFields.forEach(field => this.clearFieldError(field));

            // Required fields
            for (let field of requiredFields) {
                const value = $('#dsf-' + field.replace(/_/g, '-')).val().trim();

                if (!value) {
                    const message = fieldMessages[field] || 'This field is required';
                    this.showFieldError(field, message);
                    isValid = false;
                }
            }

            // Email validation (extra check)
            const emailVal = $('#dsf-email').val().trim();
            if (emailVal && !this.isValidEmail(emailVal)) {
                this.showFieldError('email', 'Please enter a valid email address');
                isValid = false;
            }

            if (!isValid) {
                const $firstError = $('.dsf-step:visible .dsf-has-error').first();

                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 120
                    }, 400);

                    $firstError.find('input, textarea').first().focus();
                }
            }

            return isValid;
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
                    break;
            }

            return baseData;
        },

        /**
         * Submit form
         */
        submitForm: function() {
            const self = this;
            
            // Prevent double submission
            if (this.isSubmitting) {
                return;
            }
            
            this.isSubmitting = true;
            const formData = this.collectFormData();
            const ajaxData = {
                action: 'dsf_submit_form',
                nonce: dsfFrontend.nonce,
                ...formData,
            };
            
            // Show loading dialog
            Swal.fire({
                title: 'Submitting Your Request',
                html: '<div class="swal-loader"></div><p style="margin-top: 20px; color: #666;">Please wait while we process your submission...</p>',
                icon: undefined,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: (modal) => {
                    $.ajax({
                        url: dsfFrontend.ajaxUrl,
                        type: 'POST',
                        data: ajaxData,
                        success: function(response) {
                            self.isSubmitting = false;
                            if (response.success) {
                                self.$form.hide();
                                $('.dsf-success-message').show();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: '<p style="color: #555; line-height: 1.6;">Your service request has been submitted successfully!</p>' +
                                          '<p style="font-size: 14px; margin-top: 15px; color: #888;"><strong>Submission ID:</strong> #' + response.data.submission_id + '</p>' +
                                          '<p style="color: #888; font-size: 13px;">Our team will review your request and contact you soon.</p>',
                                    confirmButtonColor: '#27ae60',
                                    confirmButtonText: 'Close',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload(); 
                                    }
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Submission Failed',
                                    html: '<p style="color: #555;">' + (response.data.message || 'An error occurred while submitting your form.') + '</p>',
                                    confirmButtonColor: '#e74c3c',
                                    confirmButtonText: 'Try Again'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            self.isSubmitting = false;
                            console.error('AJAX Error:', error);
                            
                            // Show connection error
                            Swal.fire({
                                icon: 'error',
                                title: 'Connection Error',
                                html: '<p style="color: #555;">Failed to submit your form. Please check your internet connection and try again.</p>',
                                confirmButtonColor: '#e74c3c',
                                confirmButtonText: 'Try Again'
                            });
                        }
                    });
                }
            });
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFForm.init();
    });

})(jQuery);