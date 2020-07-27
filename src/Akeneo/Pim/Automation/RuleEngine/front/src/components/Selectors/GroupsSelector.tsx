import React from 'react';
import {
  InitSelectionCallback,
  Select2Ajax,
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import { getGroupsByIdentifiers } from '../../repositories/GroupRepository';
import { GroupCode, LocaleCode } from '../../models';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: GroupCode[];
  onChange?: (value: GroupCode[]) => void;
  validation?: { required?: string; validate?: (value: any) => string | true };
  name: string;
  id: string;
};

const dataProvider = (
  term: string,
  page: number,
  currentCatalogLocale: LocaleCode
) => {
  return {
    search: term,
    options: {
      limit: 20,
      page: page,
      locale: currentCatalogLocale,
    },
  };
};

type Select2Results = {
  results: Select2Option[];
};

const handleResults = (json: Select2Results) => {
  return {
    more: 20 === json.results.length,
    ...json,
  };
};

const initSelectedGroups = (
  router: Router,
  selectedGroupCodes: GroupCode[],
  callback: InitSelectionCallback
): void => {
  getGroupsByIdentifiers(selectedGroupCodes, router).then(groups => {
    callback(
      selectedGroupCodes.map(groupCode => {
        return {
          id: groupCode,
          text: groups[groupCode]?.label || `[${groupCode}]`,
        };
      })
    );
  });
};

const GroupsSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  validation,
  name,
  id,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const handleChange = (value: Select2Value[]) => {
    if (onChange) {
      onChange(value as GroupCode[]);
    }
  };

  const ajax = React.useMemo<Select2Ajax>(() => {
    return {
      url: router.generate('pim_enrich_group_rest_search'),
      quietMillis: 250,
      cache: true,
      data: (term: string, page: number) =>
        dataProvider(term, page, currentCatalogLocale),
      results: (json: Select2Results) => handleResults(json),
    };
  }, [currentCatalogLocale, router]);

  return (
    <Select2MultiAsyncWrapper
      {...remainingProps}
      name={name}
      label={
        label ||
        translate('pim_enrich.mass_edit.product.operation.add_to_group.field')
      }
      data-testid={id}
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      ajax={ajax}
      initSelection={(_element, callback) => {
        initSelectedGroups(router, value, callback);
      }}
      validation={validation}
    />
  );
};

export { GroupsSelector };
