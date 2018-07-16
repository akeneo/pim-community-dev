'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing',
        'pim/base-fetcher'
    ],
    function (
        $,
        _,
        Routing,
        BaseFetcher
    ) {
        return BaseFetcher.extend({
            fetchAllByProduct: function (productId) {
                return $.getJSON(
                    Routing.generate(this.options.urls.product_index, {productId: productId})
                ).promise();
            }
        });
    }
);
