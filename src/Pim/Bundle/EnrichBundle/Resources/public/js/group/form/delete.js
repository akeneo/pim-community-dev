

/**
 * Delete extension for groups
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import GroupRemover from 'pim/remover/group'
export default DeleteForm.extend({
    remover: GroupRemover
})

