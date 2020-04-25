/*global define*/
define([
    'Magento_Ui/js/form/form',
    'mage/translate',
    'ko',
    'jquery',
    'mage/url'
], function (Component, $t, ko, $, url) {
    'use strict';

    const backendUrl = url.build('vat/index/validateandapply');

    return Component.extend({

        isLoading: ko.observable(false),
        apiResult: ko.observable(false),
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
                this._ajax(backendUrl, {'sepa': formData['sepa']}, this._onSubmit);
            }
        },


        _onSubmit: function (response) {
            // Console log response
            console.log('_onSubmitComplete');
            console.log(response);

            // Parse api respons to the json format
            var resultJson = JSON.parse(response.data['api_response']);

            // Print API response
            this.apiResult(JSON.stringify(resultJson, undefined, 4));

            // Show notice
            if (resultJson.success === true
                && resultJson.result && resultJson.result.records
                && resultJson.result.records.length === 1) {
                this.isValid(true);
            } else {
                this.isInvalid(true);
            }

        },


        /**
         * @param {String} url - ajax url
         * @param {Object} data - post data for ajax call
         * @param {Function} callback - callback method to execute after AJAX success
         */
        _ajax: function (url, data, callback) {
            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,

                /** @inheritdoc */
                beforeSend: function () {
                    // Show loading message
                    this.isLoading(true);
                    this.apiResult(false);
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
                    callback.call(this, response);
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
         * TODO: apply tax back
         */
        onReset: function () {
            this.reset();

            this.isLoading(false);
            this.apiResult(false);
            this.isValid(false);
            this.isInvalid(false);
        },
    });
});
