/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

import $ from 'jquery'
import _ from 'underscore'
import BaseField from 'pim/attribute-edit-form/properties/field'
import template from 'pim/template/attribute/tab/properties/text'
export default BaseField.extend({
  template: _.template(template),

  /**
   * {@inheritdoc}
   */
  renderInput: function (templateContext) {
    return this.template(_.extend(templateContext, {
      value: this.getFormData()[this.fieldName]
    }))
  },

  /**
   * {@inheritdoc}
   */
  getFieldValue: function (field) {
    return $(field).val()
  }
})
