import React, {FC, useMemo} from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Channel, MicroFrontendDependenciesProvider, Routes, useRouter} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import {FakePIM} from './FakePIM';
import {Attribute, FetcherContext, MeasurementFamily} from './feature';

const baseFetcher = async (route: string) => {
  const response = await fetch(route);

  return await response.json();
};

const cache = {};
const cachedFetcher = (route: string) => {
  if (!cache[route]) {
    cache[route] = baseFetcher(route);
  }

  return cache[route];
};

const FetcherProvider: FC = ({children}) => {
  const router = useRouter();

  const fetcherValue = useMemo(
    () => ({
      attribute: {
        fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> => {
          const route = router.generate('pim_enrich_attribute_rest_index', {
            identifiers: identifiers.join(','),
          });

          return cachedFetcher(route);
        },
        fetchAttributeIdentifier: async (): Promise<Attribute> => {
          const route = router.generate('pim_enrich_attribute_rest_index', {
            types: 'pim_catalog_identifier',
          });

          const attributes = await cachedFetcher(route);

          return attributes[0];
        },
      },
      channel: {
        fetchAll: (): Promise<Channel[]> => {
          const route = router.generate('pim_enrich_channel_rest_index');

          return cachedFetcher(route);
        },
      },
      measurementFamily: {
        fetchByCode: async (measurementFamilyCode: string): Promise<MeasurementFamily | undefined> => {
          const route = router.generate('pim_enrich_measures_rest_index');

          const measurementFamilies = await cachedFetcher(route);

          return measurementFamilies.find(({code}) => code === measurementFamilyCode);
        },
      },
    }),
    [router]
  );

  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes}>
        <FetcherProvider>
          <FakePIM />
        </FetcherProvider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
