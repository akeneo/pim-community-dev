'use strict';

define([
        'jquery',
        'underscore',
        'module',
        'oro/mediator',
        'routing',
        'pim/attribute-manager',
        'pim/fetcher-registry',
        'pim/cache-invalidator'
    ], function (
        $,
        _,
        module,
        mediator,
        Routing,
        AttributeManager,
        FetcherRegistry,
        CacheInvalidator
    ) {
        return {
            productValues: null,
            get: function (id) {
                return $.getJSON(Routing.generate(module.config().urls.get, { identifier: id }))
                    .then(function (variantGroup) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(variantGroup);

                        return this.generateMissing(variantGroup);
                    }.bind(this))
                    .then(function (product) {
                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            },
            save: function (id, data) {
                return $.ajax({
                    type: 'POST',
                    url: Routing.generate(module.config().urls.post, {id: id}),
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                }).then(function (variantGroup) {
                    mediator.trigger('pim_enrich:form:entity:post_save', variantGroup);

                    return variantGroup;
                }.bind(this));
            },
            remove: function (id) {
                return $.ajax({
                    type: 'DELETE',
                    url: Routing.generate(module.config().urls.remove, {id: id}),
                    headers: { accept: 'application/json' },
                    data: { _method: 'DELETE' }
                });
            },
            getValues: function (variantGroup) {
                return AttributeManager.getAttributes(variantGroup).then(function (attributes) {
                    _.each(attributes, function (attributeCode) {
                        if (!variantGroup.values[attributeCode]) {
                            variantGroup.values[attributeCode] = [];
                        }
                    }.bind(this));

                    return variantGroup.values;
                });
            },
            doGenerateMissing: function (variantGroup) {
                return AttributeManager.getAttributes(variantGroup)
                    .then(function (productAttributeCodes) {
                        return $.when(
                            FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(productAttributeCodes),
                            FetcherRegistry.getFetcher('locale').fetchAll(),
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
