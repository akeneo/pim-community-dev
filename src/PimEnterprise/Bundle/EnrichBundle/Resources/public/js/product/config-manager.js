'use strict';

define(
    [
        'underscore',
        'pimenrich/js/product/config-manager',
        'pim/permission-manager'
    ],
    function (_, ConfigManager, PermissionManager) {
        return _.extend({}, ConfigManager,
            {
                getEntityList: function (entityType)
                {
                    var promise = $.Deferred();

                    ConfigManager.getEntityList(entityType).done(function (entities) {
                        if ('locales' === entityType) {
                            PermissionManager.getPermissions().done(function (permissions) {
                                entities = _.filter(entities, function (localeCode) {
                                    return _.findWhere(permissions.locales, {code: localeCode}).view;
                                });
                                promise.resolve(entities);
                            });
                        } else {
                            promise.resolve(entities);
                        }
                    });

                    return promise.promise();
                }
            }
        );
    }
);
