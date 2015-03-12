"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/textarea'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change textarea': 'updateModel'
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
