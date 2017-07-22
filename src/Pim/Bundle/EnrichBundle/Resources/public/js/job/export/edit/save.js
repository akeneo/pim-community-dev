
/**
 * Save extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import BaseSave from 'pim/job-instance-edit-form/save'
import JobInstanceSaver from 'pim/saver/job-instance-export'
export default BaseSave.extend({
  /**
   * {@inheritdoc}
   */
  getJobInstanceSaver: function () {
    return JobInstanceSaver
  }
})
