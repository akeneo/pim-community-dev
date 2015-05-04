'use strict';

define(
    ['jquery', 'underscore', 'pim/entity-manager', 'pim/attribute-manager'],
    function ($, _, EntityManager, AttributeManager) {
    return {
        getAttributeGroupsForProduct: function (product) {
            return $.when(
                EntityManager.getRepository('attributeGroup').findAll(),
                AttributeManager.getAttributesForProduct(product)
            ).then(function (attributeGroups, productAttributes) {
                var activeAttributeGroups = {};
                _.each(attributeGroups, function (attributeGroup) {
                    if (_.intersection(attributeGroup.attributes, productAttributes).length > 0) {
                        activeAttributeGroups[attributeGroup.code] = attributeGroup;
                    }
                });

                return activeAttributeGroups;
            });
        },
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
        getAttributeGroupForAttribute: function (attributeGroups, attribute) {
            var result = null;

            _.each(attributeGroups, function (attributeGroup) {
                if (-1 !== attributeGroup.attributes.indexOf(attribute)) {
                    result = attributeGroup.code;
                }
            });

            return result;
        }
    };
});
