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
            // Auto-update pricing model dependent fields
            $(document).on('change', '#pricing_model', function() {
                const model = $(this).val();
                const $hasPackages = $('#has_packages').closest('tr');

                if (model === 'state_based') {
                    $hasPackages.show();
                } else if (model === 'portal_based' || model === 'calculator' || model === 'fixed_price') {
                    $hasPackages.hide();
                    $('#has_packages').prop('checked', false);
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
