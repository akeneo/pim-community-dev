import React from 'react';
import {
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import {
  AttributeOptionCode,
  AttributeOptionDataProvider,
  getAttributeOptionsByIdentifiers,
} from '../../fetch/AttributeOptionFetcher';
import { AttributeId } from '../../models/Attribute';
import { useBackboneRouter } from '../../dependenciesTools/hooks';

type Props = {
  label: string;
  hiddenLabel: boolean;
  currentCatalogLocale: string;
  attributeId: AttributeId;
  onChange?: (value: Select2Value[]) => void;
  value: string[];
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
};

const MultiOptionsSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  attributeId,
  onChange,
  value,
  name,
  validation,
}) => {
  const router = useBackboneRouter();
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
      onChange={onChange}
      hiddenLabel={hiddenLabel}
      name={name}
      validation={validation}
    />
  );
};

export { MultiOptionsSelector };
