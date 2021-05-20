import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {CategoriesApp} from '@akeneo-pim-community/settings-ui';
const __ = require('oro/translator');

const mediator = require('oro/mediator');

class CategoriesSettings extends ReactController {
  private canLeavePage: boolean = true;

  setCanLeavePage(canLeavePage: boolean) {
    this.canLeavePage = canLeavePage;
  }

  reactElementToMount() {
    return <CategoriesApp setCanLeavePage={(canLeavePage: boolean) => this.setCanLeavePage(canLeavePage)} />;
  }

  routeGuardToUnmount() {
    return /pim_enrich_categorytree_(index|tree|edit)/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-product-category'});

    return super.renderRoute();
  }

  canLeave() {
    return this.canLeavePage || confirm(__('pim_ui.flash.unsaved_changes'));
  }
}

export = CategoriesSettings;
