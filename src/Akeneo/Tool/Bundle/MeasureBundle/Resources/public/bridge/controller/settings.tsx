import React from 'react';
import {dependencies} from '../dependencies';
import ReactController from '../react/react-controller';
import App from 'akeneomeasure/index';

const mediator = require('oro/mediator');

class SettingsController extends ReactController {
  reactElementToMount() {
    return <App dependencies={dependencies} />;
  }

  routeGuardToUnmount() {
    return /^akeneo_measurements_settings_/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-measurements-settings'});

    return super.renderRoute();
  }
}

export = SettingsController;
