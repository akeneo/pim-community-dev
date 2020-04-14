import {ErrorManagement} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

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

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-connection-settings'});

    return super.renderRoute();
  }
}

export = ErrorManagementController;
