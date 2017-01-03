'use strict';

define([
        'jquery',
        'pim/base-fetcher',
        'module',
        'routing',
        'oro/mediator',
        'pim/cache-invalidator',
        'pim/product-manager'
    ], function (
        $,
        BaseFetcher,
        module,
        Routing,
        mediator,
        CacheInvalidator,
        ProductManager
    ) {
        return BaseFetcher.extend({
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
             * @param {Object} options
             *
             * @return {Promise}
             */
            fetch: function (identifier, options) {
                options = options || {};

                options.code = identifier;
                var promise = BaseFetcher.prototype.fetch.apply(this, [identifier, options]);

                if (false === options.cached) {
                    promise.then(function (variantGroup) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(variantGroup);

                        return ProductManager.generateMissing(variantGroup);
                    }.bind(this));
                }

                if (options.generateMissing) {
                    promise.then(function (variantGroup) {
                        mediator.trigger('pim_enrich:form:variant_group:post_fetch', variantGroup);

                        return variantGroup;
                    });
                }

                return promise.promise();
            }
        });
    }
);
