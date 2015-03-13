"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/media'], function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'media',
        events: {
            'change input': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
