'use strict';

define(
    [
        'jquery',
        'backbone',
        'routing',
        'oro/mediator',
        'pim/cache-invalidator',
        'pim/product-manager'
    ],
    function (
        $,
        Backbone,
        Routing,
        mediator,
        CacheInvalidator,
        ProductManager
    ) {
        return Backbone.Model.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch an element based on its identifier
             *
             * @param {string} identifier
             *
             * @return {Promise}
             */
            fetch: function (identifier) {
                return $.getJSON(Routing.generate(this.options.urls.get, { id: identifier }))
                    .then(function (product) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(product);

                        return ProductManager.generateMissing(product);
                    }.bind(this))
                    .then(function (product) {
                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            }
        });
    }
);
