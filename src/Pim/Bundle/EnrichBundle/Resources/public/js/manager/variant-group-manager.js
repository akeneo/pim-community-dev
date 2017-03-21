'use strict';

define([
        'jquery',
        'underscore',
        'module',
        'oro/mediator',
        'routing',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ], function (
        $,
        _,
        module,
        mediator,
        Routing,
        AttributeManager,
        FetcherRegistry
    ) {
        return {
            productValues: null,
            doGenerateMissing: function (variantGroup) {
                return AttributeManager.getAttributes(variantGroup)
                    .then(function (productAttributeCodes) {
                        return $.when(
                            FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(productAttributeCodes),
                            FetcherRegistry.getFetcher('locale').fetchActivated(),
                            FetcherRegistry.getFetcher('channel').fetchAll(),
                            FetcherRegistry.getFetcher('currency').fetchAll()
                        );
                    })
                    .then(function (attributes, locales, channels, currencies) {
                        var oldValues = {};
                        var newValues = {};

                        if (!_.isArray(variantGroup.values)) {
                            oldValues = variantGroup.values;
                        }

                        _.each(attributes, function (attribute) {
                            newValues[attribute.code] = AttributeManager.generateMissingValues(
                                _.has(oldValues, attribute.code) ? oldValues[attribute.code] : [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        });

                        variantGroup.values = newValues;

                        return variantGroup;
                    });
            },
            generateMissing: function (product) {
                return this.doGenerateMissing(product);
            }
        };
    }
);
