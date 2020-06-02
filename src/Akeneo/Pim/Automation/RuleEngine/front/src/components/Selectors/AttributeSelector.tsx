import React from 'react';
import {
  InitSelectionCallback,
  Select2SimpleAsyncWrapper,
} from '../Select2Wrapper';

import { Attribute, AttributeCode, LocaleCode } from '../../models';
import { Router } from '../../dependenciesTools';
import { useBackboneRouter } from '../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository'

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
  id: string;
  label: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: AttributeCode | null;
  onChange: (value: AttributeCode) => void;
  placeholder?: string;
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

  attribute && callback({
    id: attribute.code,
    text: attribute.labels[currentCatalogLocale] || `[${attribute.code}]`,
  });
};

const AttributeSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  placeholder,
}) => {
  const router: Router = useBackboneRouter();

  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const dataProvider = (term: string, page: number) => {
    return {
      search: term,
      options: {
        limit: 20,
        page: page,
        systemFields: [],
        locale: currentCatalogLocale,
      },
    };
  };

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
        return { ...group, id: null };
      }),
    };
  };

  return (
    <Select2SimpleAsyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      dropdownCssClass={'fields-selector-dropdown'}
      value={value}
      onValueChange={value => onChange(value as string)}
      ajax={{
        url: router.generate('pimee_enrich_rule_definition_get_available_fields'),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (attributes: Results) =>
          handleResults(attributes),
      }}
      onSelecting={(event: any) => {
        event.preventDefault();
        if (event.val !== null) {
          // User has not clicked on a group
          setCloseTick(!closeTick);
          onChange(event.val);
        }
      }}
      initSelection={(_element, callback) => {
        if (value) {
          initSelectedAttribute(router, value, currentCatalogLocale, callback);
        }
      }}
      placeholder={placeholder}
      formatResult={option => {

        return `<span>${option.text}</span>`;
      }}
      allowClear={true}
    />
  );
};

export { AttributeSelector };
