import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {LocalesApp} from '@akeneo-pim-community/settings-ui';

const mediator = require('oro/mediator');

class AttributeGroupsSettings extends ReactController {
  reactElementToMount() {
    return <LocalesApp />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_locale_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-locale'});

    return super.renderRoute();
  }
}

export = AttributeGroupsSettings;
