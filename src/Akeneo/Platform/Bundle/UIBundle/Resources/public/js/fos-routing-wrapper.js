import Routing from 'fos-routing-base';
import routes from 'routes';

Routing.setRoutingData(routes);
Routing.generateHash = (route, routeParams) => `#${Routing.generate(route, routeParams)}`;

module.exports = Routing;
