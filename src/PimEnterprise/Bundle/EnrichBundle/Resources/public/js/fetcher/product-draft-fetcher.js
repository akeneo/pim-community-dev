'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing',
        'pim/base-fetcher',
        'pimee/model/draft'
    ],
    function (
        $,
        _,
        Routing,
        BaseFetcher,
        Draft
    ) {
        return BaseFetcher.extend({
            entityPromises: {},

            /**
             * Retrieve the draft corresponding to the specified product
             *
             * @param productId
             *
             * @returns {Promise}
             */
            fetchForProduct: function (productId) {
                if (!(productId in this.entityPromises)) {
                    this.entityPromises[productId] = $.getJSON(
                        Routing.generate(this.options.urls.get, {id: productId})
                    ).then(_.bind(function (draftData) {
                        var draft = new Draft(draftData);
                        draft.setUrl('ready', this.options.urls.ready);

                        return draft;
                    }, this));
                }

                return this.entityPromises[productId];
            }
        });
    }
);
