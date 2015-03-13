"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/price-collection'], function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            // var data = event.currentTarget.value;
            // this.setCurrentValue(data);
        }
    });
});
