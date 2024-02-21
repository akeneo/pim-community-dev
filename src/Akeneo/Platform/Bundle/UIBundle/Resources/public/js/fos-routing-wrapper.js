const Routing = require('fos-routing-base');
const routes = require('routes');

Routing.setRoutingData(routes);
Routing.generateHash = (route, routeParams) => `#${Routing.generate(route, routeParams)}`;

module.exports = Routing;
