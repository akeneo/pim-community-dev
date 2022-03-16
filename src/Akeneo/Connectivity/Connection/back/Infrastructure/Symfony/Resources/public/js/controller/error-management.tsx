import {ErrorManagement} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import highlightMenu from '../menu/highlight-menu';
import ReactController from '../react/react-controller';

class ErrorManagementController extends ReactController {
  reactElementToMount() {
    return <ErrorManagement dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_error_management_/;
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

export default ErrorManagementController;
