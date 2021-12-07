import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import React from 'react';
import * as $ from 'jquery';
import BaseView = require('pimui/js/view/base');
import {CatalogVolumeMonitoringApp, getCatalogVolume} from '@akeneo-pim-community/catalog-volume-monitoring';

const mediator = require('oro/mediator');
const featureFlags = require('pim/feature-flags');
const FormBuilder = require('pim/form-builder');
const Routing = require('routing');
const translate = require('oro/translator');
const Error = require('pim/error');

class CatalogVolumeController extends ReactController {
  private formPromise: JQueryPromise<BaseView> | null;

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
    if (featureFlags.isEnabled('control_volume_monitoring_new_page')) {
      mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-system'});
      mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-system-catalog-volume'});

      return super.renderRoute();
    }

    // return old CVM
    this.formPromise = this.renderForm().fail(response => {
      const message =
        response && response.responseJSON
          ? response.responseJSON.message
          : translate('pim_enrich.entity.fallback.generic_error');
      const status = response && response.status ? response.status : 500;

      const errorView = new Error(message, status);
      errorView.setElement(this.$el).render();
    });

    return jQuery.Deferred().resolve(this.formPromise);
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

  remove() {
    if (!featureFlags.isEnabled('control_volume_monitoring_new_page')) {
      if (null === this.formPromise) {
        return;
      }

      this.formPromise.then(form => {
        if (form && typeof form.shutdown === 'function') {
          form.shutdown();
        }
      });
    }

    super.remove();
  }
}

export = CatalogVolumeController;
