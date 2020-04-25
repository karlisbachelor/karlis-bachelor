/*global define*/
define([
    'Magento_Ui/js/form/form',
    'mage/translate'
], function (Component, $t) {
    'use strict';
    return Component.extend({

        initialize: function () {
            this._super();
            // component initialization logic
            return this;
        },

        details: function () {
            return $t('Enter your VAT number below and click "Submit" to confirm it. If the VAT is correct, we will exclude taxes from your order.');
        },

        /**
         * Form submit handler
         *
         * This method can have any name.
         */
        onSubmit: function () {
            // trigger form validation
            this.source.set('params.invalid', false);
            this.source.trigger('vatCheckoutForm.data.validate');

            // verify that form data is valid
            if (!this.source.get('params.invalid')) {
                // data is retrieved from data provider by value of the vatScope property
                var formData = this.source.get('vatCheckoutForm');
                // do something with form data
                console.dir(formData);
            }
        }
    });
});
