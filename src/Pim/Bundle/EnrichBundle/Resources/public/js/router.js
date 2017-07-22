import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Backbone from 'backbone'
import Routing from 'routing'
import RouteMatcher from 'pim/route-matcher'
import LoadingMask from 'oro/loading-mask'
import ControllerRegistry from 'pim/controller-registry'
import mediator from 'oro/mediator'
import Error from 'pim/error'
import securityContext from 'pim/security-context'
import 'pim/controller/template'

var Router = Backbone.Router.extend({
  DEFAULT_ROUTE: 'oro_default',
  routes: {
    '': 'dashboard',
    '*path': 'defaultRoute'
  },
  loadingMask: null,
  currentController: null,

  /**
   * {@inheritdoc}
   */
  initialize: function () {
    this.loadingMask = new LoadingMask()
    this.loadingMask.render().$el.appendTo($('.hash-loading-mask'))
    _.bindAll(this, 'showLoadingMask', 'hideLoadingMask')

    this.listenTo(mediator, 'route_complete', this._processLinks)
  },

  /**
   * Go to the homepage of the app
   *
   * @return {String}
   */
  dashboard: function () {
    return this.defaultRoute(this.generate('pim_dashboard_index'))
  },

  /**
   * Render the given route
   *
   * @param {String} path
   */
  defaultRoute: function (path) {
    if (path.indexOf('/') !== 0) {
      path = '/' + path
    }

    if (path.indexOf('|') !== -1) {
      path = path.substring(0, path.indexOf('|'))
    }

    var route = this.match(path)
    if (route === false) {
      return this.notFound()
    }
    if (this.DEFAULT_ROUTE === route.name) {
      return this.dashboard()
    }

    this.showLoadingMask()
    this.triggerStart(route)

    ControllerRegistry.get(route.name).done(function (controller) {
      if (this.currentController) {
        this.currentController.remove()
      }

      $('#container').empty()
      var $view = $('<div class="view"/>').appendTo($('#container'))

      if (controller.aclResourceId && !securityContext.isGranted(controller.aclResourceId)) {
        this.hideLoadingMask()

        return this.displayErrorPage(__('pim_enrich.error.http.403'), '403')
      }

      controller.el = $view
      this.currentController = new controller.class(controller) // eslint-disable-line
      this.currentController.setActive(true)
      this.currentController.renderRoute(route, path)
        .done(function () {
          this.triggerComplete(route)
        }.bind(this))
        .fail(this.handleError.bind(this))
        .always(this.hideLoadingMask)
    }.bind(this))
  },

  /**
   * Manage not found error
   */
  notFound: function () {
    this.displayErrorPage('Page not found', 404)
  },

  handleError: function (xhr) {
    switch (xhr.status) {
      case 401:
        window.location = this.generate('oro_user_security_login')
        break
      default:
        this.errorPage(xhr)
        break
    }
  },

  /**
   * Manage error from xhr calls
   *
   * @param {Object} xhr
   */
  errorPage: function (xhr) {
    this.displayErrorPage(xhr.statusText, xhr.status)
  },

  /**
   * Display the error page
   *
   * @param {String} message
   * @param {Integer} code
   */
  displayErrorPage: function (message, code) {
    var errorView = new Error(message, code)
    errorView.setElement($('#container')).render()
  },

  /**
   * Trigger route start events
   *
   * @param {String} route
   */
  triggerStart: function (route) {
    this.trigger('route:' + route.name, route.params)
    this.trigger('route_start', route.name, route.params)
    this.trigger('route_start:' + route.name, route.params)
    mediator.trigger('route_start', route.name, route.params)
    mediator.trigger('route_start:' + route.name, route.params)
  },

  /**
   * Trigger completed route events
   *
   * @param {String} route
   */
  triggerComplete: function (route) {
    this.trigger('route_complete', route.name, route.params)
    this.trigger('route_complete:' + route.name, route.params)
    mediator.trigger('route_complete', route.name, route.params)
    mediator.trigger('route_complete:' + route.name, route.params)
  },

  /**
   * Display the loading mask
   */
  showLoadingMask: function () {
    this.loadingMask.show()
  },

  /**
   * Hide the loading mask
   */
  hideLoadingMask: function () {
    this.loadingMask.hide()
  },

  /**
   * {@inheritdoc}
   */
  generate: function () {
    return Routing.generate.apply(Routing, arguments)
  },

  /**
   * {@inheritdoc}
   */
  match: function () {
    return RouteMatcher.match.apply(RouteMatcher, arguments)
  },

  /**
   * {@inheritdoc}
   */
  redirect: function (fragment, options) {
    if (this.currentController && !this.currentController.canLeave()) {
      return false
    }

    fragment = fragment.indexOf('#') === 0 ? fragment : '#' + fragment
    Backbone.history.navigate(fragment, options)
  },

  /**
   * Redirect to the given route
   */
  redirectToRoute: function (route, routeParams, options) {
    this.redirect(Routing.generate(route, routeParams), options)
  },

  /**
   * Reload the current page
   */
  reloadPage: function () {
    var fragment = window.location.hash
    this.redirect(fragment, {
      trigger: true
    })
  },

  /**
   * Process route links in the current page
   */
  _processLinks: function () {
    _.each($('a[route]'), function (link) {
      var route = link.getAttribute('route')
      var routeParams = link.getAttribute('route-params')
      if (routeParams) {
        try {
          routeParams = JSON.parse(routeParams.replace(/'/g, '"'))
        } catch (error) {
          routeParams = null
        }
      }
      link.setAttribute('href', '#' + Routing.generate(route, routeParams))
    })
  }
})

export default new Router()
