'use strict';

define([
    'underscore',
    'pim/job/common/edit/field/field',
    'text!pim/template/export/common/edit/field/text'
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

        getFieldValue: function () {
            return this.$('input').val();
        }
    });
});
