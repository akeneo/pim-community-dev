import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DashboardIndex} from '@akeneo-pim-community/activity';

const mediator = require('oro/mediator');

class DashboardController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <DashboardIndex />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /pim_dashboard_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-dashboard'});

    return super.renderRoute();
  }
}

export = DashboardController;
