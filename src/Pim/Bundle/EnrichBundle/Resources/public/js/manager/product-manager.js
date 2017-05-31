'use strict';

define([
        'jquery',
        'underscore',
        'oro/mediator',
        'routing',
        'pim/attribute-manager',
        'pim/fetcher-registry'
    ], function (
        $,
        _,
        mediator,
        Routing,
        AttributeManager,
        FetcherRegistry
    ) {
        return {
            productValues: null,
            doGenerateMissing: function (product) {
                return AttributeManager.getAttributes(product)
                    .then(function (productAttributeCodes) {
                        return $.when(
                            FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(productAttributeCodes),
                            FetcherRegistry.getFetcher('locale').fetchActivated(),
                            FetcherRegistry.getFetcher('channel').fetchAll(),
                            FetcherRegistry.getFetcher('currency').fetchAll(),
                            FetcherRegistry.getFetcher('association-type').fetchAll()
                        );
                    })
                    .then(function (attributes, locales, channels, currencies, associationTypes) {
                        var oldValues = _.isArray(product.values) && 0 === product.values.length ? {} : product.values;
                        var newValues = {};

                        _.each(attributes, function (attribute) {
                            newValues[attribute.code] = AttributeManager.generateMissingValues(
                                _.has(oldValues, attribute.code) ? oldValues[attribute.code] : [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        });

                        var associations = {};
                        _.each(associationTypes, function (assocType) {
                            associations[assocType.code] = AttributeManager.generateMissingAssociations(
                                _.has(product.associations, assocType.code) ? product.associations[assocType.code] : {}
                            );
                        });

                        product.values       = newValues;
                        product.associations = associations;

                        return product;
                    });
            },
            generateMissing: function (product) {
                return this.doGenerateMissing(product);
            }
        };
    }
);
