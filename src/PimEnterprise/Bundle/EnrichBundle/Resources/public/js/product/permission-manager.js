'use strict';

define(
    [
        'jquery',
        'routing'
    ],
    function ($, Routing) {
        var permissions = {};
        var promise = null;

        return {
            getPermissions: function () {
                if (promise) {
                    return promise.promise();
                }

                promise = $.Deferred();

                $.getJSON(
                    Routing.generate('pimee_security_permissions_rest')
                ).done(function (data) {
                    permissions = data;
                    promise.resolve(permissions);
                });

                return promise.promise();
            }
        };
    }
);
