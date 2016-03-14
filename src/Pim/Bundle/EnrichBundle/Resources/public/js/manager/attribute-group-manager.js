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
                var activeAttributeGroups = {};
                _.each(attributeGroups, function (attributeGroup) {
                    if (_.intersection(attributeGroup.attributes, ObjectAttributes).length > 0) {
                        activeAttributeGroups[attributeGroup.code] = attributeGroup;
                    }
                });

                return activeAttributeGroups;
            });
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
            var matchingValues = {};
            if (!attributeGroup) {
                return matchingValues;
            }

            _.each(attributeGroup.attributes, function (attributeCode) {
                if (values[attributeCode]) {
                    matchingValues[attributeCode] = values[attributeCode];
                }
            });

            return matchingValues;
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
