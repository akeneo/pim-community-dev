'use strict';

define(['jquery', 'routing'], ($, Routing) => {
  var contextData = {};

  return {
    /**
     * Fetches data from the back then stores it.
     *
     * @returns {Promise}
     */
    initialize: () => {
      return fetch(Routing.generate('pim_user_security_rest_get')).then(async response => (contextData = await response.json()));
    },

    /**
     * Returns the value corresponding to the specified key.
     *
     * @param {String} key
     *
     * @returns {*}
     */
    get: key => contextData[key],

    /**
     * Shortcut to test if an ACL is granted for the current user.
     *
     * @param {String} acl
     */
    isGranted: acl => contextData[acl] === true,
  };
});
