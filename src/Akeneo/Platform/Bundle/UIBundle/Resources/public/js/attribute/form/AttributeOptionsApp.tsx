import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
  AttributeContextProvider,
  AttributeOptionsContextProvider,
  LocalesContextProvider,
} from 'akeneopimstructure/js/attribute-option/contexts';
import AttributeOptions from 'akeneopimstructure/js/attribute-option/components/AttributeOptions';
import OverridePimStyle from 'akeneopimstructure/js/attribute-option/components/OverridePimStyles';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {fetchSpellcheckEvaluation} from '@akeneo-pim-ee/data-quality-insights';

interface IndexProps {
  attributeId: number;
  attributeCode: string;
  autoSortOptions: boolean;
}

const AttributeOptionsApp = ({attributeId, attributeCode, autoSortOptions}: IndexProps) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSortOptions}>
          <LocalesContextProvider>
            <AttributeOptionsContextProvider
              attributeOptionsQualityFetcher={async () => {
                return await fetchSpellcheckEvaluation(attributeCode);
              }}
            >
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
