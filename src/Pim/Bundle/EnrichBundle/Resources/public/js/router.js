'use strict';

define(function (require) {
    var $ = require('jquery');
    var Backbone = require('backbone');
    var Routing = require('routing');

    Routing.match = function (url) {
        var routes = this.getRoutes().c;
        var route;

        if (url.indexOf('?') !== -1) {
            url = url.substring(0, url.indexOf('?'));
        }

        if (url.indexOf('#') === 0) {
            url = url.substring(1);
        }

        if (url.indexOf('#') !== -1) {
            url = url.substring(0, url.indexOf('#'));
        }

        var escape = function (value) {
            return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
        }

        for (var name in routes) {
            route = routes[name];
            var pattern = '';
            var variables = [];
            var matching = true;

            route.tokens.forEach(function (token) {
                switch (token[0]) {
                    case 'text':
                        pattern = escape(token[1]) + pattern;
                        matching = matching && url.indexOf(token[1]) !== -1;
                        break;
                    case 'variable':
                        pattern = escape(token[1]) + '(' + token[2].replace('++', '+') + ')' + pattern;
                        variables.push(token[3]);
                        break;
                    default:
                        break;
                }
            });

            if (!matching) {
                continue;
            }

            var matches = url.match(new RegExp(pattern + '$'));

            if (matches) {
                var params = {};
                variables.reverse();

                variables.forEach(function (variable, index) {
                    params[variable] = matches[index + 1];
                });

                return {
                    name: name,
                    route: route,
                    params: params
                };
            }
        }

        return false;
    }

    return Backbone.Router.extend({
        routes: {
            '': 'dashboard',
            '*route': 'defaultRoute'
        },
        dashboard: function () {
            return this.defaultRoute(Routing.generate('pim_dashboard_index'));
        },
        defaultRoute: function (route) {
            if (route.indexOf('/') !== 0) {
                route = '/' + route;
            }
            var routeData = Routing.match(route);
            if (false === routeData) {
                return this.notFound();
            }
            this.trigger('route:' + routeData.name, routeData.params);
            $.get(route).done(function (template) {
                $('#container').html(template);

                // temp
                _.each($('a[href]'), function (link) {
                    var href = $(link).attr('href');
                    if (href.substring(0, 1) !== '#' && href.substring(0, 11) !== 'javascript:') {
                        href = '#' + href;
                    }
                    $(link).attr('href', href);
                });

            }).fail(this.notFound);
        },
        notFound: function () {
            $('#container').html('Whoops, no such page!');
        }
    });
});
