'use strict';

define([
        'jquery',
        'module',
        'routing'
    ], function (
        $,
        module,
        Routing
    ) {
        return {
            /**
             * Remove the given datagridView.
             * Return the DELETE request promise.
             *
             * @param {object} datagridView
             *
             * @returns {Promise}
             */
            remove: function (datagridView) {
                var removeRoute = Routing.generate(module.config().url, {identifier: datagridView.id});

                return $.ajax({url: removeRoute, type: 'DELETE'});
            }
        };
    }
);
