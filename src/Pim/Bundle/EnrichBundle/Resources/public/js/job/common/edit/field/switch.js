
/**
 * Switch view extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseField from 'pim/job/common/edit/field/field'
import fieldTemplate from 'pim/template/export/common/edit/field/switch'
import 'bootstrap.bootstrapswitch'
export default BaseField.extend({
  fieldTemplate: _.template(fieldTemplate),
  events: {
    'change input': 'updateState'
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    BaseField.prototype.render.apply(this, arguments)

    this.$('.switch').bootstrapSwitch()
  },

  /**
   * Get the field dom value
   *
   * @return {string}
   */
  getFieldValue: function () {
    return this.$('input[type="checkbox"]').prop('checked')
  }
})
