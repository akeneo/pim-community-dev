"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/number'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
