'use strict';

define(['jquery', 'underscore', 'routing', 'pim/attribute-manager'], function ($, _, Routing, AttributeManager) {
    return {
        productValues: null,
        productPromises: {},
        get: function (id) {
            if (!(id in this.productPromises)) {
                this.productPromises[id] = $.getJSON(Routing.generate('pim_enrich_product_rest_get', { id: id }))
                    .then(_.identity)
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
            }, this)).promise();
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
        }
    };
});
