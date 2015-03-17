'use strict';

define(['jquery', 'routing'], function ($, Routing) {
    return {
        get: function (id) {
            var promise = $.Deferred();

            $.ajax(
                Routing.generate('pim_enrich_product_rest_get', {id: id}),
                {
                    method: 'GET'
                }
            ).done(function(product) {
                promise.resolve(product);
            });

            return promise.promise();
        },
        save: function (id, data) {
            return $.ajax({
                type: 'POST',
                url: Routing.generate('pim_enrich_product_rest_get', {id: id}),
                contentType: 'application/json',
                data: JSON.stringify(data)
            }).promise();
        }
    };
});
