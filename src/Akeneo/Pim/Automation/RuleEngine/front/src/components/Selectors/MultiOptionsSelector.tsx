import React from 'react';
import {
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import {
  AttributeOptionCode,
  AttributeOptionDataProvider,
  getAttributeOptionsByIdentifiers,
} from '../../fetch/AttributeOptionFetcher';
import { AttributeId } from '../../models/Attribute';

type Props = {
  id: string;
  label: string;
  hiddenLabel: boolean;
  router: Router;
  currentCatalogLocale: string;
  attributeId: AttributeId;
  onValueChange: (value: Select2Value[]) => void;
  value: string[];
};

const MultiOptionsSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  router,
  currentCatalogLocale,
  attributeId,
  onValueChange,
  value,
}) => {
  const handleResults = (response: { results: Select2Option[] }) => {
    return {
      more: 20 === response.results.length,
      results: response.results,
    };
  };

  const initSelectedOptions = (value: any, callback: any) => {
    getAttributeOptionsByIdentifiers(
      value,
      currentCatalogLocale,
      attributeId,
      router
    ).then(attributeOptions => {
      callback(
        value.map((optionCode: AttributeOptionCode) => {
          const found = attributeOptions.find(attributeOption => {
            return attributeOption.id === optionCode;
          });
          return found
            ? found
            : {
                id: optionCode,
                text: `[${optionCode}]`,
              };
        })
      );
    });
  };

  return (
    <Select2MultiAsyncWrapper
      id={id}
      label={label}
      ajax={{
        url: router.generate('pim_ui_ajaxentity_list'),
        quietMillis: 250,
        cache: true,
        data: (term, page) => {
          return AttributeOptionDataProvider(
            currentCatalogLocale,
            attributeId,
            term,
            page
          );
        },
        results: handleResults,
      }}
      value={value}
      initSelection={(_element, callback) => {
        initSelectedOptions(value, callback);
      }}
      onValueChange={onValueChange}
      hiddenLabel={hiddenLabel}
    />
  );
};

export { MultiOptionsSelector };
