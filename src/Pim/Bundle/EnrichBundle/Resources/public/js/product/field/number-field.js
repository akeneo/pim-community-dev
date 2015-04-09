'use strict';

define(['pim/field', 'underscore', 'text!pim/template/product/field/number'], function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'number',
        events: {
            'change input': 'updateModel'
        },
        renderInput: function (context) {
            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;

            if ('' !== data) {
                data = this.attribute.decimalsAllowed ? parseFloat(data) : parseInt(data);
            }

            if (isNaN(data)) {
                data = null;
            }

            this.setCurrentValue(data);
        }
    });
});
