'use strict';

// Remove this
define(
    ['jquery', 'module-config', 'controllers'],
    function ($, module) {
        // var controllers       = module.config().controllers || {};
        var defaultController = module.config().defaultController;
        console.log('controller registry')

        return {
            /**
             * Get the controller for the given name
             *
             * @param {String} name
             *
             * @return {Promise}
             */
            get: function (name) {
                console.log('get from bundle loader', require(['bundle-loader!.' + name]))

                return require(['bundle-loader!.' + name]) || defaultController;
                // var deferred = $.Deferred();
                // return controllers[name] || defaultController;
                // deferred.resolve(controller);
                //
                // return deferred.promise();
            }
        };
    }
);
