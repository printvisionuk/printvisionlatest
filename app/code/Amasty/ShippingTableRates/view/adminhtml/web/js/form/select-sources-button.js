define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'uiRegistry',
    'Amasty_ShippingTableRates/js/model/sources-converter'
], function ($j, Element, registry, converter) {
    'use strict';

    return Element.extend({
        defaults: {
            sources: [],
            listens: {
                sources: 'onSourcesChange'
            },
            modules: {
                listing: 'index = inventory_sources',
                targetField: ''
            }
        },

        initObservable: function () {
            this._super();

            this.observe(['sources']);

            return this;
        },

        /**
         * Handler to change input value when sources selected on grid
         *
         * @param {Array} newSources
         */
        onSourcesChange: function (newSources) {
            this.targetField().value(converter.toInputValue(newSources));
        },

        /**
         * Handler to change grid rows according to input value
         */
        onModalOpen: function () {
            registry.get(this.targetField, function (field) {
                var listingValue = converter.toListingValue(field.value());

                this.listing().externalValue(listingValue);
            }.bind(this));
        }
    });
});
