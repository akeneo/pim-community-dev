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
        events: {
            'change .field-input input[type="text"]': 'updateModel'
        },
        renderInput: function (context) {
            context.value.value = _.sortBy(context.value.value, 'currency');

            return this.fieldTemplate(context);
        },
        updateModel: function () {
            var data = [];
            var $elements = this.$('.field-input .price-input');
            _.each($elements, _.bind(function (element) {
                var $input = $(element).children('input');

                var inputData = $input.val();
                if ('' !== inputData) {
                    data.push({'data': inputData, 'currency': $input.data('currency')});
                }
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
        },
        setFocus: function () {
            this.$('input[type="text"]:first').focus();
        }
    });
});
