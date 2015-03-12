"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/boolean', 'bootstrap.bootstrapswitch'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            this.$('.switch').bootstrapSwitch();
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
