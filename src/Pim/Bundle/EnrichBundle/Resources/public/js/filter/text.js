/* global console */
'use strict';

define([
        'pim/form',
        'text!pim/template/filter/text'
    ], function (BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),
        field: null,
        removable: false,
        events: {
            'blur input': 'updateState',
            'click .remove': 'removeFilter'
        },
        render: function () {
            this.$el.empty().append(this.template({filter: this.getFormData(), removable: this.isRemovable()}));

            this.delegateEvents();

            return this;
        },
        updateState: function () {
            var data = '';
            try {
                data = JSON.parse(this.$('input[name="data"]').val());
            } catch (error) {
                data = this.$('input[name="data"]').val();
            }
            this.setData({
                'field': this.getField(),
                'operator': this.$('input[name="operator"]').val(),
                'data': data
            });
        },
        setField: function (field) {
            this.field = field;

            var data = this.getFormData();
            data.field = field;
            this.setData(data, {silent: true});
        },
        getField: function () {
            return this.field;
        },
        setRemovable: function (removable) {
            this.removable = removable;
        },
        isRemovable: function () {
            return this.removable;
        },
        removeFilter: function () {
            this.trigger('filter:remove', this.getField());
        }
    });
});
