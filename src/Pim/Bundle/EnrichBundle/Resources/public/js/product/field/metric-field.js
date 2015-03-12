"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/metric', 'jquery.select2'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change .data, .unit': 'updateModel'
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            this.$('.unit').select2('destroy').select2({});
        },
        setValues: function(values) {
            if (values.length === 0) {
                var emptyValue = this.createEmptyValue();
                emptyValue.value = {'data': null, 'unit': 'Kilogram'};
                values.push(emptyValue);
            }

            Field.prototype.setValues.apply(this, [values]);
        },
        updateModel: function () {
            this.setCurrentValue({
                unit: this.$('.unit option:selected').val(),
                data: this.$('.data').val()
            });
        }
    });
});
