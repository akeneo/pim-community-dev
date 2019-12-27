import {Apps} from '@akeneo-pim-ce/apps';
import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';

const mediator = require('oro/mediator');

class AppsController extends ReactController {
  reactElementToMount() {
    return <Apps dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_apps_/;
  }

  initialize() {
    this.$el.addClass('AknApps-view');

    return super.initialize();
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-apps'});

    return super.renderRoute();
  }
}

export = AppsController;
