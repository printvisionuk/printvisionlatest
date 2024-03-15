/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/cookies'
    ],
    function (
        $,
        Component,
        setPaymentInformationAction,
        fullScreenLoadern,
        checkoutData,
        quote,
        customer,
        urlBuilder,
        placeOrderService,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magenest_Barclaycard/payment/barclaycard-payments-method',
                redirectAfterPlaceOrder: false
            },

            validate: function () {
                return true;
            },

            isActive: function () {
                return true;
            },

            afterPlaceOrder: function () {
                var serviceUrl, payload;
                var self = this;
                var formData;
                var payUrl;
                fullScreenLoader.startLoader();
                this.isPlaceOrderActionAllowed(false);

                $.when(ajax1()).done(function () {
                    var form = $('<form id="form-test" action="' + payUrl + '" method="post">' +
                        '</form>');
                    $('body').append(form);
                    for (var key in formData) {
                        if (formData.hasOwnProperty(key)) {
                            console.log(key + " "+ formData[key]);
                            $('<input>').attr({
                                type: 'hidden',
                                name: key,
                                value: formData[key]
                            }).appendTo('#form-test');
                        }
                    }
                    form.submit();
                }).fail(function () {
                    self.isPlaceOrderActionAllowed(true);
                    fullScreenLoader.stopLoader(true);
                });

                function ajax1()
                {
                    return $.ajax({
                        type: 'POST',
                        url: window.checkoutConfig.payment.barclaycard_direct.genHashUrl,
                        dataType: "json",
                        data: {
                            guest_email: quote.guestEmail,
                            quoteId : quote.getQuoteId(),
                            form_key: $.cookie('form_key')
                        },
                        success: function (response) {
                            if (response.success) {
                                formData = response.data;
                                payUrl = response.payUrl+"?";
                            } else {
                                self.messageContainer.addErrorMessage({
                                    message: response.message
                                });
                                fullScreenLoader.stopLoader();
                                self.isPlaceOrderActionAllowed(true);
                                location.reload();
                            }
                        },
                        error: function (response) {
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                            self.messageContainer.addErrorMessage({
                                message: "Error, please try again"
                            });
                            location.reload();
                        }
                    });
                }
            }

        });
    }
);
