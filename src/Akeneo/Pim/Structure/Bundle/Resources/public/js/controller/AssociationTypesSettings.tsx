import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {AssociationTypesApp} from '@akeneo-pim-community/settings-ui';

const mediator = require('oro/mediator');

class AssociationTypesSettings extends ReactController {
  reactElementToMount() {
    return <AssociationTypesApp />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_associationtype_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-association-type'});
    return super.renderRoute();
  }
}

export = AssociationTypesSettings;
