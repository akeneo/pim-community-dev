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
  'oro/translator',
  'pim/fetcher-registry',
  'pim/template/product/field/metric',
  'pim/initselect2',
  'pim/i18n',
  'pim/user-context',
], function ($, Field, _, __, FetcherRegistry, fieldTemplate, initSelect2, i18n, UserContext) {
  return Field.extend({
    fieldTemplate: _.template(fieldTemplate),
    events: {
      'change .field-input:first .data, .field-input:first .unit': 'updateModel',
    },
    renderInput: function (context) {
      const $element = $(this.fieldTemplate(_.extend({}, context, {__: __})));
      initSelect2.init($element.find('.unit'));

      return $element;
    },
    getTemplateContext: function () {
      return $.when(
        Field.prototype.getTemplateContext.apply(this, arguments),
        FetcherRegistry.getFetcher('measure').fetchAll()
      ).then(function (templateContext, measures) {
        const measurementFamily = measures.find(family => family.code === templateContext.attribute.metric_family);
        templateContext.i18n = i18n;
        templateContext.units = measurementFamily.units;
        templateContext.uiLocale = UserContext.get('uiLocale');

        return templateContext;
      });
    },
    setFocus: function () {
      this.$('.data:first').focus();
    },
    updateModel: function () {
      var amount = this.$('.field-input:first .data').val();
      var unit = this.$('.field-input:first .unit').select2('val');

      this.setCurrentValue({
        unit: '' !== unit ? unit : this.attribute.default_metric_unit,
        amount: '' !== amount ? amount : null,
      });
    },
  });
});
