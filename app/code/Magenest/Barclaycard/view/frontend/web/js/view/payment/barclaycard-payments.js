/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'magenest_barclaycard',
                component: 'Magenest_Barclaycard/js/view/payment/method-renderer/barclaycard-payments-method'
            },
            {
                type: 'barclaycard_direct',
                component: 'Magenest_Barclaycard/js/view/payment/method-renderer/barclay-direct-method'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
