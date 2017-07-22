
import $ from 'jquery'
import BaseFetcher from 'pim/base-fetcher'
import Routing from 'routing'
import mediator from 'oro/mediator'
import CacheInvalidator from 'pim/cache-invalidator'
import ProductManager from 'pim/product-manager'
export default BaseFetcher.extend({
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    this.options = options || {}
  },

  /**
   * Fetch an element based on its identifier
   *
   * @param {string} identifier
   * @param {Object} options
   *
   * @return {Promise}
   */
  fetch: function (identifier, options) {
    options = options || {}

    options.code = identifier
    var promise = BaseFetcher.prototype.fetch.apply(this, [identifier, options])

    return promise
      .then(function (variantGroup) {
        var cacheInvalidator = new CacheInvalidator()
        cacheInvalidator.checkStructureVersion(variantGroup)

        return variantGroup
      })
      .then(ProductManager.generateMissing.bind(ProductManager))
      .then(function (variantGroup) {
        mediator.trigger('pim_enrich:form:variant_group:post_fetch', variantGroup)

        return variantGroup
      })
  }
})
