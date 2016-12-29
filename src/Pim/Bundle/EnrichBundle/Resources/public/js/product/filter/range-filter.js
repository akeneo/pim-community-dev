'use strict';
/*
 * src/Acme/Bundle/CustomBundle/Resources/public/js/product/filter/range-filter.js
 */
define([
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'text!acme/template/product/filter/range',
    'jquery.select2'
], function (
    _,
    __,
    AttributeFilter,
    template
) {
    return AttributeFilter.extend({
        shortname: 'string',
        template: _.template(template),
        events: {
            'change [name="filter-value"]': 'updateState'
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode(), value: '', operator: '>='});
            }.bind(this));

            return AttributeFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inherit}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend({}, templateContext, {
                __: __,
                value: this.getValue(),
                field: this.getField(),
            }));
        },

        updateState: function () {
            this.setData({
                field: this.getField(),
                operator: this.getOperator(),
                value: this.$('[name="filter-value"]').val()
            });
        }
    });
});
