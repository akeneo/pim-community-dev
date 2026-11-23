'use strict';

/**
 * @deprecated Legacy module ID kept for backward compatibility.
 * The matching logic lives in @akeneo-pim-community/shared (matchRoute).
 */
const Routing = require('routing');
const {matchRoute} = require('@akeneo-pim-community/shared');

module.exports = {
  match: url => matchRoute(Routing.getRoutes(), url, Routing.getBaseUrl()),
};
