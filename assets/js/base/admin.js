/**
 * Dynamic Services Form - Admin JavaScript
 */

(function($) {
    'use strict';

    const DSFAdmin = {
        /**
         * Initialize admin
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $(document).on('change', '#pricing_model', function() {
                const model = $(this).val();
                const $hasPackages = $('#has_packages').closest('tr');
                const $fixedPrice = $('#fixed-price-row');
                const $fixedPriceInput = $('#fixed_price');
                if (model === 'state_based') {
                    $hasPackages.show();
                    $fixedPrice.hide();
                    $fixedPriceInput.prop('required', false);
                } else if (model === 'portal_based' || model === 'calculator') {
                    $hasPackages.hide();
                    $fixedPrice.hide();
                    $('#has_packages').prop('checked', false);
                    $fixedPriceInput.prop('required', false);
                } else if (model === 'fixed_price') {
                    $hasPackages.hide();
                    $fixedPrice.show();
                    $('#has_packages').prop('checked', false);
                    $fixedPriceInput.prop('required', true);
                }
            });

            // Trigger on page load
            $('#pricing_model').trigger('change');
        },
    };

    // Initialize on document ready
    $(document).ready(function() {
        DSFAdmin.init();
    });

})(jQuery);
