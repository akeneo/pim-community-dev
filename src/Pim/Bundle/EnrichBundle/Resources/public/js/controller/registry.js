'use strict';

define(function (require, exports, module) {
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
