
/**
 * Family label translation fields view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import BaseTranslation from 'pim/common/properties/translation'
import SecurityContext from 'pim/security-context'
export default BaseTranslation.extend({
  /**
   * {@inheritdoc}
   */
  isReadOnly: function () {
    return !SecurityContext.isGranted('pim_enrich_family_edit_properties')
  }
})
