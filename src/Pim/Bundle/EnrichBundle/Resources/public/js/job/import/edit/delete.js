
/**
 * Delete extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import DeleteForm from 'pim/form/common/delete'
import JobInstanceRemover from 'pim/remover/job-instance-import'
export default DeleteForm.extend({
  remover: JobInstanceRemover
})
