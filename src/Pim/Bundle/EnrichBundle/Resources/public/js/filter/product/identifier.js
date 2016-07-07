/* global console */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'text!pim/template/filter/product/identifier',
    'jquery.select2'
], function (_, __, BaseFilter, template, initSelect2) {
    return BaseFilter.extend({
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
         * Gets the template context.
         *
         * @returns {Promise}
         */
        getTemplateContext: function () {
            var deferred = $.Deferred();

            deferred.resolve({
                label: __('pim_enrich.export.product.filter.' + this.getField() + '.title'),
                removable: this.removable
            });

            return deferred.promise();
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
