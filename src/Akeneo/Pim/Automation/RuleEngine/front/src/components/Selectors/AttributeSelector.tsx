import React from 'react';
import {
  InitSelectionCallback,
  Select2Ajax,
  Select2SimpleAsyncWrapper,
} from '../Select2Wrapper';

import {
  Attribute,
  AttributeCode,
  getAttributeLabel,
  LocaleCode,
} from '../../models';
import { Router } from '../../dependenciesTools';
import { useBackboneRouter } from '../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';

type AttributeResult = {
  id: string;
  text: string;
};

type AttributeGroupResult = {
  id: string;
  text: string;
  children: AttributeResult[];
};

type Results = AttributeGroupResult[];

type Props = {
  label: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: AttributeCode | null;
  onSelecting?: (event: any) => void;
  onChange?: (value: AttributeCode) => void;
  placeholder?: string;
  filterAttributeTypes?: string[];
  name: string;
  validation?: {
    required?: string;
    validate?: (value: any) => string | true | Promise<string | true>;
  };
  disabled?: boolean;
};

const initSelectedAttribute = async (
  router: Router,
  selectedAttributeCode: AttributeCode,
  currentCatalogLocale: LocaleCode,
  callback: InitSelectionCallback
): Promise<void> => {
  const attribute: Attribute | null = await getAttributeByIdentifier(
    selectedAttributeCode,
    router
  );

  if (attribute) {
    callback({
      id: attribute.code,
      text: getAttributeLabel(attribute, currentCatalogLocale),
    });
    return;
  }
  callback({
    id: selectedAttributeCode,
    text: selectedAttributeCode,
  });
};

const AttributeSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  placeholder,
  filterAttributeTypes,
  name,
  validation,
  ...remainingProps
}) => {
  const router: Router = useBackboneRouter();

  const ajax = React.useMemo<Select2Ajax>(() => {
    let lastDisplayedGroupLabel: string;

    const handleResults = (result: Results) => {
      const fieldCount = result.reduce((previousCount, group) => {
        return previousCount + group.children.length;
      }, 0);

      if (result.length) {
        const firstCurrentGroupLabel = result[0].text;
        if (firstCurrentGroupLabel === lastDisplayedGroupLabel) {
          // Prevents to display 2 times the group label. Having an empty text removes the line.
          result[0].text = '';
        }
        lastDisplayedGroupLabel = result[result.length - 1].text;
      }

      return {
        more: fieldCount >= 20,
        results: result.map(group => {
          return { ...group, disabled: true };
        }),
      };
    };

    const dataProvider = (term: string, page: number) => {
      const data: any = {
        search: term,
        options: {
          limit: 20,
          page: page,
          locale: currentCatalogLocale,
          systemFields: [],
        },
      };
      if (filterAttributeTypes) {
        data.options.attributeTypes = filterAttributeTypes;
      }

      return data;
    };

    return {
      url: router.generate('pimee_enrich_rule_definition_get_available_fields'),
      quietMillis: 250,
      cache: true,
      data: dataProvider,
      results: (attributes: Results) => handleResults(attributes),
    };
  }, [JSON.stringify(filterAttributeTypes), currentCatalogLocale, router]);

  return (
    <Select2SimpleAsyncWrapper
      {...remainingProps}
      name={name}
      label={label}
      hiddenLabel={hiddenLabel}
      dropdownCssClass={'fields-selector-dropdown'}
      value={value}
      onChange={value => (onChange ? onChange(value as string) : null)}
      ajax={ajax}
      initSelection={(_element, callback) => {
        if (value) {
          initSelectedAttribute(
            router,
            _element.val(),
            currentCatalogLocale,
            callback
          );
        }
      }}
      // This formatResult is useful for the :empty css selector
      // (without the format the result line is not empty because select2 adds some child node)
      formatResult={option => option.text}
      placeholder={placeholder}
      validation={validation}
    />
  );
};

export { AttributeSelector };
