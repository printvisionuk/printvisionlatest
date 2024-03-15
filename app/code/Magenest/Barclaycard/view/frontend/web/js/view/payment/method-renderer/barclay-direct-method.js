/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/cookies'
    ],
    function ($, Component,fullScreenLoader, redirectOnSuccessAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magenest_Barclaycard/payment/barclay-direct-method',
                redirectAfterPlaceOrder: false
            },

            getCode: function () {
                return 'barclaycard_direct';
            },

            isActive: function () {
                return true;
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            },

            validate: function () {
                return this.validateForm($('#'+this.getCode()+'-form'));
            },

            afterPlaceOrder: function () {
                var self = this;
                var formData;
                fullScreenLoader.startLoader();
                this.isPlaceOrderActionAllowed(false);
                $.ajax({
                    type: 'POST',
                    url: window.checkoutConfig.payment.barclaycard_direct.genDirectUrl,
                    dataType: "json",
                    data: {
                        form_key: $.cookie('form_key')
                    },
                    success: function (response) {
                        if (response.success) {
                            if (response.has3ds) {
                                formData = response.form;
                                $('#threedform').append(formData);
                                $('form[name=downloadform3D]').submit();
                                fullScreenLoader.stopLoader();
                            } else {
                                self.redirectAfterPlaceOrder = true;
                                self.messageContainer.addSuccessMessage({
                                    message: "Payment success!"
                                });
                                redirectOnSuccessAction.execute();
                            }
                        } else {
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                            fullScreenLoader.stopLoader();
                        }
                    },
                    error: function (response) {
                        fullScreenLoader.stopLoader(true);
                        self.isPlaceOrderActionAllowed(true);
                        self.messageContainer.addErrorMessage({
                            message: "Error, please try again"
                        });
                    }
                });
            }

        });
    }
);