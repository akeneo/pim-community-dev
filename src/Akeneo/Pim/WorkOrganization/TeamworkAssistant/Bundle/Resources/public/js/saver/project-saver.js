'use strict';

/**
 * Saver for Project
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(['jquery', 'routing'], function($, Routing) {
  return {
    /**
     * Save the given Project represented by params.
     * Return the POST request promise.
     *
     * @param {object} project Must be all project properties that you want to hydrate
     *
     * @returns {Promise}
     */
    save: function(project) {
      var saveRoute = Routing.generate(__moduleConfig.url);

      return $.post(saveRoute, {project: project});
    },
  };
});
