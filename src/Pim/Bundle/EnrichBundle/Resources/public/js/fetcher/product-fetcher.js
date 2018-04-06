'use strict';

define(
    [
        'jquery',
        'pim/base-fetcher',
        'routing',
        'oro/mediator',
        'pim/cache-invalidator'
    ],
    function (
        $,
        BaseFetcher,
        Routing,
        mediator,
        CacheInvalidator
    ) {
        return BaseFetcher.extend({
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
                        const cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(product);

                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            },

            /**
             * {@inheritdoc}
             */
            getIdentifierField: function () {
                return $.Deferred().resolve('identifier');
            }
        });
    }
);
