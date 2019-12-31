import {Dashboard} from '@akeneo-pim-community/connectivity-connection';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

class DashboardController extends ReactController {
  reactElementToMount() {
    return <Dashboard dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_apps_dashboard_/;
  }

  initialize() {
    this.$el.addClass('AknApps-view');

    return super.initialize();
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-apps-dashboard'});

    return super.renderRoute();
  }
}

export = DashboardController;
