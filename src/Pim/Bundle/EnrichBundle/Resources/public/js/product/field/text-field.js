'use strict';

define([
        'pim/field',
        'underscore',
        'pim/attribute-manager',
        'text!pim/template/product/field/text'
    ], function (
        Field,
        _,
        AttributeManager,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'text',
            events: {
                'change input': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function (event) {
                var data = event.currentTarget.value;
                    data = '' === data ? AttributeManager.getEmptyValue(this.attribute) : data;

                this.setCurrentValue(data);
            }
        });
    }
);
