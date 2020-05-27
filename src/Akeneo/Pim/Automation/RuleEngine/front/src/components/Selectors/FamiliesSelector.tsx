import React from 'react';
import {
  InitSelectionCallback,
  Select2MultiAsyncWrapper,
  Select2Option,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import { IndexedFamilies } from '../../fetch/FamilyFetcher';
import { getFamiliesByIdentifiers } from '../../repositories/FamilyRepository';
import { Family, FamilyCode, LocaleCode } from '../../models';

type Props = {
  router: Router;
  id: string;
  label: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: FamilyCode[];
  onChange: (value: FamilyCode[]) => void;
};

const dataProvider = (term: string, page: number) => {
  return {
    search: term,
    options: {
      limit: 20,
      page: page,
      locale: 'en_US',
    },
  };
};

const handleResults = (
  families: IndexedFamilies,
  currentCatalogLocale: LocaleCode
) => {
  return {
    more: 20 === Object.keys(families).length,
    results: Object.keys(families).map(
      (familyIdentifier): Select2Option => {
        return {
          id: families[familyIdentifier].code,
          text:
            families[familyIdentifier].labels[currentCatalogLocale] ||
            `[${families[familyIdentifier].code}]`,
        };
      }
    ),
  };
};

const initSelectedFamilies = async (
  router: Router,
  selectedFamilyCodes: FamilyCode[],
  currentCatalogLocale: LocaleCode,
  callback: InitSelectionCallback
): Promise<void> => {
  const families: IndexedFamilies = await getFamiliesByIdentifiers(
    selectedFamilyCodes,
    router
  );

  callback(
    Object.values(families).map((family: Family) => {
      return {
        id: family.code,
        text: family.labels[currentCatalogLocale] || `[${family.code}]`,
      };
    })
  );
};

const FamiliesSelector: React.FC<Props> = ({
  router,
  id,
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
}) => {
  return (
    <Select2MultiAsyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      value={value}
      onValueChange={value => onChange(value as string[])}
      ajax={{
        url: router.generate('pim_enrich_family_rest_index'),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (families: IndexedFamilies) =>
          handleResults(families, currentCatalogLocale),
      }}
      initSelection={(_element, callback) => {
        initSelectedFamilies(router, value, currentCatalogLocale, callback);
      }}
    />
  );
};

export { FamiliesSelector };
