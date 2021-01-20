import React from 'react';
import ReactController from '../react/react-controller';
// import {Index} from 'akeneomeasure/index';
import {measurementsDependencies} from '../dependencies';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

class SettingsController extends ReactController {
  reactElementToMount() {
    return <div />;
  }

  routeGuardToUnmount() {
    return /^akeneo_measurements_settings_/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-measurements-settings'});

    return super.renderRoute();
  }

  canLeave() {
    return !measurementsDependencies.unsavedChanges.hasUnsavedChanges || confirm(__('pim_ui.flash.unsaved_changes'));
  }
}

export default SettingsController;
