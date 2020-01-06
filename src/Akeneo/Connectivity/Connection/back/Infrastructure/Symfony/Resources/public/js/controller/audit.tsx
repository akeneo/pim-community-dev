import {Audit} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

class AuditController extends ReactController {
  reactElementToMount() {
    return <Audit dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_audit_/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-connection-audit'});

    return super.renderRoute();
  }
}

export = AuditController;
