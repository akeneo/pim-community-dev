'use strict';

define(
    ['pim/field', 'underscore', 'text!pim/template/product/field/boolean', 'bootstrap.bootstrapswitch'],
    function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'boolean',
        events: {
            'change input': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        render: function() {
            Field.prototype.render.apply(this, arguments);

            this.$('.switch').bootstrapSwitch();
        },
        updateModel: function (event) {
            var data = event.currentTarget.checked;
            this.setCurrentValue(data);

            Field.prototype.updateModel.apply(this, arguments);
        }
    });
});
