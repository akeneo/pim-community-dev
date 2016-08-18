'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/attribute/number',
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
        shortname: 'number',
        template: _.template(template),
        events: {
            'change [name="filter-operator"], [name="filter-value"]': 'updateState'
        },

        /**
         * {@inherit}
         */
        initialize: function (config) {
            this.config = config.config;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode(), operator: _.first(_.values(this.config.operators))});
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments)
        },

        /**
         * {@inherit}
         */
        isEmpty: function () {
            return !_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === this.getValue() || '' === this.getValue());
        },

        /**
         * {@inherit}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend({}, templateContext, {
                __: __,
                shortName: this.shortname,
                value: this.getValue(),
                field: this.getField(),
                operator: this.getOperator(),
                operatorChoices: this.config.operators
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('.operator').select2({minimumResultsForSearch: -1});
        },

        /**
         * {@inherit}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('attribute').fetch(this.getCode())
            ).then(function (templateContext, attribute) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code)
                });
            }.bind(this));
        },

        /**
         * {@inherit}
         */
        updateState: function () {
            var operator = this.$('[name="filter-operator"]').val();
            var value = null;

            if (!_.contains(['EMPTY', 'NOT EMPTY'], operator)) {
                value = this.$('[name="filter-value"]').val().trim();
            }

            this.setData({
                field: this.getField(),
                operator: operator,
                value: value
            });
        }
    });
});
