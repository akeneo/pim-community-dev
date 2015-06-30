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
            productPromises: {},
            get: function (id) {
                if (!(id in this.productPromises)) {
                    this.productPromises[id] = $.getJSON(Routing.generate('pim_enrich_product_rest_get', { id: id }))
                        .then(_.bind(function (product) {
                            return this.generateMissing(product);
                        }, this))
                        .then(function(product) {
                            var promises = [];
                            mediator.trigger('product:action:post_fetch', {promises: promises, product: product});

                            return $.when.apply($, promises).then(function () {
                                return product;
                            })
                        })
                        .promise();
                }

                return this.productPromises[id];
            },
            save: function (id, data) {
                return $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_rest_get', {id: id}),
                    contentType: 'application/json',
                    data: JSON.stringify(data)
                }).then(_.bind(function (data) {
                    this.productPromises[id] = $.Deferred().resolve(data).promise();

                    return data;
                }, this));
            },
            remove: function (id) {
                return $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_remove', {id: id}),
                    headers: { accept: 'application/json' },
                    data: { _method: 'DELETE' }
                }).then(_.bind(function () {
                    delete this.productPromises[id];
                }, this));
            },
            clear: function (id) {
                if (id in this.productPromises) {
                    delete this.productPromises[id];
                }
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
