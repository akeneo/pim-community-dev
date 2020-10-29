import React from 'react';
import {Select2Option, Select2Value, Select2Wrapper} from '../Select2Wrapper';
import {
  AttributeOptionDataProvider,
  getAttributeOptionsByIdentifiers,
} from '../../fetch/AttributeOptionFetcher';
import {AttributeId} from '../../models';
import {
  useBackboneRouter,
  useUserCatalogLocale,
} from '../../dependenciesTools/hooks';

type Props = {
  label: string;
  hiddenLabel: boolean;
  attributeId: AttributeId;
  onChange?: (value: Select2Value) => void;
  value: string;
  name: string;
  allowClear?: boolean;
  validation?: {required?: string; validate?: (value: any) => string | true};
};

const SimpleOptionSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  attributeId,
  onChange,
  value,
  allowClear = true,
  ...remainingProps
}) => {
  const currentCatalogLocale = useUserCatalogLocale();

  const router = useBackboneRouter();
  const handleResults = (response: {results: Select2Option[]}) => {
    return {
      more: 20 === response.results.length,
      results: response.results,
    };
  };

  const initSelectedOption = (value: any, callback: any) => {
    getAttributeOptionsByIdentifiers(
      value,
      currentCatalogLocale,
      attributeId,
      router
    ).then(attributeOptions => {
      const found = attributeOptions.find(attributeOption => {
        return attributeOption.id === value;
      });

      callback(
        found
          ? found
          : {
              id: value,
              text: `[${value}]`,
            }
      );
    });
  };

  return (
    <Select2Wrapper
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
      placeholder={' '} // it allows to remove the 'undefined' text that is display when value is empty
      value={value}
      initSelection={(_element, callback) => {
        initSelectedOption(value, callback);
      }}
      onChange={(value: Select2Value | Select2Value[]) => {
        if (onChange && !Array.isArray(value)) {
          return onChange(value);
        }
      }}
      hiddenLabel={hiddenLabel}
      multiple={false}
      allowClear={allowClear}
    />
  );
};

export {SimpleOptionSelector};
