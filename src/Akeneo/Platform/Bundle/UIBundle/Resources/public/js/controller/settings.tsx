import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const mediator = require('oro/mediator');
const routing = require('routing');

class DashboardController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <div>
            <p><a href={`#${routing.generateHash('pim_enrich_categorytree_index')}`}>Categories</a></p>
            <p><a href={`#${routing.generateHash('pim_enrich_channel_index')}`}>Channels</a></p>
            <p><a href={`#${routing.generateHash('pim_enrich_locale_index')}`}>Locales</a></p>
          </div>
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /pim_settings_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:hide', 'pim-menu-settings-column');

    return super.renderRoute();
  }
}

export = DashboardController;
