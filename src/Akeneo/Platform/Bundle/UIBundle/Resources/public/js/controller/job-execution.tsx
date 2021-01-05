import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {Index} from '../job/execution';

const mediator = require('oro/mediator');

class JobExecutionController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <Index />
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
