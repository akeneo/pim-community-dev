'use strict';

define(['jquery', 'routing'], function ($, Routing) {
    return {
        publish: function (id) {
            return $.ajax({
                type: 'PUT',
                url: Routing.generate('pimee_workflow_published_product_rest_publish', {originalId: id}),
                headers: { accept: 'application/json' }
            }).promise();
        },
        unpublish: function (id) {
            return $.ajax({
                type: 'DELETE',
                url: Routing.generate('pimee_workflow_published_product_rest_unpublish', {originalId: id}),
                headers: { accept: 'application/json' }
            }).promise();
        }
    };
});
