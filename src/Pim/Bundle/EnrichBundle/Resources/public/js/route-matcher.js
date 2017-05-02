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

        for (var name in routes) {
            route = routes[name];
            var pattern = '';
            var variables = [];
            var matching = true;

            if (route.requirements._method &&
                -1 === route.requirements._method.indexOf('GET') &&
                -1 === route.requirements._method.indexOf('POST')
            ) {
                continue;
            }

            route.tokens.forEach(function (token) {
                switch (token[0]) {
                    case 'text':
                        pattern = escape(token[1]) + pattern;
                        matching = matching && url.indexOf(token[1]) !== -1;
                        break;
                    case 'variable':
                        var separator = escape(token[1]);
                        var varPattern = token[2].replace('++', '+');
                        var varName = token[3];

                        if (undefined === route.defaults[token[3]]) {
                            pattern = separator + '(' + varPattern + ')' + pattern;
                        } else {
                            pattern = '(?:' + separator + '(' + varPattern + '))?' + pattern;
                        }
                        variables.push(varName);
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
                    var matchedValue = matches[index + 1];
                    if (undefined !== matchedValue) {
                        params[variable] = matchedValue;
                    } else {
                        params[variable] = route.defaults[variable];
                    }
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
