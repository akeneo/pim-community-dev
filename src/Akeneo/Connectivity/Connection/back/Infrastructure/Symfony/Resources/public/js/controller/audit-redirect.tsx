import React from 'react';
import ReactController from '../react/react-controller';
import {RedirectToConnectMenu} from "@akeneo-pim-community/connectivity-connection/src/shared/components/RedirectToConnectMenu";
import {dependencies} from '../dependencies';

const mediator = require('oro/mediator');

class AuditRedirectController extends ReactController {
  reactElementToMount() {
    return <RedirectToConnectMenu dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_connectivity_connection_redirect_audit$/;
  }

  initialize() {
    this.$el.addClass('AknConnectivityConnection-view');

    return super.initialize();
  }

  renderRoute(route: any) {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-connection-audit'});

    return super.renderRoute(route);
  }
}

export = AuditRedirectController;
