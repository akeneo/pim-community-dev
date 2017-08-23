'use strict';

define(
    ['jquery', 'routing'],
    ($, Routing) => {
        var contextData = {};

        return {
            /**
             * Fetches data from the back then stores it.
             *
             * @returns {Promise}
             */
            initialize: () => {
                return $.get(Routing.generate('pim_user_security_rest_get'))
                    .then(response => contextData = response);
            },

            /**
             * Returns the value corresponding to the specified key.
             *
             * @param {String} key
             *
             * @returns {*}
             */
            get: key => contextData[key],

            /**
             * Shortcut to test if an ACL is granted for the current user.
             *
             * @param {String} acl
             */
            isGranted: acl => contextData[acl] === true
        };
    }
);
