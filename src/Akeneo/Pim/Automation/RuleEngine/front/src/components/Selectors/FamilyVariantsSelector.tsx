import React from 'react';
import {
  InitSelectionCallback,
  Select2Ajax,
  Select2MultiAsyncWrapper,
  Select2Option,
  Select2Value,
} from '../Select2Wrapper';
import { Router } from '../../dependenciesTools';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';
import { FamilyVariantCode, LocaleCode } from '../../models';
import { IndexedFamilyVariants } from '../../fetch/FamilyVariantFetcher';
import { getFamilyVariantsByIdentifiers } from '../../repositories/FamilyVariantRepository';

type Props = {
  id: string;
  label?: string;
  hiddenLabel?: boolean;
  currentCatalogLocale: LocaleCode;
  value: FamilyVariantCode[];
  onChange?: (value: FamilyVariantCode[]) => void;
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
  familyVariants: IndexedFamilyVariants,
  currentCatalogLocale: LocaleCode
) => {
  return {
    more: 20 === Object.keys(familyVariants).length,
    results: Object.keys(familyVariants).map(
      (familyVariantIdentifier): Select2Option => {
        return {
          id: familyVariants[familyVariantIdentifier].code,
          text:
            familyVariants[familyVariantIdentifier].labels[
              currentCatalogLocale
            ] || `[${familyVariants[familyVariantIdentifier].code}]`,
        };
      }
    ),
  };
};

const initSelectedFamilyVariants = async (
  router: Router,
  selectedFamilyVariantCodes: FamilyVariantCode[],
  currentCatalogLocale: LocaleCode,
  callback: InitSelectionCallback
): Promise<void> => {
  const families: IndexedFamilyVariants = await getFamilyVariantsByIdentifiers(
    selectedFamilyVariantCodes,
    router
  );

  callback(
    selectedFamilyVariantCodes.map(familyVariantCode => {
      return families[familyVariantCode]
        ? {
            id: familyVariantCode,
            text:
              families[familyVariantCode].labels[currentCatalogLocale] ||
              `[${familyVariantCode}]`,
          }
        : {
            id: familyVariantCode,
            text: `[${familyVariantCode}]`,
          };
    })
  );
};

const FamilyVariantsSelector: React.FC<Props> = ({
  id,
  label,
  hiddenLabel = false,
  currentCatalogLocale,
  value,
  onChange,
  name,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const handleChange = (value: Select2Value[]) => {
    if (onChange) {
      onChange(value as FamilyVariantCode[]);
    }
  };

  const ajax = React.useMemo<Select2Ajax>(() => {
    return {
      url: router.generate('pim_enrich_family_variant_rest_index'),
      quietMillis: 250,
      cache: true,
      data: (term: string, page: number) =>
        dataProvider(term, page, currentCatalogLocale),
      results: (familyVariants: IndexedFamilyVariants) =>
        handleResults(familyVariants, currentCatalogLocale),
    };
  }, [currentCatalogLocale, router]);

  return (
    <Select2MultiAsyncWrapper
      {...remainingProps}
      name={name}
      data-test-id={id}
      label={
        label || translate('pim_enrich.entity.family_variant.plural_label')
      }
      hiddenLabel={hiddenLabel}
      value={value}
      onChange={handleChange}
      ajax={ajax}
      initSelection={(_element, callback) =>
        initSelectedFamilyVariants(
          router,
          value,
          currentCatalogLocale,
          callback
        )
      }
    />
  );
};

export { FamilyVariantsSelector };
