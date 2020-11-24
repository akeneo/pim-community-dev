'use strict';

define(['jquery', 'require-context'], function ($, requireContext) {
  var config = __moduleConfig;
  var controllers = config.controllers || {};
  var defaultController = config.defaultController;

  return {
    /**
     * Get the controller for the given name
     *
     * @param {String} name
     *
     * @return {Promise}
     */
    get: function (name) {
      return new Promise(resolve => {
        const controller = controllers[name] || defaultController;
        const Controller = requireContext(controller.module);
        controller.class = Controller;
        resolve(controller);
      })
    },
  };
});
