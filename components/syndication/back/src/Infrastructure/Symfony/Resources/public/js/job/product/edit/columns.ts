import React from 'react';
import ReactDOM from 'react-dom';
import BaseView = require('pimui/js/view/base');
import {
  AssetFamily,
  Syndication,
  FetcherContext,
  Attribute,
  AssociationType,
  MeasurementFamily,
} from '@akeneo-pim-enterprise/syndication';
import {Channel} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {QueryClient, QueryClientProvider} from 'react-query';

const fetcherRegistry = require('pim/fetcher-registry');
const router = require('pim/router');


const client = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 10 * 1000, // 10s
      cacheTime: 5 * 60 * 1000, // 5m
    },
  },
});

class ColumnView extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const formData = this.getFormData();
    const props: {jobCode: string} = {
      jobCode: formData.code,
    };

    ReactDOM.render(
      React.createElement(
        QueryClientProvider,
        {client: client},
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
                  associationType: {
                    fetchByCodes: (codes: string[]): Promise<AssociationType[]> => {
                      return new Promise(resolve =>
                        fetcherRegistry.getFetcher('association-type').fetchByIdentifiers(codes).then(resolve)
                      );
                    },
                  },
                  measurementFamily: {
                    fetchByCode: (code: string): Promise<MeasurementFamily | undefined> => {
                      return new Promise(resolve => fetcherRegistry.getFetcher('measure').fetch(code).then(resolve));
                    },
                    fetchAll: (): Promise<MeasurementFamily[]> => {
                      return new Promise(resolve => fetcherRegistry.getFetcher('measure').fetchAll().then(resolve));
                    },
                  },
                  assetFamily: {
                    fetchByIdentifier: async (identifier: string): Promise<AssetFamily | undefined> => {
                      const route = router.generate('akeneo_asset_manager_asset_family_get_rest', {identifier});
                      const response = await fetch(route);
                      if (!response.ok) {
                        return undefined;
                      }

                      return await response.json();
                    },
                  },
                },
              },
              React.createElement(Syndication, props)
            )
          )
        ),
      ),
      this.el
    );

    return this;
  }
}

export = ColumnView;
