import React, {Children} from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {CategoriesApp} from '@akeneo-pim-community/category/lib/CategoriesApp';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';
const __ = require('oro/translator');

const mediator = require('oro/mediator');

// To be used later when we have to decide whether we should show some element or not based on CE vs EE
const CategoryEnrichmentProvider = ({children}) => children;
class CategoriesSettings extends ReactController {
  private canLeavePage: boolean = true;

  private static container = document.createElement('div');

  setCanLeavePage(canLeavePage: boolean) {
    this.canLeavePage = canLeavePage;
  }

  reactElementToMount() {
    return (
      <ThemeProvider theme={pimTheme}>
        <DependenciesProvider>
          <CategoryEnrichmentProvider>
            <CategoriesApp setCanLeavePage={(canLeavePage: boolean) => this.setCanLeavePage(canLeavePage)} />
          </CategoryEnrichmentProvider>
        </DependenciesProvider>
      </ThemeProvider>
    );
  }

  routeGuardToUnmount() {
    return /pim_enrich_categorytree_(index|tree|edit|template_edit)/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {
      extension: 'pim-menu-settings',
    });
    mediator.trigger('pim_menu:highlight:item', {
      extension: 'pim-menu-settings-product-category',
    });

    return super.renderRoute();
  }

  canLeave() {
    return this.canLeavePage || confirm(__('pim_ui.flash.unsaved_changes'));
  }

  getContainerRef(): Element {
    return CategoriesSettings.container;
  }
}

export = CategoriesSettings;
