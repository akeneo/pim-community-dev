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
            get: function (id) {
                return $.getJSON(Routing.generate('pim_enrich_product_rest_get', { id: id }))
                    .then(_.bind(function (product) {
                        return this.generateMissing(product);
                    }, this))
                    .then(function (product) {
                        mediator.trigger('pim_enrich:form:product:action:post_fetch', product);

                        return product;
                    })
                    .promise();
            },
            save: function (id, data) {
                return $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_rest_get', {id: id}),
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                }).then(_.bind(function (product) {
                    mediator.trigger('pim_enrich:form:entity:action:post_save', product);

                    return product;
                }, this));
            },
            remove: function (id) {
                return $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_remove', {id: id}),
                    headers: { accept: 'application/json' },
                    data: { _method: 'DELETE' }
                });
            },
            getValues: function (product) {
                return AttributeManager.getAttributesForProduct(product).then(function (attributes) {
                    _.each(attributes, _.bind(function (attributeCode) {
                        if (!product.values[attributeCode]) {
                            product.values[attributeCode] = [];
                        }
                    }, this));

                    return product.values;
                });
            },
            generateMissing: function (product) {
                return $.when(
                    FetcherRegistry.getFetcher('attribute').fetchAll(),
                    FetcherRegistry.getFetcher('locale').fetchAll(),
                    FetcherRegistry.getFetcher('channel').fetchAll(),
                    FetcherRegistry.getFetcher('currency').fetchAll(),
                    AttributeManager.getAttributesForProduct(product)
                ).then(function (attributes, locales, channels, currencies, productAttributes) {
                    var deferred = new $.Deferred();
                    var values = {};
                    product.values = Array.isArray(product.values) && 0 === product.values.length ? {} : product.values;

                    _.each(productAttributes, function (attributeCode) {
                        var attribute = _.findWhere(attributes, {code: attributeCode});

                        values[attribute.code] = AttributeManager.generateMissingValues(
                            _.has(product.values, attribute.code) ? product.values[attribute.code] : [],
                            attribute,
                            locales,
                            channels,
                            currencies
                        );
                    });

                    product.values = values;

                    deferred.resolve(product);

                    return deferred.promise();
                }).promise();
            }
        };
    }
);
