'use strict';

define([
        'jquery',
        'config',
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
                var removeRoute = Routing.generate(module.config(__moduleName).url, {identifier: project.code});

                return $.ajax({url: removeRoute, type: 'DELETE'});
            }
        };
    }
);
