'use strict';
/**
 * Price collection field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'pim/field',
        'underscore',
        'pim/fetcher-registry',
        'pim/template/product/field/price-collection'
    ],
    function ($, Field, _, FetcherRegistry, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change .field-input:first input[type="text"]': 'updateModel'
        },
        renderInput: function (context) {
            if (undefined === context.value) {
                return null;
            }

            context.value.data = _.sortBy(context.value.data, 'currency');

            return this.fieldTemplate(context);
        },
        updateModel: function () {
            var prices = [];
            var inputs = this.$('.field-input:first .price-input input');
            _.each(inputs, function (input) {
                var $input = $(input);
                var inputData = $input.val();
                prices.push({
                    amount: '' === inputData ? null : inputData,
                    currency: $input.data('currency')
                });
            }.bind(this));

            this.setCurrentValue(_.sortBy(prices, 'currency'));
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('currency').fetchAll()
            ).then(function (templateContext, currencies) {
                templateContext.currencies = currencies;

                return templateContext;
            });
        },
        setFocus: function () {
            this.$('input[type="text"]:first').focus();
        }
    });
});
