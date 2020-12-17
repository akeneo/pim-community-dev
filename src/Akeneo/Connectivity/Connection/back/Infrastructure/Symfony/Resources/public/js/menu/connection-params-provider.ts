const {BaseForm: View} = require('pim/form');
const mediator = require('oro/mediator');

class ConnectionParamsProvider extends View {
  initialize() {
    mediator.on('route_start', (route: string, params: any) => {
      switch (route) {
        case 'akeneo_connectivity_connection_settings_edit':
        case 'akeneo_connectivity_connection_error_management_connection_monitoring':
          this.getRoot().trigger('pim_menu:item:update_route_params', {
            route: 'akeneo_connectivity_connection_settings_edit',
            routeParams: {code: params.code},
          });
          this.getRoot().trigger('pim_menu:item:update_route_params', {
            route: 'akeneo_connectivity_connection_error_management_connection_monitoring',
            routeParams: {code: params.code},
          });
      }
    });

    super.initialize();
  }
}

export default ConnectionParamsProvider;
