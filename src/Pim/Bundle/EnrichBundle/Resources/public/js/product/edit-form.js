"use strict";

define(['jquery', 'underscore', 'routing', 'pim/field-manager'], function($, _, Routing, FieldManager) {
    var productManager = {
        get: function (id) {
            return $.ajax(
                Routing.generate('pim_enrich_product_rest_get', {id: id}),
                {
                    method: 'GET'
                }
            ).promise();
        }
    };

    var formManager = {
        render: function (product) {
            var content = '';

            _.each(product.values, _.bind(function (value, attributeCode) {
                console.log(value);
                var field = FieldManager.getField(attributeCode);
                field.setData(value);

                content += field.render();
            }, this));

            return content;
        }
    };

    (function() {
        productManager.get(1).done(function(data) {
            $('#product-edit-form').html(formManager.render(data));
        });
    })();
});
