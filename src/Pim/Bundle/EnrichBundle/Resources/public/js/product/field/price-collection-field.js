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
            // var data = event.currentTarget.value;
            // this.setCurrentValue(data);
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
