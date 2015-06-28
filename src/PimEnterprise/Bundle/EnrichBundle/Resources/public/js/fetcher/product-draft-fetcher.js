'use strict';

define(['jquery', 'underscore', 'routing', 'pim/base-fetcher'], function ($, _, Routing, BaseFetcher) {
    return BaseFetcher.extend({
        entityPromises: {},
        fetchForProduct: function (productId) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(_.identity).promise();
            }

            return this.entityPromises[productId];
        },
        /** TODO: move it to a proper location */
        sendForApproval: function (draft) {
            return $.post(Routing.generate(this.options.urls.ready, { id: draft.id })).promise();
        }
    });
});
