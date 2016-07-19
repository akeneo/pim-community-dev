'use strict';

define([
        'underscore',
        'pim/filter/filter',
        'text!pim/template/filter/text'
    ], function (_, BaseFilter, template) {
    return BaseFilter.extend({
        template: _.template(template),
        events: {
            'blur input': 'updateState',
            'click .remove': 'removeFilter'
        },

        /**
         * {@inherit}
         */
        render: function () {
            this.$el.empty().append(this.template({filter: this.getFormData(), removable: this.isRemovable()}));

            this.delegateEvents();

            return this;
        },

        /**
         * Udpate the sate on field change
         */
        updateState: function () {
            var value = '';
            try {
                value = JSON.parse(this.$('input[name="filter-value"]').val());
            } catch (error) {
                value = this.$('input[name="filter-value"]').val();
            }
            this.setData({
                operator: this.$('input[name="operator"]').val(),
                value: value
            });
        }
    });
});
