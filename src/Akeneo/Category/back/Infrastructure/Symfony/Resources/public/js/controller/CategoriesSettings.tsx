import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {CategoriesApp} from "@akeneo-pim-community/category";
import {CanLeavePageProvider} from "@akeneo-pim-community/category/lib/components";


const __ = require('oro/translator');

const mediator = require('oro/mediator');

class CategoriesSettings extends ReactController {
  private canLeavePage: boolean = true;

  private static container = document.createElement('div');

  setCanLeavePage(canLeavePage: boolean) {
    this.canLeavePage = canLeavePage;
  }

  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CanLeavePageProvider setCanLeavePage={(canLeavePage: boolean) => this.setCanLeavePage(canLeavePage)}>
            <CategoriesApp />
          </CanLeavePageProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /pim_category_template_edit|pim_enrich_categorytree_(index|tree|edit)/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-settings'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-settings-product-category'});

    return super.renderRoute();
  }

  canLeave() {
    return this.canLeavePage || confirm(__('akeneo.category.edition_form.unsaved_changes'));
  }

  getContainerRef(): Element {
    return CategoriesSettings.container;
  }
}

export = CategoriesSettings;
