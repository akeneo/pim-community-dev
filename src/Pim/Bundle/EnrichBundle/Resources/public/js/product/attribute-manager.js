"use strict";

define(['pim/config-manager'], function (ConfigManager) {
    return {
        getAttributeGroupsForProduct: function(product)
        {
            var promise = new $.Deferred();

            $.when(
                ConfigManager.getEntityList('attributegroups'),
                this.getAttributesForProduct(product)
            ).done(function(attributeGroups, productAttributes) {
                var activeAttributeGroups = {};
                _.each(attributeGroups, function(attributeGroup) {
                    if (_.intersection(attributeGroup.attributes, productAttributes).length > 0) {
                        activeAttributeGroups[attributeGroup.code] = attributeGroup;
                    }
                });

                promise.resolve(activeAttributeGroups);
            });

            return promise.promise();
        },
        getAttributesForProduct: function(product) {
            var promise = new $.Deferred();

            ConfigManager.getEntityList('families').done(function (families) {
                promise.resolve(
                    !product.family ?
                    _.keys(product.values) :
                    _.union(_.keys(product.values), families[product.family].attributes)
                );
            });

            return promise.promise();
        },
        getOptionalAttributes: function(product) {
            var promise = new $.Deferred();

            $.when(ConfigManager.getEntityList('attributes'), this.getAttributesForProduct(product))
                .done(function(attributes, productAttributes) {
                    promise.resolve(_.difference(_.keys(attributes), productAttributes));
                });

            return promise.promise();
        }
    };
});
