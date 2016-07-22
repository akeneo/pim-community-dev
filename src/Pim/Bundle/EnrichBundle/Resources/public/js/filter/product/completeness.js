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
        shortname: 'completeness',
        template: _.template(template),
        events: {
            'change [name="filter-operator"]': 'updateState'
        },

        /**
         * {@inherit}
         */
        initialize: function (config) {
            this.config = config.config;
        },

        /**
         * {@inherit}
         */
        configure: function () {
            this.on('locales:update:after', this.updateState.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
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
                isEditable: this.isEditable(),
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
            this.$('[name="filter-operator"]').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inherit}
         */
        isEmpty: function () {
            return 'ALL' === this.getOperator();
        },

        /**
         * Updates operator and value on fields change.
         */
        updateState: function () {
            this.setData({
                operator: this.$('[name="filter-operator"]').val(),
                value: 100,
                context: {'locales': this.getParentForm().getFormData().structure.locales}
            });
        }
    });
});
