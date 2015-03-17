'use strict';

define(['jquery', 'routing'], function ($, Routing) {
    return {
        completenesses: {},
        completenessPromises: {},
        getCompleteness: function (productId) {
            var promise = $.Deferred();

            if (!(productId in this.completenessPromises)) {
                this.completenessPromises[productId] = $.ajax(
                    Routing.generate('pim_enrich_product_completeness_rest_get', {id: productId}),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            if (!(productId in this.completenesses)) {
                this.completenessPromises[productId].done(_.bind(function(product) {
                    this.completenesses[productId] = product;

                    promise.resolve(this.completenesses[productId]);
                }, this));
            } else {
                promise.resolve(this.completenesses[productId]);
            }

            return promise.promise();
        }
    };
});
