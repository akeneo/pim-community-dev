import $ from 'jquery'
import mediator from 'oro/mediator'
import Routing from 'routing'

export default {
  /**
   * Save an entity
   *
   * @param {String} code
   * @param {Object} data
   *
   * @return {Promise}
   */
  save: function (code, data, method) {
    return $.ajax({
      /* todo: remove ternary when all instances using this module will provide method parameter */
      type: typeof method === 'undefined' ? 'POST' : method,
      url: this.getUrl(code),
      data: JSON.stringify(data)
    }).then(function (entity) {
      mediator.trigger('pim_enrich:form:entity:post_save', entity)

      return entity
    })
  },

  /**
   * Get the entity url
   * @param {String} code
   *
   * @return {String}
   */
  getUrl: function (code) {
    return Routing.generate(__moduleConfig.url, {
      code: code
    })
  }
}
