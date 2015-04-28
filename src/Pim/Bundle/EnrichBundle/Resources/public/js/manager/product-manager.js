'use strict';

define(['jquery', 'underscore', 'routing', 'pim/attribute-manager'], function ($, _, Routing, AttributeManager) {
    return {
        productValues: null,
        productPromises: {},
        get: function (id) {
            if (!(id in this.productPromises)) {
                var deferred = $.Deferred();

                $.ajax(
                    Routing.generate('pim_enrich_product_rest_get', {id: id}),
                    {
                        method: 'GET'
                    }
                ).done(function (product) {
                    deferred.resolve(product);
                });

                this.productPromises[id] = deferred.promise();
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
                this.productPromises = $.Deferred().resolve(data).promise();
            }, this)).promise();
        },
        remove: function (id) {
            return $.ajax({
                type: 'POST',
                url: Routing.generate('pim_enrich_product_remove', {id: id}),
                headers: { accept: 'application/json' },
                data: { _method: 'DELETE' }
            }).then(_.bind(function () {
                delete this.productPromises[id];
            }), this).promise();
        },
        getValues: function (product) {
            var deferred = $.Deferred();

            AttributeManager.getAttributesForProduct(product).done(function (attributes) {
                _.each(attributes, _.bind(function (attributeCode) {
                    if (!product.values[attributeCode]) {
                        product.values[attributeCode] = [];
                    }
                }, this));

                deferred.resolve(product.values);
            });

            return deferred.promise();
        }
    };
});
