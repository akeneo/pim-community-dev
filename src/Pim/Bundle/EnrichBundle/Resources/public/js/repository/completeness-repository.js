'use strict';

define(['jquery', 'underscore', 'routing', 'pim/entity-repository'], function ($, _, Routing, EntityRepository) {
    var completenessPromises = {};
    return EntityRepository.extend({
        findForProduct: function (productId) {
            if (!(productId in completenessPromises)) {
                completenessPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(_.identity).promise();
            }

            return completenessPromises[productId];
        },
        invalidateCache: function (productId) {
            if (productId) {
                delete completenessPromises[productId];
            } else {
                completenessPromises = {};
            }
        }
    });
});
