import React from 'react';
import ReactDOM from 'react-dom';
import BaseView = require('pimui/js/view/base');
import {
  AssetFamily,
  ColumnsTab,
  ColumnsTabProps,
  FetcherContext,
  Attribute,
  AssociationType,
  ColumnConfiguration,
  MeasurementFamily,
} from '@akeneo-pim-enterprise/tailored-export';
import {filterErrors, Channel, ValidationError} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
const __ = require('oro/translator');
const fetcherRegistry = require('pim/fetcher-registry');

class ColumnView extends BaseView {
  public config: any;
  private validationErrors: ValidationError[] = [];

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  configure() {
    this.trigger('tab:register', {
      code: this.getTabCode(),
      label: __(this.config.tabTitle),
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => {
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-error', this.getTabCode());
      this.setValidationErrors([]);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      this.setValidationErrors(event.response.normalized_errors);

      if (filterErrors(this.validationErrors, '[columns]').length > 0) {
        this.getRoot().trigger('pim_enrich:form:form-tabs:add-error', this.getTabCode());
      }
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    this.render();
  }

  getTabCode(): string {
    return this.config.tabCode ? this.config.tabCode : this.code;
  }

  setColumnConfigurationData(columnsConfiguration: ColumnConfiguration[]): void {
    const formData = this.getFormData();
    this.setData({...formData, configuration: {...formData.configuration, columns: columnsConfiguration}});
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: ColumnsTabProps = {
      columnsConfiguration: formData.configuration.columns,
      onColumnsConfigurationChange: this.setColumnConfigurationData.bind(this),
      validationErrors: this.validationErrors,
      entityType: ['xlsx_tailored_product_export', 'csv_tailored_product_export'].includes(formData.job_name)
        ? 'product'
        : 'product_model',
    };

    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(
          DependenciesProvider,
          null,
          React.createElement(
            FetcherContext.Provider,
            {
              value: {
                attribute: {
                  fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('attribute')
                        .fetchByIdentifiers(identifiers)
                        .then(resolve)
                    );
                  },
                },
                channel: {
                  fetchAll: (): Promise<Channel[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('channel')
                        .fetchAll()
                        .then(resolve)
                    );
                  },
                },
                associationType: {
                  fetchByCodes: (codes: string[]): Promise<AssociationType[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('association-type')
                        .fetchByIdentifiers(codes)
                        .then(resolve)
                    );
                  },
                },
                measurementFamily: {
                  fetchByCode: (code: string): Promise<MeasurementFamily | undefined> => {
                    return new Promise(resolve =>
                      fetcherRegistry
                        .getFetcher('measure')
                        .fetch(code)
                        .then(resolve)
                    );
                  },
                },
                assetFamily: {
                  fetchByIdentifier: async (identifier: string): Promise<AssetFamily | undefined> => {
                    const {assetFamily} = await assetFamilyFetcher.fetch(identifier);
                    return {
                      identifier: assetFamily.identifier,
                      attribute_as_main_media: assetFamily.attributeAsMainMedia,
                      attributes: assetFamily.attributes,
                    };
                  },
                },
              },
            },
            React.createElement(ColumnsTab, props)
          )
        )
      ),
      this.el
    );

    return this;
  }
}

export = ColumnView;
