/**
 * Delete extension for channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import ChannelRemover from 'pim/remover/channel'

export default DeleteForm.extend({
  remover: ChannelRemover
})
