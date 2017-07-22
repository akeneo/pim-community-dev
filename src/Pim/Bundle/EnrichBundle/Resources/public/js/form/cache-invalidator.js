import _ from 'underscore'
import BaseForm from 'pim/form'
import mediator from 'oro/mediator'
import FetcherRegistry from 'pim/fetcher-registry'

export default BaseForm.extend({
  /**
   * {@inheritdoc}
   */
  configure: function () {
    _.each(__moduleConfig.events, function (event) {
      this.listenTo(mediator, event, this.checkStructureVersion)
    }.bind(this))

    this.listenTo(this.getRoot(), 'pim_enrich:form:cache:clear', this.clearCache)

    return BaseForm.prototype.configure.apply(this, arguments)
  },

  /**
   * Check if the given entity need e newer version of the cache
   *
   * @param {Object} entity
   */
  checkStructureVersion: function (entity) {
    if (entity.meta.structure_version !== this.getLocaleStructureVersion(entity.meta.model_type)) {
      this.clearCache()
    }

    this.setLocaleStructureVersion(entity.meta.model_type, entity.meta.structure_version)
  },

  /**
   * Get the in locale storage structure version
   *
   * @param {string} modelType
   *
   * @return {int}
   */
  getLocaleStructureVersion: function (modelType) {
    return parseInt(window.sessionStorage.getItem('structure_version_' + modelType))
  },

  /**
   * Set the current locale structure version in locale storage
   *
   * @param {string} modelType
   * @param {int}    structureVersion
   */
  setLocaleStructureVersion: function (modelType, structureVersion) {
    window.sessionStorage.setItem('structure_version_' + modelType, structureVersion)
  },

  /**
   * Clear the cache for all fetchers
   */
  clearCache: function () {
    FetcherRegistry.clearAll()
  }
})
