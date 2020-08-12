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
import { AttributeId } from '../../models';
import {
  useBackboneRouter,
  useUserCatalogLocale,
} from '../../dependenciesTools/hooks';

type Props = {
  label: string;
  hiddenLabel: boolean;
  attributeId: AttributeId;
  onChange?: (value: Select2Value[]) => void;
  value: string[];
  name: string;
};

const MultiOptionsSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  attributeId,
  onChange,
  value,
  name,
  ...remainingProps
}) => {
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();
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
      {...remainingProps}
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
    />
  );
};

export { MultiOptionsSelector };
