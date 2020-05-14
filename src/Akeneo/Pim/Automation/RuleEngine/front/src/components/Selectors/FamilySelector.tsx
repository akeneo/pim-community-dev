import React from 'react';
import {
  InitSelectionCallback,
  Select2MultiAsyncWrapper,
  Select2Option,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import { IndexedFamilies } from '../../fetch/FamilyFetcher';
import { Family } from '../../models';
import { getFamiliesByIdentifiers } from '../../repositories/FamilyRepository';

type Props = {
  router: Router;
  id: string;
  label: string;
  hiddenLabel?: boolean;
  multiple: boolean;
  selectedFamilyCodes: string[];
  currentCatalogLocale: string;
  onSelectorChange: (values: string[]) => void;
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
  currentCatalogLocale: string
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
  selectedFamilyCodes: string[],
  currentCatalogLocale: string,
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

const FamilySelector: React.FC<Props> = ({
  router,
  id,
  label,
  hiddenLabel = false,
  selectedFamilyCodes,
  currentCatalogLocale,
  onSelectorChange,
}) => {
  return (
    <Select2MultiAsyncWrapper
      id={id}
      label={label}
      hiddenLabel={hiddenLabel}
      onChange={(value: string[]) => {
        onSelectorChange(value);
      }}
      value={selectedFamilyCodes}
      ajax={{
        url: router.generate('pim_enrich_family_rest_index'),
        quietMillis: 250,
        cache: true,
        data: dataProvider,
        results: (families: IndexedFamilies) =>
          handleResults(families, currentCatalogLocale),
      }}
      initSelection={(_element, callback) =>
        initSelectedFamilies(
          router,
          selectedFamilyCodes,
          currentCatalogLocale,
          callback
        )
      }
    />
  );
};

export { FamilySelector };
