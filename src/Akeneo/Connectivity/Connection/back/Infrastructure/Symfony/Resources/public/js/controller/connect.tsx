import {Connect} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import highlightMenu from '../menu/highlight-menu';
import ReactController from '../react/react-controller';

class ConnectController extends ReactController {
  reactElementToMount() {
    return <Connect dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_connect/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute(route: {name: string}) {
    highlightMenu(route.name);

    return super.renderRoute(route);
  }
}

export = ConnectController;
