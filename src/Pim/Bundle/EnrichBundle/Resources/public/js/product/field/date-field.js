"use strict";

define(
    [
        'pim/field',
        'underscore',
        'text!pim/template/product/field/date',
        'bootstrap.bootstrapsdatepicker'
    ],
    function (
        Field,
        _,
        fieldTemplate,
        datepicker
    ) {
        return Field.extend({
            template: _.template(fieldTemplate),
            events: {
                'change input': 'updateModel'
            },
            render: function() {
                Field.prototype.render.apply(this, arguments);

                setTimeout(_.bind(function() {
                    this.$('input').datepicker();
                }, this), 0);
            },
            updateModel: function (event) {
                var data = event.currentTarget.value;
                this.setCurrentValue(data);
            }
        });
    }
);
