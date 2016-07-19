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
         * {@inherit}
         */
        initialize: function (config) {
            this.config = config.config;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inherit}
         */
        isEmpty: function () {
            return _.isEmpty(this.getValue());
        },

        /**
         * {@inherit}
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
         * {@inherit}
         */
        getTemplateContext: function () {
            return FetcherRegistry
                .getFetcher('attribute')
                .getIdentifierAttribute()
                .then(function (identifier) {
                    return {
                        label: i18n.getLabel(
                            identifier.labels,
                            UserContext.get('catalogLocale'),
                            identifier.code
                        ),
                        removable: false
                    };
                }.bind(this));
        },

        /**
         * {@inherit}
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
