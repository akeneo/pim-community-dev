'use strict';

define(['jquery', 'routing'], function($, Routing) {
  return {
    /**
     * Remove the given project.
     * Return the DELETE request promise.
     *
     * @param {object} project
     *
     * @returns {Promise}
     */
    remove: function(project) {
      var removeRoute = Routing.generate(__moduleConfig.url, {identifier: project.code});

      return $.ajax({url: removeRoute, type: 'DELETE'});
    },
  };
});
