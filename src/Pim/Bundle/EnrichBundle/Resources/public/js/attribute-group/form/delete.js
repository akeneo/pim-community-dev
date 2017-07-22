/**
 * Delete extension for attribute group
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import AttributeGroupRemover from 'pim/remover/attribute-group'

export default DeleteForm.extend({
  remover: AttributeGroupRemover
})
