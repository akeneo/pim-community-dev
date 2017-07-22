/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import BaseField from 'pim/attribute-edit-form/properties/boolean'

export default BaseField.extend({
  /**
   * {@inheritdoc}
   */
  updateModel: function () {
    BaseField.prototype.updateModel.apply(this, arguments)

    if (this.getFormData().is_locale_specific === false) {
      this.setData({
        available_locales: []
      }, {
        silent: true
      })
    }
  }
})
