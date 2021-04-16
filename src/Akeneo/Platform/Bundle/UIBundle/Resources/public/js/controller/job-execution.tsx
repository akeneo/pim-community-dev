import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {ReactController} from '@akeneo-pim-community/shared';
import {Index} from '../job/execution';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const mediator = require('oro/mediator');

class JobExecutionController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Index />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^pim_enrich_job_tracker_show$/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-job-tracker'});

    return super.renderRoute();
  }
}

export = JobExecutionController;
