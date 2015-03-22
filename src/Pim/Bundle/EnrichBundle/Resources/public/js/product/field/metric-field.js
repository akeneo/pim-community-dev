"use strict";

define([
        'pim/field',
        'underscore',
        'pim/config-manager',
        'text!pim/template/product/field/metric',
        'jquery.select2'
        ], function (Field, _, ConfigManager, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'metric',
        events: {
            'change .data, .unit': 'updateModel'
        },
        renderInput: function(context) {
            var $element = $(this.fieldTemplate(context));
            $element.find('.unit').select2('destroy').select2({});

            return $element;
        },
        getTemplateContext: function() {
            var promise = $.Deferred();

            $.when(Field.prototype.getTemplateContext.apply(this, arguments), ConfigManager.getEntityList('measures'))
                .done(function(templateContext, measures) {
                    templateContext.measures = measures;

                    promise.resolve(templateContext);
                });

            return promise.promise();
        },
        updateModel: function () {
            var data = this.$('.data').val();
            this.setCurrentValue({
                unit: this.$('.unit option:selected').val(),
                data: '' !== data ? data : null
            });
        }
    });
});
