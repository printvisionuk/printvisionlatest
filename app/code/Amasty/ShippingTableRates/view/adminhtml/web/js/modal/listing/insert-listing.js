define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/insert-listing',
    'Amasty_ShippingTableRates/js/model/sources-converter'
], function ($, _, registry, insertListing, converter) {
    'use strict';

    return insertListing.extend({
        setExternalValue: function (newValue) {
            this._super(newValue);

            registry.get(this.targetField, function (component) {
                component.value(converter.toInputValue(newValue));
            }.bind(this));
        },
    });
});
