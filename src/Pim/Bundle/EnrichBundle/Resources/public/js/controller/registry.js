'use strict';

// Remove this
define(
    ['jquery', 'module-config', 'controllers'],
    function ($, module, controllers) {
        // var controllers       = module.config().controllers || {};
        var defaultController = module.config().defaultController;

        return {
            /**
             * Get the controller for the given name
             *
             * @param {String} name
             *
             * @return {Promise}
             */
            get: function (name) {
                // var deferred = $.Deferred();
                return controllers[name] || defaultController;
                // deferred.resolve(controller);
                //
                // return deferred.promise();
            }
        };
    }
);
