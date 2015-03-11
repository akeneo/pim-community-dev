"use strict";

define(['routing', 'pim/config-manager'], function (Routing, ConfigManager) {
    return {
        getAttributeGroupsForProduct: function(product)
        {
            var promise = new $.Deferred();

            $.when(
                ConfigManager.getEntityList('attributegroups'),
                this.getAttributesForProduct(product)
            ).done(_.bind(function(attributeGroups, productAttributes) {
                var activeAttributeGroups = {};
                _.each(attributeGroups, function(attributeGroup) {
                    if (_.intersection(attributeGroup.attributes, productAttributes).length > 0) {
                        activeAttributeGroups[attributeGroup.code] = attributeGroup;
                    }
                });

                promise.resolve(activeAttributeGroups);
            }, this));

            return promise.promise();
        },
        getAttributesForProduct: function(product) {
            var promise = new $.Deferred();

            ConfigManager.getEntityList('families').done(_.bind(function (families) {
                promise.resolve(!product.family ? _.keys(product.values) : families[product.family].attributes);
            }, this));

            return promise.promise();
        }
    };
});
