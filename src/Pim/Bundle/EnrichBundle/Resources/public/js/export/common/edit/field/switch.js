'use strict';

define([
    'underscore',
    'pim/export/common/edit/field/field',
    'text!pim/template/export/common/edit/field/switch',
    'bootstrap.bootstrapswitch'
], function (
    _,
    BaseField,
    fieldTemplate
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change input': 'updateState'
        },

        render: function () {
            BaseField.prototype.render.apply(this, arguments);

            this.$('.switch').bootstrapSwitch();
        },

        getFieldValue: function () {
            return this.$('input[type="checkbox"]').prop('checked');
        }
    });
});
