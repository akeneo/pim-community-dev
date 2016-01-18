'use strict';
/**
 * Metric field
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
        'text!pim/template/product/field/metric',
        'jquery.select2'
        ], function ($, Field, _, FetcherRegistry, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change .field-input:first .data, .field-input:first .unit': 'updateModel'
        },
        renderInput: function (context) {
            var $element = $(this.fieldTemplate(context));
            $element.find('.unit').select2('destroy').select2();

            return $element;
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('measure').fetchAll()
            ).then(function (templateContext, measures) {
                templateContext.measures = measures;

                return templateContext;
            });
        },
        setFocus: function () {
            this.$('.data:first').focus();
        },
        updateModel: function () {
            var data = this.$('.field-input:first .data').val();
            var numericValue = -1 !== data.indexOf('.') ? parseFloat(data) : parseInt(data);

            if (!_.isNaN(numericValue)) {
                data = numericValue;
            }

            var unit = this.$('.field-input:first .unit').select2('val');

            this.setCurrentValue({
                unit: '' !== unit ? unit : this.attribute.default_metric_unit,
                data: '' !== data ? data : null
            });
        }
    });
});
