

import $ from 'jquery';
import requireContext from 'require-context';
var config            = __moduleConfig
var controllers       = config.controllers || {}
var defaultController = config.defaultController

export default {
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

