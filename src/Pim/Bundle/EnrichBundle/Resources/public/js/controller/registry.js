'use strict';

define(
    ['jquery', 'module'],
    function ($, module) {
        var controllers       = module.config().controllers || {};
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
                var deferred = $.Deferred();

                var controller = controllers[name] || defaultController;

                require([controller.module], function (Controller) {
                    controller.class = Controller;

                    deferred.resolve(controller);
                });

                return deferred.promise();
            }
        };
    }
);
