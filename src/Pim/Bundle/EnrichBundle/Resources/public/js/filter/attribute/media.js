'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/attribute/media',
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
        shortname: 'media',
        template: _.template(template),
        events: {
            'change [name="filter-value"], [name="filter-operator"]': 'updateState'
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
                (undefined === this.getValue() || '' === this.getValue());
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            var value = this.getValue();

            if (undefined === value) {
                value = '';
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
            this.$('.operator').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('attribute').fetch(this.getField())
            ).then(function (templateContext, attribute) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code)
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = this.$('[name="filter-value"]').val();
            var operator = this.$('[name="filter-operator"]').val();

            this.setData({
                field: this.getField(),
                operator: operator,
                value: value
            });
        }
    });
});
