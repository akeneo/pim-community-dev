"use strict";

define(['pim/text-field'], function (TextField) {
    return {
        getField: function (attributeCode) {
            var attribute = {
                'code': attributeCode,
                'label': attributeCode,
                'type': 'pim_catalog_text',
                'localizable': false,
                'scopable': false
            };

            return TextField.init(attribute);
        }
    };
});
