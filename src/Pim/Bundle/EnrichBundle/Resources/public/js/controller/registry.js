'use strict';

define(
    ['jquery', 'require-context', 'controllers'],
    function ($, requireContext, controllers) {
        var controllers       = controllers || {}
        var defaultController = {
            module: 'pim/controller/template'
        }

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
