'use strict';

/**
 * @deprecated Legacy module ID kept for backward compatibility.
 * The implementation lives in @akeneo-pim-community/shared; this shim only adapts promises for legacy callers
 * still chaining jQuery specific methods (done/fail/always).
 */
const $ = require('jquery');
const {securityContext} = require('@akeneo-pim-community/shared');

module.exports = {
  initialize: () => $.when(securityContext.initialize()),
  get: key => securityContext.get(key),
  isGranted: acl => securityContext.isGranted(acl),
};
