import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import BaseView = require('pimui/js/view/base');
import {
  Attribute,
  FetcherContext,
  ImportStructureTab,
  ImportStructureTabProps,
  MeasurementFamily,
  StructureConfiguration,
} from '@akeneo-pim-enterprise/tailored-import';
import {pimTheme} from 'akeneo-design-system';
import {Channel, filterErrors, formatParameters, ValidationError} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

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
      this.getRoot().trigger('pim_enrich:form:form-tabs:remove-errors');
      this.setValidationErrors([]);
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event => {
      const errors = formatParameters(filterErrors(event.response.normalized_errors, '[import_structure]'));
      this.setValidationErrors(errors);

      if (errors.length > 0) {
        this.getRoot().trigger('pim_enrich:form:form-tabs:add-errors', {
          tabCode: this.getTabCode(),
          errors,
        });
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

  setStructureConfigurationData(structureConfiguration: StructureConfiguration): void {
    const formData = this.getFormData();
    this.setData({
      ...formData,
      configuration: {
        ...formData.configuration,
        ...structureConfiguration,
      },
    });
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const structureConfiguration: StructureConfiguration = {
      file_key: formData.configuration.file_key ?? null,
      error_action: formData.configuration.error_action ?? 'skip_product',
      import_structure: {
        columns: formData.configuration.import_structure.columns ?? [],
        data_mappings: formData.configuration.import_structure.data_mappings ?? [],
      },
      file_structure: formData.configuration.file_structure,
    };

    const props: ImportStructureTabProps = {
      structureConfiguration,
      validationErrors: this.validationErrors,
      onStructureConfigurationChange: this.setStructureConfigurationData.bind(this),
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
                  fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> =>
                    new Promise(resolve =>
                      fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(identifiers).then(resolve)
                    ),
                  fetchAttributeIdentifier: (): Promise<Attribute> =>
                    new Promise(resolve =>
                      fetcherRegistry.getFetcher('attribute').getIdentifierAttribute().then(resolve)
                    ),
                },
                channel: {
                  fetchAll: (): Promise<Channel[]> =>
                    new Promise(resolve => fetcherRegistry.getFetcher('channel').fetchAll().then(resolve)),
                },
                measurementFamily: {
                  fetchByCode: (code: string): Promise<MeasurementFamily | undefined> => {
                    return new Promise(resolve => fetcherRegistry.getFetcher('measure').fetch(code).then(resolve));
                  },
                },
              },
            },
            React.createElement(ImportStructureTab, props)
          )
        )
      ),
      this.el
    );

    return this;
  }
}

export = ColumnView;
