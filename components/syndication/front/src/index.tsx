import React, {useMemo, FC} from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  Channel,
  MicroFrontendDependenciesProvider,
  Routes,
  Translations,
  useRouter,
} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {FakePIM} from './FakePIM';
import {Attribute, AssetFamily, AssociationType, MeasurementFamily} from './feature/configuration/models';
import {FetcherContext} from './feature/configuration/contexts';
import {QueryClient, QueryClientProvider} from 'react-query';

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
      },
      channel: {
        fetchAll: (): Promise<Channel[]> => {
          const route = router.generate('pim_enrich_channel_rest_index');

          return cachedFetcher(route);
        },
      },
      associationType: {
        fetchByCodes: (codes: string[]): Promise<AssociationType[]> => {
          const route = router.generate('pim_enrich_associationtype_rest_index', {
            identifiers: codes.join(','),
          });

          return cachedFetcher(route);
        },
      },
      measurementFamily: {
        fetchByCode: async (measurementFamilyCode: string): Promise<MeasurementFamily | undefined> => {
          const route = router.generate('pim_enrich_measures_rest_index');

          const measurementFamilies = await cachedFetcher(route);

          return measurementFamilies.find(({code}) => code === measurementFamilyCode);
        },
        fetchAll: async (): Promise<MeasurementFamily[]> => {
          const route = router.generate('pim_enrich_measures_rest_index');

          const measurementFamilies = await cachedFetcher(route);

          return measurementFamilies;
        },
      },
      assetFamily: {
        fetchByIdentifier: async (assetFamilyIdentifier: string): Promise<AssetFamily | undefined> => {
          const route = router.generate('akeneo_asset_manager_asset_family_get_rest', {
            identifier: assetFamilyIdentifier,
          });

          return await cachedFetcher(route);
        },
      },
    }),
    [router]
  );

  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

const client = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 10 * 1000, // 10s
      cacheTime: 5 * 60 * 1000, // 5m
    },
  },
});

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <QueryClientProvider client={client}>
        <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
          <FetcherProvider>
            <FakePIM />
          </FetcherProvider>
        </MicroFrontendDependenciesProvider>
      </QueryClientProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
