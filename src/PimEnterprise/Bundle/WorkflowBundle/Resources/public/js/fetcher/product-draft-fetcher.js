'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing',
        'pim/base-fetcher',
        'pim/product-manager'
    ],
    function (
        $,
        _,
        Routing,
        BaseFetcher,
        ProductManager
    ) {
        return BaseFetcher.extend({
            fetchAllByProduct: function (productId) {
                if (!this.entityListPromise) {
                    this.entityListPromise = $.getJSON(
                        Routing.generate(this.options.urls.product_index, {productId: productId})
                    )
                    .then(function (drafts) {
                        var draftsPromises = [];

                        _.each(drafts, function (draft) {
                            draftsPromises.push(
                                ProductManager
                                    .doGenerateMissing(draft.changes)
                                    .then(function () {return draft;})
                            );
                        });

                        return this.getObjects(draftsPromises);
                    }.bind(this))
                    .promise();
                }

                return this.entityListPromise;
            }
        });
    }
);
