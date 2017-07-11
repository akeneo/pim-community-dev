
import $ from 'jquery'
import _ from 'underscore'
import FetcherRegistry from 'pim/fetcher-registry'
import AttributeManager from 'pim/attribute-manager'
export default {
        /**
         * Get all the attribute group for the given object
         *
         * @param {Object} object
         *
         * @return {Promise}
         */
  getAttributeGroupsForObject: function (object) {
    return $.when(
                FetcherRegistry.getFetcher('attribute-group').fetchAll(),
                AttributeManager.getAttributes(object)
            ).then(function (attributeGroups, ObjectAttributes) {
              return _.reduce(attributeGroups, function (result, attributeGroup) {
                if (_.intersection(attributeGroup.attributes, ObjectAttributes).length > 0) {
                  result[attributeGroup.code] = attributeGroup
                }

                return result
              }, {})
            })
  },

        /**
         * Get attribute group values filtered from the whole list
         *
         * @param {Object} values
         * @param {String} attributeGroup
         *
         * @return {Object}
         */
  getAttributeGroupValues: function (values, attributeGroup) {
    var matchingValues = {}
    if (!attributeGroup) {
      return matchingValues
    }

    _.each(attributeGroup.attributes, function (attributeCode) {
      if (values[attributeCode]) {
        matchingValues[attributeCode] = values[attributeCode]
      }
    })

    return matchingValues
  },

        /**
         * Get the attribute group for the given attribute
         *
         * @param {Array} attributeGroups
         * @param {String} attributeCode
         *
         * @return {String}
         */
  getAttributeGroupForAttribute: function (attributeGroups, attributeCode) {
    var result = null

    _.each(attributeGroups, function (attributeGroup) {
      if (attributeGroup.attributes.indexOf(attributeCode) !== -1) {
        result = attributeGroup.code
      }
    })

    return result
  }
}
