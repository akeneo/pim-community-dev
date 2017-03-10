'use strict';

define(['routing'], function (Routing) {
    var matchUrl = function (url) {
        var routes = Routing.getRoutes().c;
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

        if (url.indexOf('/') !== 0) {
            url = '/' + url;
        }

        url = url.replace(Routing.getBaseUrl(), '');

        var escape = function (value) {
            return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
        };

        /* jshint loopfunc:true */
        for (var name in routes) {
            // if (name === 'pim_enrich_categorytree_create_tree') {
            //     debugger;
            // }
            route = routes[name];
            var pattern = '';
            var variables = [];
            var matching = true;

            if (route.requirements._method &&
                -1 === route.requirements._method.indexOf('GET') &&
                -1 === route.requirements._method.indexOf('POST')) {
                continue;
            }

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

            var matches = url.match(new RegExp('^' + pattern + '$'));

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
    };

    return {
        match: matchUrl
    };
});
