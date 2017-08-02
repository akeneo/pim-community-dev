'use strict';

define(
    [
        'jquery',
        'backbone',
        'routing',
        'oro/mediator',
        'pim/cache-invalidator'
    ],
    function (
        $,
        Backbone,
        Routing,
        mediator,
        CacheInvalidator
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

                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            }
        });
    }
);
