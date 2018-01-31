require('fos-routing-base')
require('routes')
var Routing = Routing || window.Routing;

Routing.generateHash = (route, routeParams) => `#${Routing.generate(route, routeParams)}`;

module.exports = Routing;
