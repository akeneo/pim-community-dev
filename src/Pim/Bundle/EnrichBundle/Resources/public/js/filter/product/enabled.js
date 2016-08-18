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
    ], function (_, __, BaseFilter, Routing, template) {
    return BaseFilter.extend({
        shortname: 'enabled',
        template: _.template(template),
        removable: false,
        events: {
            'change [name="filter-value"]': 'updateState'
        },

        /**
         * Returns rendered input.
         *
         * @return {String}
         */
        renderInput: function () {
            return this.template({
                isEditable: this.isEditable(),
                labels: {
                    title: __('pim_enrich.export.product.filter.enabled.title'),
                    valueChoices: {
                        all: __('pim_enrich.export.product.filter.enabled.value.all'),
                        enabled: __('pim_enrich.export.product.filter.enabled.value.enabled'),
                        disabled: __('pim_enrich.export.product.filter.enabled.value.disabled')
                    }
                },
                value: this.getValue()
            });
        },

        /**
         * Initializes select2 after rendering.
         */
        postRender: function () {
            this.$('[name="filter-value"]').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            if (undefined === this.getOperator()) {
                this.setOperator('=');
            }
            if (undefined === this.getValue()) {
                this.setValue(true, {silent: false});
            }

            return BaseFilter.prototype.getTemplateContext.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return false;
        },

        /**
         * Updates operator and value on fields change.
         */
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();

            if ('all' === value) {
                this.setData({operator: 'ALL', value: null});
            } else {
                this.setData({operator: '=', value: 'enabled' === value});
            }
        }
    });
});
