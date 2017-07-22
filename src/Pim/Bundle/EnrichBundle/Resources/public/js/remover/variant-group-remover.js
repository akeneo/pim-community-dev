
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
