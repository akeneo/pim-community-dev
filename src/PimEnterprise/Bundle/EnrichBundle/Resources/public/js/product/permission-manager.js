'use strict';

define(
    [
        'jquery',
        'underscore',
        'routing'
    ],
    function ($, _, Routing) {
        var promise = null;

        return {
            getPermissions: function () {
                if (!promise) {
                    promise = $.getJSON(
                        Routing.generate('pimee_security_permissions_rest')
                    ).then(_.identity).promise();
                }

                return promise;
            }
        };
    }
);
