
import $ from 'jquery'
import Backbone from 'backbone'
import Routing from 'routing'
import mediator from 'oro/mediator'
import CacheInvalidator from 'pim/cache-invalidator'
import ProductManager from 'pim/product-manager'
export default Backbone.Model.extend({
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
   *
   * @return {Promise}
   */
  fetch: function (identifier) {
    return $.getJSON(Routing.generate(this.options.urls.get, {
      id: identifier
    }))
      .then(function (product) {
        var cacheInvalidator = new CacheInvalidator()
        cacheInvalidator.checkStructureVersion(product)

        return ProductManager.generateMissing(product)
      })
      .then(function (product) {
        mediator.trigger('pim_enrich:form:product:post_fetch', product)

        return product
      })
      .promise()
  }
})
