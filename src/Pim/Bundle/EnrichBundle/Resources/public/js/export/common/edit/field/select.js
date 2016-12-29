'use strict';

define([
    'underscore',
    'pim/export/common/edit/field/field',
    'text!pim/template/export/common/edit/field/select',
    'jquery.select2'
], function (
    _,
    BaseField,
    fieldTemplate
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change select': 'updateState'
        },

        render: function () {
            BaseField.prototype.render.apply(this, arguments);

            this.$('.select2').select2({});
        },

        getFieldValue: function () {
            return this.$('select').val();
        }
    });
});
