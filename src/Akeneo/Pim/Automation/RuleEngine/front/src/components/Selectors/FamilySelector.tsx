import React from 'react';
import {
  InitSelectionCallback,
  Select2Option,
  Select2SimpleAsyncWrapper,
  Select2Value,
} from '../Select2Wrapper';
import {Router} from '../../dependenciesTools';
import {IndexedFamilies} from '../../fetch/FamilyFetcher';
import {getFamilyByIdentifier} from '../../repositories/FamilyRepository';
import {Family, FamilyCode, LocaleCode} from '../../models';
import {useBackboneRouter} from '../../dependenciesTools/hooks';

type Props = {
  label: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: FamilyCode | null;
  onChange?: (value: FamilyCode) => void;
  placeholder?: string;
  name: string;
};

const dataProvider = (term: string, page: number, locale: LocaleCode) => {
  return {
    search: term,
    options: {
      limit: 20,
      page: page,
      locale: locale,
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

const initSelectedFamily = async (
  router: Router,
  selectedFamilyCode: FamilyCode,
  currentCatalogLocale: LocaleCode,
  callback: InitSelectionCallback
): Promise<void> => {
  const family: Family = await getFamilyByIdentifier(
    selectedFamilyCode,
    router
  );

  callback({
    id: family.code,
    text: family.labels[currentCatalogLocale] || `[${family.code}]`,
  });
};

const FamilySelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  placeholder,
  name,
}) => {
  const router = useBackboneRouter();
  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as FamilyCode);
    }
  };

  return (
    <Select2SimpleAsyncWrapper
      dropdownCssClass='family-selector-dropdown'
      label={label}
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      ajax={{
        url: router.generate('pim_enrich_family_rest_index'),
        quietMillis: 250,
        cache: true,
        data: (term: string, page: number) =>
          dataProvider(term, page, currentCatalogLocale),
        results: (families: IndexedFamilies) =>
          handleResults(families, currentCatalogLocale),
      }}
      initSelection={(_element, callback) => {
        if (value) {
          initSelectedFamily(router, value, currentCatalogLocale, callback);
        }
      }}
      placeholder={placeholder}
      allowClear={true}
      name={name}
    />
  );
};

export {FamilySelector};
