/* global console */
'use strict';

//Should be reworked to be a boolean filter

define([
        'underscore',
        'oro/translator',
        'pim/filter/filter',
        'routing',
        'text!pim/template/filter/product/enabled',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'jquery.select2'
    ], function (_, __, BaseFilter, Routing, template, fetcherRegistry, userContext, i18n, initSelect2) {
    return BaseFilter.extend({
        template: _.template(template),
        removable: false,
        events: {
            'change [name="filter-value"]': 'updateState',
            'click .remove': 'removeFilter'
        },
        render: function () {
            this.$el.empty();

            if (undefined === this.getValue()) {
                this.setValue('');
            }

            this.$el.append(this.template({
                __: __,
                field: this.getField(),
                operator: this.getOperator(),
                value: this.getValue(),
                removable: this.isRemovable()
            }));

            this.$('[name="filter-value"]').select2();

            this.delegateEvents();

            return this;
        },
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();
            switch (value) {
                case 'all':
                    value = null;
                    break;
                case 'true':
                    value = true;
                    break;
                case 'false':
                    value = false;
                    break;
            }

            this.setData({
                field: this.getField(),
                operator: null === value ? '' : '=', //Maybe we should add an applyable parameter to disable a filter if we don't want to apply it instead of setting an empty operator
                value: value
            });
        }
    });
});
