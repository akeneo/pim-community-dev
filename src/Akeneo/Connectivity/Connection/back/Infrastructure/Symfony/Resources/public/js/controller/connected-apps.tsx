import {ConnectedApps} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

class ConnectedAppsController extends ReactController {
  reactElementToMount() {
    return <ConnectedApps dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_connect_connected_apps/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute(route: {name: string}) {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-connect'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-connect-connected-apps'});

    return super.renderRoute(route);
  }
}

export = ConnectedAppsController;
