"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/metric', 'jquery.select2'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            this.$('.unit').select2('destroy').select2({});
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
