"use strict";

define(['pim/field', 'underscore', 'text!pim/template/product/field/metric', 'jquery.select2'], function (Field, _, fieldTemplate) {
    return Field.extend({
        template: _.template(fieldTemplate),
        events: {
            'change input': 'updateModel'
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            setTimeout(_.bind(function() {
                this.$('.unit').select2();
            }, this), 0);
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        }
    });
});
