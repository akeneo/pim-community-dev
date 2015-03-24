"use strict";

define([
        'pim/field',
        'underscore',
        'pim/config-manager',
        'text!pim/template/product/field/price-collection'
    ],
    function (Field, _, ConfigManager, fieldTemplate) {
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
            console.log(event.currentTarget);

            var data = [];
            _.each($(event.currentTarget).parents('.price-collection-field').find('.price-input'), function(element) {
                var input = $(element).children('input');

                data.push({'data': input.val(), 'currency': input.data('currency')});
            });

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
