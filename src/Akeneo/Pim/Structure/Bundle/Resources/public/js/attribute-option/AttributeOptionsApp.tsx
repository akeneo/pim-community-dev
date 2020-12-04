import React, {useEffect} from 'react';
import {Provider} from 'react-redux';

import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import attributeOptionsStore from './store/store';
import {AttributeContextProvider, LocalesContextProvider} from './contexts';
import AttributeOptions from './components/AttributeOptions';
import OverridePimStyle from './components/OverridePimStyles';
import {resetAttributeOptionsAction} from './reducers';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

interface IndexProps {
  attributeId: number;
  autoSortOptions: boolean;
}

const AttributeOptionsApp = ({attributeId, autoSortOptions}: IndexProps) => {
  useEffect(() => {
    return () => {
      attributeOptionsStore.dispatch(resetAttributeOptionsAction());
    };
  }, []);

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Provider store={attributeOptionsStore}>
          <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSortOptions}>
            <LocalesContextProvider>
              <OverridePimStyle />
              <AttributeOptions />
            </LocalesContextProvider>
          </AttributeContextProvider>
        </Provider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default AttributeOptionsApp;
