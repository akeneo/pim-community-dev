import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeContextProvider, AttributeOptionsContextProvider, LocalesContextProvider} from './contexts';
import AttributeOptions from './components/AttributeOptions';
import OverridePimStyle from './components/OverridePimStyles';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

interface IndexProps {
  attributeId: number;
  autoSortOptions: boolean;
}

const AttributeOptionsApp = ({attributeId, autoSortOptions}: IndexProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSortOptions}>
          <LocalesContextProvider>
            <AttributeOptionsContextProvider>
              <OverridePimStyle />
              <AttributeOptions />
            </AttributeOptionsContextProvider>
          </LocalesContextProvider>
        </AttributeContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default AttributeOptionsApp;
