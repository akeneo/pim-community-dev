'use strict';

define(['pim/field', 'underscore', 'text!pim/template/product/field/textarea'], function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'textarea',
        events: {
            'change textarea': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            var data = event.currentTarget.value;
            this.setCurrentValue(data);
        },
        setFocus: function() {
            this.$('textarea').first().focus();
        }
    });
});
