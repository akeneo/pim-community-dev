import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import * as $ from 'jquery';
import BaseView = require('pimui/js/view/base');
import {CatalogVolumeMonitoringApp, getCatalogVolume} from '@akeneo-pim-community/catalog-volume-monitoring';

const mediator = require('oro/mediator');
const FormBuilder = require('pim/form-builder');
const Routing = require('routing');

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

  renderForm(): JQueryPromise<BaseView> {
    return $.when(
      FormBuilder.build('pim-catalog-volume-index'),
      $.get(Routing.generate('pim_volume_monitoring_get_volumes'))
    ).then((form: BaseView, data = []) => {
      this.on('pim:controller:can-leave', (event: {canLeave: true}) => {
        form.trigger('pim_enrich:form:can-leave', event);
      });

      form.setData(data[0]);
      form.setElement(this.$el).render();

      return form;
    });
  }
}

export = CatalogVolumeController;
