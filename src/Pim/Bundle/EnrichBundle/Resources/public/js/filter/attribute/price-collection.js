'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/attribute/price-collection',
    'jquery.select2'
], function (
    $,
    _,
    __,
    BaseFilter,
    FetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseFilter.extend({
        shortname: 'price-collection',
        template: _.template(template),
        events: {
            'change [name="filter-data"], [name="filter-operator"], select.currency': 'updateState'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === this.getValue() || undefined === this.getValue().data || '' === this.getValue().data);
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            var value = this.getValue();

            if (undefined === value || undefined === value.data) {
                value = {
                    data: '',
                    currency: ''
                };
            }

            if ('' !== value.data) {
                value.data = Number(value.data);
            }

            return this.template(_.extend({}, templateContext, {
                __: __,
                value: value,
                field: this.getField(),
                operator: this.getOperator(),
                operators: this.config.operators
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('.operator, .currency').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('attribute').fetch(this.getField()),
                FetcherRegistry.getFetcher('currency').fetchAll()
            ).then(function (templateContext, attribute, currencies) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
                    currencies: currencies
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = {
                data: this.$('[name="filter-data"]').val(),
                currency: this.$('select[name="filter-currency"]').val()
            };

            var operator = this.$('[name="filter-operator"]').val();

            this.setData({
                field: this.getField(),
                operator: operator,
                value: value
            });
        }
    });
});
