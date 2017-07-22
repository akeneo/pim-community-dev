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
  configure: function () {
    this.listenTo(this.getRoot(), this.getRoot().preUpdateEventName, this.onPreUpdate.bind(this))

    return BaseField.prototype.configure.apply(this, arguments)
  },

  /**
   * Attribute must not be scopable if it has unique values.
   */
  onPreUpdate: function (data) {
    if (undefined !== data.unique && data.unique === true) {
      var newData = {}
      newData[this.fieldName] = false

      this.setData(newData, {
        silent: true
      })
    }
  },

  /**
   * {@inheritdoc}
   *
   * This field shouldn't be editable if the attribute has unique values.
   */
  isReadOnly: function () {
    return BaseField.prototype.isReadOnly.apply(this, arguments) ||
      (undefined !== this.getFormData().unique && this.getFormData().unique === true)
  }
})
