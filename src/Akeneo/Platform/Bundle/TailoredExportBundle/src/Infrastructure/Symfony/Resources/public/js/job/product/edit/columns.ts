import React from 'react';
import ReactDOM from 'react-dom';
import BaseView = require('pimui/js/view/base');
import {
  ColumnsTab,
  ColumnsTabProps,
  FetcherContext,
  Attribute,
  AssociationType,
} from '@akeneo-pim-enterprise/tailored-export';
import {Channel, ValidationError} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
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
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.tabTitle),
    });

    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', () => this.setValidationErrors([]));
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', event =>
      this.setValidationErrors(event.response.normalized_errors)
    );

    return BaseView.prototype.configure.apply(this, arguments);
  }

  setValidationErrors(validationErrors: ValidationError[]) {
    this.validationErrors = validationErrors;
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();

    const props: ColumnsTabProps = {
      columnsConfiguration: formData.configuration.columns,
      onColumnsConfigurationChange: columnsConfiguration => {
        this.setData({...formData, configuration: {...formData.configuration, columns: columnsConfiguration}});
        this.render();
      },
      validationErrors: this.validationErrors,
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
                        .then((associationTypes: AssociationType[]) =>
                          associationTypes.filter(associationType => codes.includes(associationType.code))
                        )
                        .then(resolve)
                    );
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
