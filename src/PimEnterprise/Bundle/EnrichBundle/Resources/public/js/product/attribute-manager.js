"use strict";

define(
    [
        'underscore',
        'pimenrich/js/product/attribute-manager',
        'pimenrich/js/product/attribute-group-manager',
        'pim/config-manager',
        'pim/permission-manager'
    ],
    function (_, AttributeManager, AttributeGroupManager, ConfigManager, PermissionManager) {
        return _.extend({}, AttributeManager, {
            getAttributesForProduct: function(product) {
                var promise = $.Deferred();

                $.when(
                    ConfigManager.getEntityList('families'),
                    ConfigManager.getEntityList('attributegroups'),
                    PermissionManager.getPermissions()
                ).done(function (families, groups, permissions) {
                    var familyAttributes = [];
                    if (product.family) {
                        familyAttributes = _.filter(
                            families[product.family].attributes,
                            function (attributeCode) {
                                var group = AttributeGroupManager.getAttributeGroupForAttribute(groups, attributeCode);

                                return _.findWhere(permissions.attribute_groups, {code: group}).view;
                            }
                        );
                    }

                    promise.resolve(_.union(_.keys(product.values), familyAttributes));
                });

                return promise.promise();
            },
            getOptionalAttributes: function(product) {
                var promise = $.Deferred();

                $.when(
                    ConfigManager.getEntityList('attributes'),
                    this.getAttributesForProduct(product),
                    PermissionManager.getPermissions()
                ).done(function(attributes, productAttributes, permissions) {
                    attributes = _.filter(attributes, function (attribute) {
                        return _.findWhere(permissions.attribute_groups, {code: attribute.group}).edit;
                    });

                    var optionalAttributes = _.map(
                        _.difference(_.pluck(attributes, 'code'), productAttributes),
                        function (attributeCode) {
                            return _.findWhere(attributes, {code: attributeCode});
                        }
                    );

                    promise.resolve(optionalAttributes);
                });

                return promise.promise();
            }
        });
    }
);
