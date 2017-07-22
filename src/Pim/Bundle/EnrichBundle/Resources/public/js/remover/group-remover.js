/**
 * Module to remove product group
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseRemover from 'pim/remover/base'
import Routing from 'routing'

export default _.extend({}, BaseRemover, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (code) {
    return Routing.generate(__moduleConfig.url, {
      code: code
    })
  }
})
