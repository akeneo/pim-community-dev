import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {MeasurementApp, ConfigContext, UnsavedChangesContext} from '@akeneo-pim-community/measurement';
import {measurementsDependencies} from '../dependencies';
import {ThemeProvider} from 'styled-components';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

class SettingsController extends ReactController {
  private static container = document.createElement('div');

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <ConfigContext.Provider value={measurementsDependencies.config}>
            <UnsavedChangesContext.Provider value={measurementsDependencies.unsavedChanges}>
              <MeasurementApp />
            </UnsavedChangesContext.Provider>
          </ConfigContext.Provider>
        </DependenciesProvider>
      </ThemeProvider>
    );
  }

  routeGuardToUnmount() {
    return /^akeneo_measurements_settings_/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-measurements-settings'});

    return super.renderRoute();
  }

  getContainerRef(): Element {
    return SettingsController.container;
  }

  canLeave() {
    return !measurementsDependencies.unsavedChanges.hasUnsavedChanges || confirm(__('pim_ui.flash.unsaved_changes'));
  }
}

export = SettingsController;
