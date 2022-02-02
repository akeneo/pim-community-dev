import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import BaseView = require('pimui/js/view/base');
import {
  Attribute,
  FetcherContext,
  ImportStructureTab,
  ImportStructureTabProps,
  StructureConfiguration,
} from '@akeneo-pim-enterprise/tailored-import';
import {pimTheme} from 'akeneo-design-system';
import {Channel, filterErrors, ValidationError} from '@akeneo-pim-community/shared';
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
      this.setValidationErrors(event.response.normalized_errors);

      const errors = filterErrors(this.validationErrors, '[import-structure]');
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
    this.setData({...formData, configuration: {...formData.configuration, ...structureConfiguration}});
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const structureConfiguration: StructureConfiguration = {
      columns: formData.configuration.columns ?? [],
      dataMappings: formData.configuration.dataMappings ?? [],
    };

    const props: ImportStructureTabProps = {
      structureConfiguration,
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
                  fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> => {
                    return new Promise(resolve =>
                      fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(identifiers).then(resolve)
                    );
                  },
                },
                channel: {
                  fetchAll: (): Promise<Channel[]> => {
                    return new Promise(resolve => fetcherRegistry.getFetcher('channel').fetchAll().then(resolve));
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
