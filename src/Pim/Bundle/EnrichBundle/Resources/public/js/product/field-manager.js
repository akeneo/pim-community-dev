"use strict";

define(['pim/text-field', 'pim/price-field'], function (TextField, PriceField) {
    return {
        fields: {},
        getField: function (attributeCode) {
            if (this.fields[attributeCode]) {
                return this.fields[attributeCode];
            }

            var attribute = {
                'name': {
                    'code': attributeCode,
                    'label': attributeCode,
                    'type': 'pim_catalog_text',
                    'localizable': true,
                    'scopable': false
                },
                'description': {
                    'code': attributeCode,
                    'label': attributeCode,
                    'type': 'pim_catalog_text',
                    'localizable': true,
                    'scopable': true
                },
                'price': {
                    'code': attributeCode,
                    'label': attributeCode,
                    'type': 'pim_catalog_prices',
                    'localizable': false,
                    'scopable': false
                },
                'sku': {
                    'code': attributeCode,
                    'label': attributeCode,
                    'type': 'pim_catalog_identifier',
                    'localizable': false,
                    'scopable': false
                }
            };

            var field = null;
            if (attributeCode === 'price') {
                field = new PriceField(attribute[attributeCode]);
            } else {
                field = new TextField(attribute[attributeCode]);
            }

            this.fields[attributeCode] = field;

            return field;
        },
        getFields: function() {
            return this.fields;
        }
    };
});
