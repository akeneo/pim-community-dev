import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ProcessTrackerApp} from '@akeneo-pim-community/process-tracker';
import {ThemeProvider} from 'styled-components';

const mediator = require('oro/mediator');

class ProcessTrackerController extends ReactController {
  private static container = document.createElement('div');

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <ProcessTrackerApp />
        </DependenciesProvider>
      </ThemeProvider>
    );
  }

  routeGuardToUnmount() {
    return /^akeneo_job_process_tracker_/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-process-tracker'});

    return super.renderRoute();
  }

  getContainerRef(): Element {
    return ProcessTrackerController.container;
  }
}

export = ProcessTrackerController;
