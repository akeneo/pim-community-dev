'use strict';

//Should be reworked to be a boolean filter

define([
        'underscore',
        'oro/translator',
        'pim/filter/filter',
        'routing',
        'pim/template/filter/product/enabled',
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
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode(), operator: '=', value: true});
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
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
                    title: __('pim_common.status'),
                    valueChoices: {
                        all: __('pim_common.all'),
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
        isEmpty: function () {
            return false;
        },

        /**
         * Updates operator and value on fields change.
         */
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();

            if ('all' === value) {
                this.setData({field: this.getField(), operator: 'ALL', value: null});
            } else {
                this.setData({field: this.getField(), operator: '=', value: 'enabled' === value});
            }
        }
    });
});
