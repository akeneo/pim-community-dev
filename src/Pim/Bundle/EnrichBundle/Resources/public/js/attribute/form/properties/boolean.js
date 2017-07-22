/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseField from 'pim/attribute-edit-form/properties/field'
import template from 'pim/template/attribute/tab/properties/boolean'

export default BaseField.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    if (!_.has(this.getFormData(), this.fieldName) && _.has(this.config, 'defaultValue')) {
      this.updateModel(this.config.defaultValue)
    }

    return this.template(_.extend(templateContext, {
      value: this.getFormData()[this.fieldName],
      labels: {
        on: __('switch_on'),
        off: __('switch_off')
      }
    }))
  },

  /**
   * {@inheritdoc}
   */
  postRender: function () {
    this.$('.switch').bootstrapSwitch()
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).is(':checked')
  }
})
