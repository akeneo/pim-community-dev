/* global console */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'routing',
    'text!pim/template/filter/product/completeness',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'jquery.select2'
], function (_, __, BaseFilter, Routing, template) {
    return BaseFilter.extend({
        template: _.template(template),
        removable: false,
        events: {
            'change [name="filter-value"]': 'updateState',
            'click .remove': 'removeFilter'
        },
        initialize: function (config) {
            this.config = config.config;
        },
        render: function () {
            this.$el.empty();

            if (undefined === this.getOperator()) {
                this.setOperator(_.first(this.config.operators));
            }

            this.$el.append(this.template({
                __: __,
                operator: this.getOperator(),
                value: this.getValue(),
                removable: this.isRemovable(),
                operators: this.config.operators
            }));

            this.$('[name="filter-value"]').select2();

            this.delegateEvents();

            return this;
        },
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();

            if ('all' === value) {
                this.clearData();

                return;
            }

            this.setData({
                field: this.getField(),
                operator: '=',
                value: 'enabled' === value
            });
        }
    });
});
