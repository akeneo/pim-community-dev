

import $ from 'jquery';
import Routing from 'routing';
export default {
  /**
   * Remove the given datagridView.
   * Return the DELETE request promise.
   *
   * @param {object} datagridView
   *
   * @returns {Promise}
   */
  remove: function(datagridView) {
    var removeRoute = Routing.generate(__moduleConfig.url, {
      identifier: datagridView.id
    });

    return $.ajax({
      url: removeRoute,
      type: 'DELETE'
    });
  }
};

