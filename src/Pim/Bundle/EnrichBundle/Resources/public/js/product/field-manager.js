"use strict";

define(['pim/attribute-manager', 'pim/text-field', 'pim/price-field', 'routing'], function (AttributeManager, TextField, PriceField, Routing) {
    return {
        fields: {},
        getField: function (attributeCode) {
            var promise = new $.Deferred();

            if (this.fields[attributeCode]) {
                promise.resolve(this.fields[attributeCode]);

                return promise.promise();
            }

            AttributeManager.getAttribute(attributeCode).done(_.bind(function(attribute) {
                var field = null;
                if (attributeCode === 'price') {
                    field = new PriceField(attribute);
                } else {
                    field = new TextField(attribute);
                }

                this.fields[attributeCode] = field;
                promise.resolve(this.fields[attributeCode]);
            }, this));

            return promise.promise();
        },
        getFields: function() {
            return this.fields;
        },
        getProductAttributeGroups: function()
        {
            _.each(this.fields, function() {

            });
        }
    };
});
