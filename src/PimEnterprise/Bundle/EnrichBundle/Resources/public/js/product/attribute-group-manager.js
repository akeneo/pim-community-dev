'use strict';

define(
    [
        'jquery',
        'underscore',
        'pimenrich/js/product/attribute-group-manager',
        'pim/config-manager',
        'pim/attribute-manager',
        'pim/permission-manager'
    ],
    function ($, _, AttributeGroupManager, ConfigManager, AttributeManager, PermissionManager) {
        return _.extend({}, AttributeGroupManager, {
            getAttributeGroupsForProduct: function (product) {
                var promise = $.Deferred();

                $.when(
                    ConfigManager.getEntityList('attributegroups'),
                    AttributeManager.getAttributesForProduct(product),
                    PermissionManager.getPermissions()
                ).done(_.bind(function (attributeGroups, productAttributes, permissions) {
                    var activeAttributeGroups = {};
                    _.each(attributeGroups, function (attributeGroup) {
                        if (_.intersection(attributeGroup.attributes, productAttributes).length > 0) {
                            /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
                            if (_.findWhere(permissions.attribute_groups, {code: attributeGroup.code}).view) {
                                activeAttributeGroups[attributeGroup.code] = attributeGroup;
                            }
                            /* jscs:enable requireCamelCaseOrUpperCaseIdentifiers */
                        }
                    });

                    promise.resolve(activeAttributeGroups);
                }, this));

                return promise.promise();
            }
        });
    }
);
