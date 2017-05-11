'use strict';

define(
    ['jquery', 'require-context', 'controllers', 'module-config'],
    function ($, requireContext, controllers, moduleConfig) {
        var controllers       = controllers || {}
        var defaultController = moduleConfig.defaultController;

        return {
            /**
             * Get the controller for the given name
             *
             * @param {String} name
             *
             * @return {Promise}
             */
            get: function (name) {
                var deferred = $.Deferred();
                var controller = controllers[name] || defaultController;
                var Controller = requireContext(controller.module)
                controller.class = Controller;
                deferred.resolve(controller);

                return deferred.promise();
            }
        };
    }
);
