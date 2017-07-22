/**
 * Product label extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import Label from 'pim/form/common/label'
import UserContext from 'pim/user-context'

export default Label.extend({
  /**
   * Provide the object label
   * @return {String}
   */
  getLabel: function () {
    var meta = this.getFormData().meta

    if (meta && meta.label) {
      return meta.label[UserContext.get('catalogLocale')]
    }

    return null
  }
})
