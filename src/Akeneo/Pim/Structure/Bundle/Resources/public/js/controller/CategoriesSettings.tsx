import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {CategoriesApp} from '@akeneo-pim-community/settings-ui';

const mediator = require('oro/mediator');

class CategoriesSettings extends ReactController {
  reactElementToMount() {
    return <CategoriesApp />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_categorytree_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-product-category'});

    return super.renderRoute();
  }
}

export = CategoriesSettings;
