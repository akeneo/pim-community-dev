'use strict';

define(function (require) {
    var $ = require('jquery');
    var _ = require('underscore');
    var Backbone = require('backbone');
    var Routing = require('routing');
    var RouteMatcher = require('pim/route-matcher');
    var LoadingMask = require('oro/loading-mask');
    var ControllerRegistry = require('pim/controller-registry');
    var mediator = require('oro/mediator');
    var currentController = null;

    var Router = Backbone.Router.extend({
        DEFAULT_ROUTE: 'oro_default',
        routes: {
            '': 'dashboard',
            '*path': 'defaultRoute'
        },
        loadingMask: null,
        initialize: function () {
            this.loadingMask = new LoadingMask();
            this.loadingMask.render().$el.appendTo($('.hash-loading-mask'));
            _.bindAll(this, 'showLoadingMask', 'hideLoadingMask');
        },
        dashboard: function () {
            return this.defaultRoute(this.generate('pim_dashboard_index'));
        },
        defaultRoute: function (path) {
            if (path.indexOf('/') !== 0) {
                path = '/' + path;
            }
            var route = this.match(path);
            if (false === route) {
                return this.notFound();
            }
            if (this.DEFAULT_ROUTE === route.name) {
                return this.dashboard();
            }

            this.showLoadingMask();

            this.triggerStart(route);

            ControllerRegistry.get(route.name).done(_.bind(function (Controller) {
                if (currentController) {
                    currentController.remove();
                    $('#container').empty();
                }
                var $view = $('<div>', {'class': 'view'}).appendTo($('#container'));
                currentController = new Controller({ el: $view });
                currentController.renderRoute(route, path).done(_.bind(function () {
                    this.triggerComplete(route);
                }, this)).fail(this.notFound).always(this.hideLoadingMask);
            }, this));
        },
        notFound: function () {
            // temp
            $('#container').html('Whoops, no such page!');
        },
        triggerStart: function (route) {
            this.trigger('route:' + route.name, route.params);
            this.trigger('route_start', route.name, route.params);
            this.trigger('route_start:' + route.name, route.params);
            mediator.trigger('route_start', route.name, route.params);
            mediator.trigger('route_start:' + route.name, route.params);
        },
        triggerComplete: function (route) {
            this.trigger('route_complete', route.name, route.params);
            this.trigger('route_complete:' + route.name, route.params);
            mediator.trigger('route_complete', route.name, route.params);
            mediator.trigger('route_complete:' + route.name, route.params);
        },
        showLoadingMask: function () {
            this.loadingMask.show();
        },
        hideLoadingMask: function () {
            this.loadingMask.hide();
        },
        generate: function () {
            return Routing.generate.apply(Routing, arguments);
        },
        match: function () {
            return RouteMatcher.match.apply(RouteMatcher, arguments);
        },
        redirect: function (fragment, options) {
            Backbone.history.navigate(fragment, options);
        },
        redirectToRoute: function (route, routeParams, options) {
            this.redirect(Routing.generate(route, routeParams), options);
        }
    });

    return new Router();
});
