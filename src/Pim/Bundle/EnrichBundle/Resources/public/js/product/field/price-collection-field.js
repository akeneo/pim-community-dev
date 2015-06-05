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
        'pim/entity-manager',
        'text!pim/template/product/field/price-collection'
    ],
    function ($, Field, _, EntityManager, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'price-collection',
        events: {
            'change input[type="text"]': 'updateModel'
        },
        renderInput: function (context) {
            context.value.value = _.sortBy(context.value.value, 'currency')

            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            var data = [];
            var $elements = $(event.currentTarget).parents('.price-collection-field').find('.price-input');
            _.each($elements, _.bind(function (element) {
                var input = $(element).children('input');

                var inputData = input.val();

                inputData = ('' !== inputData) ? parseFloat(inputData) : inputData;
                inputData = isNaN(inputData) || '' === inputData ? null : inputData;

                input.val(null === inputData ? input.defaultValue : inputData);
                data.push({'data': inputData, 'currency': input.data('currency')});
            }, this));

            this.setCurrentValue(data);
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                EntityManager.getRepository('currency').findAll()
            ).then(function (templateContext, currencies) {
                templateContext.currencies = currencies;

                return templateContext;
            });
        }
    });
});
