"use strict";

define(['pim/text-field', 'pim/price-field'], function (TextField, PriceField) {
    return {
        fields: {},
        attributes: null,
        attributesPromise: null,
        getField: function (attributeCode) {
            var promise = new $.Deferred();

            if (this.fields[attributeCode]) {
                promise.resolve(this.fields[attributeCode]);
            }

            this.getAttribute(attributeCode).done(_.bind(function(attribute) {
                var field = null;
                if (attributeCode === 'price') {
                    field = new PriceField(attribute);
                } else {
                    field = new TextField(attribute);
                }

                this.fields[attributeCode] = field;
                console.log(field);
                promise.resolve(this.fields[attributeCode]);
            }, this));

            return promise.promise();
        },
        getFields: function() {
            return this.fields;
        },
        getAttribute: function(attributeCode)
        {
            var promise = new $.Deferred();
            if (null === this.attributesPromise) {
                this.attributesPromise = $.ajax(
                    Routing.generate('pim_enrich_attribute_rest_index'),
                    {
                        method: 'GET'
                    }
                ).promise();
            }

            if (null === this.attributes) {
                this.attributesPromise.done(_.bind(function(data) {
                    this.attributes = data;

                    promise.resolve(this.attributes[attributeCode]);
                }, this));
            } else {
                promise.resolve(this.attributes[attributeCode]);
            }

            return promise.promise();
        }
    };
});
