

/**
 * Family delete extension
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import FamilyRemover from 'pim/remover/family'
export default DeleteForm.extend({
    remover: FamilyRemover
})

