'use strict';

define(['module', 'require'], function (module, require) {
    var $ = require('jquery');

    var controllers = module.config().controllers || {};
    var defaultController = module.config().defaultController;

    return {
        get: function (name) {
            var deferred = $.Deferred();

            var controller = controllers[name] || defaultController;

            require([controller], function (Controller) {
                deferred.resolve(Controller);
            });

            return deferred.promise();
        }
    };
});
