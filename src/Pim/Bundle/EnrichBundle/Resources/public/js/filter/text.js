/* global console */
'use strict';

define([
        'pim/filter/filter',
        'text!pim/template/filter/text'
    ], function (BaseFilter, template) {
    return BaseFilter.extend({
        template: _.template(template),
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
            var value = '';
            try {
                value = JSON.parse(this.$('input[name="filter-value"]').val());
            } catch (error) {
                value = this.$('input[name="filter-value"]').val();
            }
            this.setData({
                'field': this.getField(),
                'operator': this.$('input[name="operator"]').val(),
                'value': value
            });
        }
    });
});
