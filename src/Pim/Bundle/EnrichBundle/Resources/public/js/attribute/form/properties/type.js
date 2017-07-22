/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseField from 'pim/attribute-edit-form/properties/field'
import template from 'pim/template/attribute/tab/properties/select'

export default BaseField.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    var value = this.getFormData()[this.fieldName]
    var choices = {}
    choices[value] = __('pim_enrich.entity.attribute.type.' + value)

    return this.template(_.extend(templateContext, {
      value: value,
      choices: choices,
      multiple: false,
      labels: {
        defaultLabel: ''
      }
    }))
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('select.select2').select2()
  }
})
