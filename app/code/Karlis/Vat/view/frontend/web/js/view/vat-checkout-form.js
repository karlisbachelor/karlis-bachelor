/*global define*/
define([
    'Magento_Ui/js/form/form',
    'mage/translate',
    'ko',
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default'
], function (Component, $t, ko, $, url, quote, totalsDefaultProvider) {
    'use strict';

    const backendSubmitUrl = url.build('vat/index/validateandapply');
    const backendResetUrl = url.build('vat/index/reset');

    return Component.extend({

        isLoading: ko.observable(false),
        isValid: ko.observable(false),
        isInvalid: ko.observable(false),
        // errorValidationMessage: ko.observable(false),

        initialize: function () {
            this._super();
            // component initialization logic
            return this;
        },

        details: function () {
            return $t('Enter your VAT number below and click "Submit" to confirm it. ' +
                'If the VAT is correct, we will exclude taxes from your order.');
        },

        /**
         * Form submit handler
         *
         * This method can have any name.
         */
        onSubmit: function () {

            var formData = this.source.get('vatCheckoutForm');

            // verify that form data is valid
            if (formData.hasOwnProperty('sepa') && formData['sepa'] !== "") {
                // Call ajax
                this._ajax(backendSubmitUrl, {'sepa': formData['sepa']}, this._onSubmit, $t('Validating...'));
            }
        },

        /**
         * On submit callback
         *
         * @param data
         * @private
         */
        _onSubmit: function (data) {
            this.refreshTotals();

            if (data.sepaValid) {
                this.isValid(data.message);
            } else {
                this.isInvalid(data.message);
            }
        },

        /**
         * On reset callback.
         * @param data
         * @private
         */
        _onReset: function (data) {
            this.refreshTotals();

            if (data.message) {
                this.isValid(data.message);
            }
        },

        /**
         * Refresh totals
         */
        refreshTotals: function() {
            totalsDefaultProvider.estimateTotals(quote.shippingAddress());
        },

        /**
         * @param {String} url - ajax url
         * @param {Object} data - post data for ajax call
         * @param {Function} callback - callback method to execute after AJAX success
         * @param {String} loadingMsg
         */
        _ajax: function (url, data, callback, loadingMsg = $t('Loading...')) {
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,

                /** @inheritdoc */
                beforeSend: function () {
                    // Show loading message
                    this.isLoading(loadingMsg);
                    this.isValid(false);
                    this.isInvalid(false);
                },

                /** @inheritdoc */
                complete: function () {
                    // Show loading message
                    this.isLoading(false);
                }
            }).done(function (response) {
                var msg;

                if (response.success) {
                    callback.call(this, response.data);
                } else {
                    msg = response['error_message'];

                    if (msg) {
                        alert({
                            content: msg
                        });
                    }
                }
            }).fail(function (error) {
                console.log(JSON.stringify(error));
            });
        },

        /**
         * Reset form.
         */
        onReset: function () {
            this.reset();
            this._ajax(backendResetUrl, {}, this._onReset, $t('Resets...'));
        },
    });
});
