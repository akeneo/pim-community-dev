import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {JobExecutionDetail} from '@akeneo-pim-community/process-tracker/lib/pages/JobExecutionDetail';
import {Route, HashRouter as Router, Switch} from 'react-router-dom';

const mediator = require('oro/mediator');

class JobExecutionController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Router>
            <Switch>
              <Route path="/job/show/:jobExecutionId">
                <JobExecutionDetail />
              </Route>
            </Switch>
          </Router>
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
