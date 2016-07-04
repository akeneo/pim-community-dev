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
            'change [name="filter-operator"]': 'updateState'
        },

        /**
         * Initializes configuration.
         *
         * @param config
         */
        initialize: function (config) {
            this.config = config.config;
        },

        /**
         * Returns rendered input.
         *
         * @return {String}
         */
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

        /**
         * Initializes select2 after rendering.
         */
        postRender: function () {
            this.$('[name="filter-operator"]').select2();
        },

        /**
         * Updates operator and value on fields change.
         */
        updateState: function () {
            var operator = this.$('[name="filter-operator"]').val();

            if ('ALL' === operator) {
                this.clearData();

                return;
            }

            this.setData({
                field: this.getField(),
                operator: operator,
                value: 100
            });
        }
    });
});
