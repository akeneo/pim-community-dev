import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {IdentifierGeneratorApp} from '@akeneo-pim-community/identifier-generator';
import {ThemeProvider} from 'styled-components';

const mediator = require('oro/mediator');

class IdentifierGeneratorController extends ReactController {
  private static container = document.createElement('div');

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <IdentifierGeneratorApp />
        </DependenciesProvider>
      </ThemeProvider>
    );
  }

  routeGuardToUnmount() {
    return /^akeneo_identifier_generator_index$/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-identifier-generator-settings'});

    return super.renderRoute();
  }

  getContainerRef(): Element {
    return IdentifierGeneratorController.container;
  }

  canLeave() {
    return true;
  }
}

export = IdentifierGeneratorController;
