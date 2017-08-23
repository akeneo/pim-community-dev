'use strict';

define(
    ['jquery', 'underscore', 'pim/fetcher-registry'],
    function ($, _, FetcherRegistry) {
    return {
        /**
         * Get all the attribute group for the given object
         *
         * @param {Object} object
         *
         * @return {Promise}
         */
        getAttributeGroupsForObject: function (object) {
            return FetcherRegistry.getFetcher('attribute-group').fetchAll()
                .then(function (attributeGroups) {
                    return _.values(attributeGroups).reduce((result, attributeGroup) => {
                        if (_.intersection(attributeGroup.attributes, _.keys(object.values)).length > 0) {
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
