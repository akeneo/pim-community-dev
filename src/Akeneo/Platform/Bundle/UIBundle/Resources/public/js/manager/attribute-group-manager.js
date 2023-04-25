'use strict';

define(['jquery', 'underscore', 'pim/fetcher-registry'], function ($, _, FetcherRegistry) {
  return {
    /**
     * Get all the attribute group for the given product
     *
     * @param {Object} product
     *
     * @return {Promise}
     */
    getAttributeGroupsForObject: function (product) {
      return FetcherRegistry.getFetcher('attribute-group')
          .search({forAttributeCodes: _.keys(product.values)})
          .then((attributeGroups) => {
            return attributeGroups;
          });
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
      var result = null;

      _.each(attributeGroups, function (attributeGroup) {
        if (-1 !== attributeGroup.attributes.indexOf(attributeCode)) {
          result = attributeGroup.code;
        }
      });

      return result;
    },
  };
});
