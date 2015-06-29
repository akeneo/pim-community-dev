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
        fetchForProduct: function (productId) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(_.identity).promise();
            }

            return this.entityPromises[productId];
        }
    });
});
