import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {AttributeGroupsApp} from '@akeneo-pim-community/settings-ui';

const mediator = require('oro/mediator');

class AttributeGroupsSettings extends ReactController {
  reactElementToMount() {
    return <AttributeGroupsApp />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_attributegroup_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-attribute-group'});

    return super.renderRoute();
  }
}

export default AttributeGroupsSettings;
