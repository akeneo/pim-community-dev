'use strict';

/**
 * Backward compatibility layer for third party code still using `Backbone.history` directly.
 * Navigation is owned by `pim/router` (which no longer uses Backbone.Router), so `Backbone.history.navigate()`,
 * `Backbone.history.fragment` and `Backbone.history.start()` are forwarded to it.
 *
 * @deprecated Use `pim/router` (navigate, redirect, getFragment, forgetFragment) instead. To be removed with Backbone.
 */
const Backbone = require('backbone');
const router = require('pim/router');

module.exports = () => {
  Backbone.History.started = true;
  Backbone.history.navigate = (fragment, options) => router.navigate(fragment, options);
  Backbone.history.start = () => router.start();

  // Code listening to Backbone.Router.prototype "route" events is notified each time the router handles a URL
  router.on('route', (handler, args) => Backbone.Router.prototype.trigger('route', handler, args));

  Object.defineProperty(Backbone.history, 'fragment', {
    configurable: true,
    get: () => router.getFragment(),
    // Legacy code assigned a random fragment to force the next navigation to reload the page
    set: () => router.forgetFragment(),
  });
};
