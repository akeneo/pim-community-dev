
/**
 * Metric field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import Field from 'pim/field'
import _ from 'underscore'
import FetcherRegistry from 'pim/fetcher-registry'
import fieldTemplate from 'pim/template/product/field/metric'
import initSelect2 from 'pim/initselect2'
export default Field.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change .field-input:first .data, .field-input:first .unit': 'updateModel'
  },
  renderInput: function (context) {
    var $element = $(this.fieldTemplate(context))
    initSelect2.init($element.find('.unit'))

    return $element
  },
  getTemplateContext: function () {
    return $.when(
      Field.prototype.getTemplateContext.apply(this, arguments),
      FetcherRegistry.getFetcher('measure').fetchAll()
    ).then(function (templateContext, measures) {
      templateContext.measures = measures

      return templateContext
    })
  },
  setFocus: function () {
    this.$('.data:first').focus()
  },
  updateModel: function () {
    var amount = this.$('.field-input:first .data').val()
    var unit = this.$('.field-input:first .unit').select2('val')

    this.setCurrentValue({
      unit: unit !== '' ? unit : this.attribute.default_metric_unit,
      amount: amount !== '' ? amount : null
    })
  }
})
