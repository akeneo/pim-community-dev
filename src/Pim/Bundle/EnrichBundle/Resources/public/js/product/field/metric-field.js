"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/metric', 'jquery.select2'], function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'metric',
        events: {
            'change .data, .unit': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            this.$('.unit').select2('destroy').select2({});
        },
        updateModel: function () {
            var data = this.$('.data').val();
            this.setCurrentValue({
                unit: this.$('.unit option:selected').val(),
                data: '' !== data ? data : null
            });
        }
    });
});
