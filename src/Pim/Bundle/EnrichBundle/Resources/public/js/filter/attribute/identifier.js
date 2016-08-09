'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/product/identifier',
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
            return FetcherRegistry
                .getFetcher('attribute')
                .fetch(this.getField())
                .then(function (attribute) {
                    return {
                        label: i18n.getLabel(
                            attribute.labels,
                            UserContext.get('catalogLocale'),
                            this.getField()
                        ),
                        removable: false
                    }
                }.bind(this));
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
