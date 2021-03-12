import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {SSOConfiguration} from './SSOConfiguration';

const mediator = require('oro/mediator');
const __ = require('oro/translator');

class SSOConfigurationController extends ReactController {
  private canLeavePage = true;

  private setCanLeavePage = (canLeavePage: boolean) => (this.canLeavePage = canLeavePage);

  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <SSOConfiguration setCanLeavePage={this.setCanLeavePage} readonly={false} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /^authentication_sso_configuration_edit/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-sso'});

    return super.renderRoute();
  }

  canLeave() {
    return this.canLeavePage || confirm(__('pim_ui.flash.unsaved_changes'));
  }
}

export = SSOConfigurationController;
