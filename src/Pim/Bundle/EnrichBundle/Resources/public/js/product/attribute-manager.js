"use strict";

define(['pim/config-manager', 'pim/channel-manager'], function (ConfigManager, ChannelManager) {
    return {
        getAttributeGroupsForProduct: function(product)
        {
            var promise = $.Deferred();

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
            var promise = $.Deferred();

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
            var promise = $.Deferred();

            $.when(ConfigManager.getEntityList('attributes'), this.getAttributesForProduct(product))
                .done(function(attributes, productAttributes) {
                    promise.resolve(_.difference(_.keys(attributes), productAttributes));
                });

            return promise.promise();
        },
        isOptional: function(attribute, product, families) {
            return !product.family ? true : !_.contains(families[product.family].attributes, attribute);
        },
        getAttribute: function(attributeCode) {
            var promise = $.Deferred();

            ConfigManager.getEntity('attributes', attributeCode).done(_.bind(function(attribute) {
                promise.resolve(attribute);
            }, this));

            return promise.promise();
        },
        getEmptyValue: function(attribute)
        {
            switch(attribute.type) {
                case 'pim_catalog_boolean':
                case 'pim_catalog_date':
                case 'pim_catalog_number':
                case 'pim_catalog_file':
                case 'pim_catalog_image':
                case 'pim_catalog_price_collection':
                case 'pim_catalog_simpleselect':
                case 'pim_catalog_identifier':
                    return null;
                case 'pim_catalog_metric':
                    return {
                        'data': null,
                        'unit': attribute.default_metric_unit
                    };
                case 'pim_catalog_multiselect':
                    return [];
                case 'pim_catalog_text':
                case 'pim_catalog_textarea':
                    return '';
                default:
                    throw new Error(JSON.stringify(attribute));
            }
        },
        getValue: function(values, attribute, locale, scope) {
            locale = attribute.localizable ? locale : null;
            scope  = attribute.scopable ? scope : null;

            var result = null;

            _.each(values, _.bind(function(value) {
                if (value.scope === scope && value.locale === locale) {
                    result = value;
                }
            }, this));

            return result ? result : {
                'scope':  scope,
                'locale': locale,
                'value':  this.getEmptyValue(attribute)
            };
        }
    };
});
