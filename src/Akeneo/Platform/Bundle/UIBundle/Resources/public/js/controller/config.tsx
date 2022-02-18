import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ConfigForm} from '@akeneo-pim-community/config';

const mediator = require('oro/mediator');

class ConfigController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <ConfigForm />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /oro_config_configuration_system/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-configuration'});

    return super.renderRoute();
  }
}

export = ConfigController;
