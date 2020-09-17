import React from 'react';
import {
  InitSelectionCallback,
  Select2Ajax,
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import { IndexedFamilies } from '../../fetch/FamilyFetcher';
import { getFamiliesByIdentifiers } from '../../repositories/FamilyRepository';
import { FamilyCode, LocaleCode } from '../../models';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: FamilyCode[];
  onChange?: (value: FamilyCode[]) => void;
  validation?: { required?: string; validate?: (value: any) => string | true };
  name: string;
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
    selectedFamilyCodes.map(familyCode => {
      return families[familyCode]
        ? {
            id: familyCode,
            text:
              families[familyCode].labels[currentCatalogLocale] ||
              `[${familyCode}]`,
          }
        : {
            id: familyCode,
            text: `[${familyCode}]`,
          };
    })
  );
};

const FamiliesSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  validation,
  name,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const handleChange = (value: Select2Value[]) => {
    if (onChange) {
      onChange(value as FamilyCode[]);
    }
  };

  const ajax = React.useMemo<Select2Ajax>(() => {
    return {
      url: router.generate('pim_enrich_family_rest_index'),
      quietMillis: 250,
      cache: true,
      data: (term: string, page: number) =>
        dataProvider(term, page, currentCatalogLocale),
      results: (families: IndexedFamilies) =>
        handleResults(families, currentCatalogLocale),
    };
  }, [currentCatalogLocale, router]);

  return (
    <Select2MultiAsyncWrapper
      {...remainingProps}
      name={name}
      label={label || translate('pim_enrich.entity.family.plural_label')}
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      ajax={ajax}
      initSelection={(_element, callback) =>
        initSelectedFamilies(router, value, currentCatalogLocale, callback)
      }
      validation={validation}
    />
  );
};

export { FamiliesSelector };
