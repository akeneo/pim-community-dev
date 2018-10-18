'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'pim/template/filter/attribute/price-collection',
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
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {
                    field: this.getCode(),
                    operator: _.first(_.values(this.config.operators)),
                    value: {
                        amount: '',
                        currency: ''
                    }
                });
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === this.getValue() ||
                undefined === this.getValue().amount ||
                '' === this.getValue().amount);
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend({}, templateContext, {
                __: __,
                value: this.getValue(),
                field: this.getField(),
                operator: this.getOperator(),
                operators: this.getLabelledOperatorChoices(this.shortname)
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
                FetcherRegistry.getFetcher('currency').fetchAll()
            ).then(function (templateContext, currencies) {
                return _.extend({}, templateContext, {
                    currencies: currencies
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = {
                amount: this.$('[name="filter-data"]').val(),
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
