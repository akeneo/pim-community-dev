'use strict';

define(['jquery', 'underscore', 'routing', 'pim/base-fetcher'], function ($, _, Routing, BaseFetcher) {
    return BaseFetcher.extend({
        /**
         * Fetch completenesses for the given product id
         *
         * @param Integer productId
         *
         * @return Promise
         */
        fetchForProduct: function (productId, family) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(function (completenesses) {
                    return {completenesses: completenesses, family: family};
                });

                return this.entityPromises[productId];
            } else {
                return this.entityPromises[productId].then(function (completeness) {
                    return (family !== completeness.family) ?
                        {completenesses: {}, family: family} :
                        this.entityPromises[productId];
                }.bind(this));
            }

        }
    });
});
