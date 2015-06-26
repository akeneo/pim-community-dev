'use strict';

define(['jquery', 'underscore', 'routing', 'pim/entity-repository'], function ($, _, Routing, EntityRepository) {
    return EntityRepository.extend({
        entityPromises: {},
        get: function (productId) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(_.identity).promise();
            }

            return this.entityPromises[productId];
        },
        sendForApproval: function (draft) {
            return $.post(Routing.generate(this.options.urls.ready, { id: draft.id })).promise();
        }
    });
});
