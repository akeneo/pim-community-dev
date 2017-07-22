import $ from 'jquery'
import _ from 'underscore'
import AttributeManager from 'pim/attribute-manager'
import FetcherRegistry from 'pim/fetcher-registry'

export default {
  productValues: null,
  doGenerateMissing: function (product) {
    return AttributeManager.getAttributes(product)
      .then(function (productAttributeCodes) {
        return $.when(
          FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(productAttributeCodes),
          FetcherRegistry.getFetcher('locale').fetchActivated(),
          FetcherRegistry.getFetcher('channel').fetchAll(),
          FetcherRegistry.getFetcher('currency').fetchAll(),
          FetcherRegistry.getFetcher('association-type').fetchAll()
        )
      })
      .then(function (attributes, locales, channels, currencies, associationTypes) {
        var oldValues = _.isArray(product.values) && product.values.length === 0 ? {} : product.values
        var newValues = {}

        _.each(attributes, function (attribute) {
          newValues[attribute.code] = AttributeManager.generateMissingValues(
            _.has(oldValues, attribute.code) ? oldValues[attribute.code] : [],
            attribute,
            locales,
            channels,
            currencies
          )
        })

        var associations = {}
        _.each(associationTypes, function (assocType) {
          associations[assocType.code] = AttributeManager.generateMissingAssociations(
            _.has(product.associations, assocType.code) ? product.associations[assocType.code] : {}
          )
        })

        product.values = newValues
        product.associations = associations

        return product
      })
  },
  generateMissing: function (product) {
    return this.doGenerateMissing(product)
  }
}
