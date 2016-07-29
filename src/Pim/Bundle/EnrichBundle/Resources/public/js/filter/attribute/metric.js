'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/attribute/metric',
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
                    unit: templateContext.defaultMetricUnit
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
            this.$('.operator, .unit').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('attribute').fetch(this.getField()),
                FetcherRegistry.getFetcher('measure').fetchAll()
            ).then(function (templateContext, attribute, measures) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
                    units: measures[attribute.metric_family],
                    defaultMetricUnit: attribute.default_metric_unit
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = {
                data: this.$('[name="filter-data"]').val(),
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
