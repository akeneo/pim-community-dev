'use strict';

define([
        'jquery',
        'pim/field',
        'underscore',
        'pim/config-manager',
        'text!pim/template/product/field/price-collection'
    ],
    function ($, Field, _, ConfigManager, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'price-collection',
        events: {
            'change input': 'updateModel'
        },
        renderInput: function(context) {
            return this.fieldTemplate(context);
        },
        updateModel: function (event) {
            var data = [];
            var $elements = $(event.currentTarget).parents('.price-collection-field').find('.price-input');
            _.each($elements, _.bind(function(element) {
                var input = $(element).children('input');

                var inputData = input.val();

                inputData = ('' !== inputData) ? parseFloat(inputData) : inputData;
                inputData = isNaN(inputData) || '' === inputData ? null : inputData;

                input.val(null === inputData ? input.defaultValue : inputData);
                data.push({'data': inputData, 'currency': input.data('currency')});
            }, this));

            this.setCurrentValue(data);
        },
        getTemplateContext: function() {
            var promise = $.Deferred();

            $.when(Field.prototype.getTemplateContext.apply(this, arguments), ConfigManager.getEntityList('currencies'))
                .done(function(templateContext, currencies) {
                    templateContext.currencies = currencies;

                    promise.resolve(templateContext);
                });

            return promise.promise();
        }
    });
});
