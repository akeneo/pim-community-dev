import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import {CatalogVolumeMonitoringApp, getCatalogVolume} from '@akeneo-pim-community/catalog-volume-monitoring';

const mediator = require('oro/mediator');

class CatalogVolumeController extends ReactController {
  reactElementToMount() {
    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolume} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }

  routeGuardToUnmount() {
    return /pim_enrich_catalog_volume_index/;
  }

  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-catalog-volume'});

    return super.renderRoute();
  }
}

export = CatalogVolumeController;
