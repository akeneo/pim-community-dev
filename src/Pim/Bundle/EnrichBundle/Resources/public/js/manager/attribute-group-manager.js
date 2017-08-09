'use strict';

define(
    ['jquery', 'underscore', 'pim/fetcher-registry', 'pim/attribute-manager'],
    function ($, _, FetcherRegistry, AttributeManager) {
    return {
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
                        result[attributeGroup.code] = attributeGroup;
                    }

                    return result;
                }, {});
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
        }
    };
});
