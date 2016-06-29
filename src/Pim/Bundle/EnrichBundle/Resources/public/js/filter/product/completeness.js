/* global console */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'routing',
    'text!pim/template/filter/product/completeness',
    'jquery.select2'
], function (_, __, BaseFilter, Routing, template) {
    return BaseFilter.extend({
        template: _.template(template),
        events: {
            'change [name="filter-value"]': 'updateState'
        },
        initialize: function (config) {
            this.config = config.config;
        },
        renderInput: function () {
            if (undefined === this.getOperator()) {
                this.setOperator(_.first(this.config.operators));
            }

            return this.template({
                __: __,
                operator: this.getOperator(),
                value: this.getValue(),
                operatorChoices: this.config.operators
            });
        },
        postRender: function () {
            this.$('[name="filter-value"]').select2();
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
