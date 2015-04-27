'use strict';

define([
        'jquery',
        'pim/field',
        'underscore',
        'pim/entity-manager',
        'text!pim/template/product/field/metric',
        'jquery.select2'
        ], function ($, Field, _, EntityManager, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        fieldType: 'metric',
        events: {
            'change .data, .unit': 'updateModel'
        },
        renderInput: function (context) {
            var $element = $(this.fieldTemplate(context));
            $element.find('.unit').select2('destroy').select2();

            return $element;
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                EntityManager.getRepository('measure').findAll()
            ).then(function (templateContext, measures) {
                templateContext.measures = measures;

                return templateContext;
            });
        },
        updateModel: function () {
            var data = this.$('.data').val();
            var unit = this.$('.unit option:selected').val();

            this.setCurrentValue({
                unit: '' !== unit ? unit : this.attribute.default_metric_unit,
                data: '' !== data ? data : null
            });
        }
    });
});
