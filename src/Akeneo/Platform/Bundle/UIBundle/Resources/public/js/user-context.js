'use strict';

/**
 * @deprecated Legacy module ID kept for backward compatibility.
 * The implementation lives in @akeneo-pim-community/shared; this shim adapts promises for legacy callers and
 * delegates the event methods so that legacy views and React code share the same listeners.
 */
const $ = require('jquery');
const {userContext} = require('@akeneo-pim-community/shared');

const legacyUserContext = {
  initialize: () => $.when(userContext.initialize()),
  refresh: () => $.when(userContext.refresh()),
  get: key => userContext.get(key),
  set: (key, value, options) => userContext.set(key, value, options),
};

['on', 'bind', 'once', 'off', 'unbind', 'trigger'].forEach(method => {
  legacyUserContext[method] = (...args) => {
    userContext[method](...args);

    return legacyUserContext;
  };
});

module.exports = legacyUserContext;
