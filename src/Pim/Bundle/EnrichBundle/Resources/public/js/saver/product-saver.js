import _ from 'underscore'
import BaseSaver from 'pim/saver/base'
import Routing from 'routing'

export default _.extend({}, BaseSaver, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (id) {
    return Routing.generate(__moduleConfig.url, {
      id: id
    })
  }
})
