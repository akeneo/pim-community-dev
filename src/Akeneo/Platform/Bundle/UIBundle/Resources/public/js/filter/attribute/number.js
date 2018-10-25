'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'pim/template/filter/attribute/number',
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
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode(), operator: _.first(_.values(this.config.operators))});
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
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
                operators: this.getLabelledOperatorChoices(this.shortname)
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
