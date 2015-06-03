'use strict';

define(
    [
        'pim/field',
        'underscore',
        'pim/attribute-manager',
        'text!pim/template/product/field/date',
        'bootstrap.bootstrapsdatepicker'
    ],
    function (
        Field,
        _,
        AttributeManager,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'date',
            events: {
                'change input': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            render: function () {
                Field.prototype.render.apply(this, arguments);

                setTimeout(_.bind(function () {
                    this.$('.datepicker').datepicker();
                }, this), 0);
            },
            updateModel: function (event) {
                var data = event.currentTarget.value;
                data = '' === data ? AttributeManager.getEmptyValue(this.attribute) : data;

                this.setCurrentValue(data);
            }
        });
    }
);
