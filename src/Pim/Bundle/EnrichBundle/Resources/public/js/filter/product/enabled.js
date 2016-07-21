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
            if (undefined === this.getValue()) {
                this.setValue(true);
            }
            if (undefined === this.getOperator()) {
                this.setOperator('=');
            }

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
         * {@inherit}
         */
        isEmpty: function () {
            return null === this.getValue();
        },

        /**
         * Updates operator and value on fields change.
         */
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();

            this.setData({
                operator: '=',
                value: 'all' === value ?
                    null :
                    'enabled' === value
            });
        }
    });
});
