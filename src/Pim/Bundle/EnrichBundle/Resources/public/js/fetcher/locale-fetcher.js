import $ from 'jquery'
import _ from 'underscore'
import BaseFetcher from 'pim/base-fetcher'
import Routing from 'routing'

export default BaseFetcher.extend({
  entityActivatedListPromise: null,
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    this.options = options || {}
  },

  /**
   * Fetch all activated locales.
   *
   * @return {Promise}
   */
  fetchActivated: function () {
    if (!this.entityActivatedListPromise) {
      if (!_.has(this.options.urls, 'list')) {
        return $.Deferred().reject().promise()
      }

      this.entityActivatedListPromise = $.getJSON(
        Routing.generate(this.options.urls.list),
        {
          activated: true
        }
      ).then(_.identity).promise()
    }

    return this.entityActivatedListPromise
  },

  /**
   * {inheritdoc}
   */
  clear: function () {
    this.entityActivatedListPromise = null

    BaseFetcher.prototype.clear.apply(this, arguments)
  }
})
