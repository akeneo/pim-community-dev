'use strict';

/**
 * Application router (`pim/router`).
 *
 * It resolves the hash URL to a Symfony route, then renders the controller registered for this route in the
 * `#container` element provided by the React application shell.
 *
 * It no longer depends on Backbone: navigation relies on the hash history of @akeneo-pim-community/shared, which
 * reproduces the Backbone.history behavior, and events rely on the shared Backbone.Events compatible emitter.
 * The public API (generate, redirect, redirectToRoute, reloadPage, loading mask, route events) is unchanged.
 */
const $ = require('jquery');
const {createHashHistory, withEvents} = require('@akeneo-pim-community/shared');
const __ = require('oro/translator');
const Routing = require('routing');
const RouteMatcher = require('pim/route-matcher');
const LoadingMask = require('oro/loading-mask');
const ControllerRegistry = require('pim/controller-registry');
const mediator = require('oro/mediator');
const ErrorView = require('pim/error');
const securityContext = require('pim/security-context');
const FeatureFlags = require('pim/feature-flags');

const history = createHashHistory(window);

const router = withEvents({
  DEFAULT_ROUTE: 'oro_default',
  indexRoute: __moduleConfig.indexRoute,
  loadingMask: null,
  currentController: null,

  /**
   * Starts listening to the URL and renders the current route.
   * The container (#container) and the loading mask container (.hash-loading-mask) must be in the DOM.
   */
  start: function () {
    if (history.isStarted()) {
      return;
    }

    this.loadingMask = new LoadingMask();
    this.loadingMask.render().$el.appendTo($('.hash-loading-mask'));

    this.listenTo(mediator, 'route_complete', this._processLinks);

    history.start(this.loadFragment.bind(this));
  },

  /**
   * @return {Boolean}
   */
  isStarted: function () {
    return history.isStarted();
  },

  /**
   * Called each time the URL fragment has to be rendered
   *
   * @param {String} fragment
   */
  loadFragment: function (fragment) {
    const handler = '' === fragment ? 'index' : 'defaultRoute';
    const result = 'index' === handler ? this.index() : this.defaultRoute(fragment);

    // Like Backbone.Router did, notify that a URL has been handled. Legacy views listen to it to unbind their events.
    this.trigger('route', handler, [fragment]);

    return result;
  },

  /**
   * Go to the index of the app
   */
  index: function () {
    return this.defaultRoute(this.generate(this.indexRoute, {freeTrialEnabled: FeatureFlags.isEnabled('free_trial')}));
  },

  /**
   * Render the given route
   *
   * @param {String} path
   */
  defaultRoute: function (path) {
    if (path.indexOf('/') !== 0) {
      path = '/' + path;
    }

    if (path.indexOf('|') !== -1) {
      path = path.substring(0, path.indexOf('|'));
    }

    const route = this.match(path);
    if (false === route) {
      return this.notFound();
    }
    if (this.DEFAULT_ROUTE === route.name) {
      return this.index();
    }

    this.showLoadingMask();
    this.triggerStart(route);

    ControllerRegistry.get(route.name).done(controller => {
      if (this.currentController) {
        this.currentController.remove();
      }

      $('#container').empty();
      const $view = $('<div class="view"></div>').appendTo($('#container'));

      if (controller.feature && !FeatureFlags.isEnabled(controller.feature)) {
        this.hideLoadingMask();

        return this.notFound();
      }

      if (controller.aclResourceId && !securityContext.isGranted(controller.aclResourceId)) {
        this.hideLoadingMask();

        return this.displayErrorPage(__('error.forbidden'), '403');
      }

      controller.el = $view;
      this.currentController = new controller.class(controller);
      this.currentController.setActive(true);

      const rendering = this.currentController.renderRoute(route, path);
      // Legacy controllers return jQuery promises (their callbacks are run synchronously when already resolved, keep
      // that timing), others may return a native promise.
      const renderingPromise = rendering && 'function' === typeof rendering.done ? rendering : $.when(rendering);

      renderingPromise
        .done(() => this.triggerComplete(route))
        .fail(this.handleError.bind(this))
        .always(() => this.hideLoadingMask());
    });
  },

  /**
   * Manage not found error
   */
  notFound: function () {
    this.displayErrorPage('Page not found', 404);
  },

  handleError: function (xhr) {
    if (undefined !== xhr) {
      this.displayErrorPage('An error occurred');
    }

    switch (xhr.status) {
      case 401:
        window.location = this.generate('pim_user_security_login');
        break;
      case 403:
        return this.displayErrorPage(__('error.forbidden'), xhr.status);
      case 200:
        break;
      default:
        this.errorPage(xhr);
        break;
    }
  },

  /**
   * Manage error from xhr calls
   *
   * @param {Object} xhr
   */
  errorPage: function (xhr) {
    this.displayErrorPage(xhr.statusText, xhr.status);
  },

  /**
   * Display the error page
   *
   * @param {String} message
   * @param {Integer} code
   */
  displayErrorPage: function (message, code) {
    const errorView = new ErrorView(message, code);
    errorView.setElement($('#container')).render();
  },

  /**
   * Trigger route start events
   *
   * @param {Object} route
   */
  triggerStart: function (route) {
    this.trigger('route:' + route.name, route.params);
    this.trigger('route_start', route.name, route.params);
    this.trigger('route_start:' + route.name, route.params);
    mediator.trigger('route_start', route.name, route.params);
    mediator.trigger('route_start:' + route.name, route.params);
  },

  /**
   * Trigger completed route events
   *
   * @param {Object} route
   */
  triggerComplete: function (route) {
    this.trigger('route_complete', route.name, route.params);
    this.trigger('route_complete:' + route.name, route.params);
    mediator.trigger('route_complete', route.name, route.params);
    mediator.trigger('route_complete:' + route.name, route.params);
  },

  showLoadingMask: function () {
    this.loadingMask && this.loadingMask.show();
  },

  hideLoadingMask: function () {
    this.loadingMask && this.loadingMask.hide();
  },

  generate: function () {
    return Routing.generate.apply(Routing, arguments);
  },

  match: function () {
    return RouteMatcher.match.apply(RouteMatcher, arguments);
  },

  /**
   * Low level navigation, without asking the current controller if it can be left.
   *
   * @param {String} fragment
   * @param {Object|Boolean} options {trigger, replace, force} (true is a shortcut for {trigger: true})
   */
  navigate: function (fragment, options) {
    if (!options || true === options) {
      options = {trigger: Boolean(options)};
    }

    return history.navigate(fragment, options);
  },

  /**
   * @return {String|null} the current fragment
   */
  getFragment: function () {
    return history.getFragment();
  },

  /**
   * Forgets the current fragment so that the next navigation reloads the page, even if the URL is the same.
   */
  forgetFragment: function () {
    history.forget();
  },

  redirect: function (fragment, options) {
    if (this.currentController && !this.currentController.canLeave()) {
      return false;
    }

    fragment = fragment.indexOf('#') === 0 ? fragment : '#' + fragment;
    this.navigate(fragment, options);
  },

  /**
   * Redirect to the given route
   */
  redirectToRoute: function (route, routeParams, options) {
    return this.redirect(Routing.generate(route, routeParams), options);
  },

  /**
   * Reload the current page
   */
  reloadPage: function () {
    this.redirect(window.location.hash, {trigger: true});
  },

  /**
   * Process route links in the current page
   */
  _processLinks: function () {
    document.querySelectorAll('a[route]').forEach(link => {
      const route = link.getAttribute('route');
      let routeParams = link.getAttribute('route-params');
      if (routeParams) {
        try {
          routeParams = JSON.parse(routeParams.replace(/'/g, '"'));
        } catch (error) {
          routeParams = null;
        }
      }
      link.setAttribute('href', '#' + Routing.generate(route, routeParams));
    });
  },
});

// Callers take methods off the router (`const {redirect} = router`): every method stays bound to it
Object.keys(router).forEach(key => {
  if ('function' === typeof router[key]) {
    router[key] = router[key].bind(router);
  }
});

module.exports = router;
