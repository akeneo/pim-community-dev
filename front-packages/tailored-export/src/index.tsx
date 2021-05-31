import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  baseFetcher,
  Channel,
  MicroFrontendDependenciesProvider,
  Routes,
  Translations,
  useRouter,
} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {FakePIM} from './FakePIM';
import {Attribute, FetcherContext} from './feature/contexts';

const FetcherProvider = ({children}) => {
  const router = useRouter();

  return (
    <FetcherContext.Provider
      value={{
        attribute: {
          fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> => {
            const route = router.generate('pim_enrich_attribute_rest_index', {
              identifiers: identifiers.join(','),
            });

            return baseFetcher(route);
          },
        },
        channel: {
          fetchAll: (): Promise<Channel[]> => {
            const route = router.generate('pim_enrich_channel_rest_index');

            return baseFetcher(route);
          },
        },
      }}
    >
      {children}
    </FetcherContext.Provider>
  );
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <FetcherProvider>
          <FakePIM />
        </FetcherProvider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
