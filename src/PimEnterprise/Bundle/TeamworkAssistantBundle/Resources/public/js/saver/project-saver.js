'use strict';

/**
 * Saver for Project
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
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
             * Save the given Project represented by params.
             * Return the POST request promise.
             *
             * @param {object} project Must be all project properties that you want to hydrate
             *
             * @returns {Promise}
             */
            save: function (project) {
                var saveRoute = Routing.generate(module.config(__moduleName).url);

                return $.post(saveRoute, {project: project});
            }
        };
    }
);
