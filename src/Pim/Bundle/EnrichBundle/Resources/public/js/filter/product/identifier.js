'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/product/identifier',
    'jquery.select2'
], function (
    _,
    __,
    BaseFilter,
    FetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseFilter.extend({
        shortname: 'identifier',
        template: _.template(template),
        events: {
            'change [name="filter-value"]': 'updateState'
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
        configure: function () {
            return $.when(
                FetcherRegistry.getFetcher('attribute').getIdentifierAttribute(),
                BaseFilter.prototype.configure.apply(this, arguments)
            ).then(function (identifier) {
                this.identifier = identifier;
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return _.isEmpty(this.getValue());
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function () {
            return this.template({
                __: __,
                value: _.isArray(this.getValue()) ? this.getValue().join(', ') : '',
                field: this.getField(),
                isEditable: this.isEditable()
            });
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            this.field = this.identifier.code;

            return $.Deferred().resolve({
                label: i18n.getLabel(
                    this.identifier.labels,
                    UserContext.get('catalogLocale'),
                    this.identifier.code
                ),
                removable: false
            }).promise();
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            var value = this.$('[name="filter-value"]').val().split(/[\s,]+/);
            var cleanedValues = _.reject(value, function (val) {
                return '' === val;
            });

            this.setData({
                field: this.getField(),
                operator: 'IN',
                value: cleanedValues
            });
        }
    });
});
