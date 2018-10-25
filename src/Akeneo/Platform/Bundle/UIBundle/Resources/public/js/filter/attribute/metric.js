'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'pim/template/filter/attribute/metric',
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
        shortname: 'metric',
        template: _.template(template),
        events: {
            'change [name="filter-data"], [name="filter-operator"], select.unit': 'updateState'
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                FetcherRegistry.getFetcher('attribute').fetch(this.getCode()),
                BaseFilter.prototype.configure.apply(this, arguments)
            ).then(function (attribute) {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                    _.defaults(data, {
                        field: this.getCode(),
                        operator: _.first(_.values(this.config.operators)),
                        value: {
                            amount: '',
                            unit: attribute.default_metric_unit
                        }
                    });
                }.bind(this));
            }.bind(this));
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
            this.$('.operator, .unit').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('measure').fetchAll()
            ).then(function (templateContext, measures) {
                return _.extend({}, templateContext, {
                    units: measures[templateContext.attribute.metric_family]
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = {
                amount: this.$('[name="filter-data"]').val(),
                unit: this.$('select[name="filter-unit"]').val()
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
