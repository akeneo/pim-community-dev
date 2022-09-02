import React from 'react';
import {
  AttributeContextProvider,
  AttributeOptionsContextProvider,
  LocalesContextProvider,
} from 'akeneopimstructure/js/attribute-option/contexts';
import AttributeOptions from 'akeneopimstructure/js/attribute-option/components/AttributeOptions';
import OverridePimStyle from 'akeneopimstructure/js/attribute-option/components/OverridePimStyles';
import {fetchSpellcheckEvaluation} from '@akeneo-pim-ee/data-quality-insights';
import {useFeatureFlags} from '@akeneo-pim-community/shared';

interface IndexProps {
  attributeId: number;
  attributeCode: string;
  autoSortOptions: boolean;
}

const AttributeOptionsApp = ({attributeId, attributeCode, autoSortOptions}: IndexProps) => {
  const featureFlags = useFeatureFlags();
  const attributeOptionsQualityFetcher = featureFlags.isEnabled('data_quality_insights_all_criteria')
    ? async () => {
        return await fetchSpellcheckEvaluation(attributeCode);
      }
    : undefined;

  return (
    <AttributeContextProvider attributeId={attributeId} autoSortOptions={autoSortOptions}>
      <LocalesContextProvider>
        <AttributeOptionsContextProvider attributeOptionsQualityFetcher={attributeOptionsQualityFetcher}>
          <OverridePimStyle />
          <AttributeOptions />
        </AttributeOptionsContextProvider>
      </LocalesContextProvider>
    </AttributeContextProvider>
  );
};

export default AttributeOptionsApp;
