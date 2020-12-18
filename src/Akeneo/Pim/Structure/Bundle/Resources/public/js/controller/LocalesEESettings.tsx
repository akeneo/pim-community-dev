import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {LocalesEEApp} from '@akeneo-pim-enterprise/settings-ui';

const mediator = require('oro/mediator');

class LocalesEESettings extends ReactController {
  reactElementToMount() {
    return <LocalesEEApp />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_locale_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {
      extension: 'pim-menu-settings',
    });
    mediator.trigger('pim_menu:highlight:item', {
      extension: 'pim-menu-settings-locale',
    });

    return super.renderRoute();
  }
}

export = LocalesEESettings;
