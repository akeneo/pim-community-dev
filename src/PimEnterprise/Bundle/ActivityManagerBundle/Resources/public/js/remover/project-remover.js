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
             * Remove the given project.
             * Return the DELETE request promise.
             *
             * @param {object} project
             *
             * @returns {Promise}
             */
            remove: function (project) {
                var removeRoute = Routing.generate(module.config().url, {identifier: project.code});

                return $.ajax({url: removeRoute, type: 'DELETE'});
            }
        };
    }
);
