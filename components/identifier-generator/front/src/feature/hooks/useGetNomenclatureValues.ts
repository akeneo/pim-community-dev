import {useGetFamilies} from './useGetFamilies';
import {useCallback, useMemo, useState} from 'react';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributeCode,
  Family,
  FamilyCode,
  FamilyProperty,
  Nomenclature,
  NomenclatureFilter,
  NomenclatureLineEditProps,
  NomenclatureValues,
  PROPERTY_NAMES,
  SimpleSelect,
  SimpleSelectProperty,
} from '../models';
import {useNomenclatureDisplay} from './useNomenclatureDisplay';
import {useGetSelectOptions} from './useGetSelectOptions';

type HookResult = {
  data: NomenclatureLineEditProps[];
  page: number;
  setPage: (page: number) => void;
  search: string;
  setSearch: (search: string) => void;
  totalValuesCount: number;
  filteredValuesCount: number;
  hasValueInvalid: boolean;
};

const useGetNomenclatureValues = (
  nomenclature: Nomenclature | undefined,
  filter: NomenclatureFilter | undefined,
  values: NomenclatureValues | undefined,
  itemsPerPage: number,
  selectedProperty: FamilyProperty | SimpleSelectProperty
): HookResult => {
  const [page, setPage] = useState<number>(1);
  const [search, setSearch] = useState<string>('');
  const {isValid, getPlaceholder} = useNomenclatureDisplay(nomenclature);
  const lowerCaseSearch = useMemo(() => search.toLowerCase(), [search]);
  const [filteredValuesCount, setFilteredValuesCount] = useState<number>(0);
  const userContext = useUserContext();
  const [hasValueInvalid, setHasValueInvalid] = useState<boolean>(false);
  const typeSelectedProperty = selectedProperty.type;
  const attributeCode =
    selectedProperty.type === PROPERTY_NAMES.SIMPLE_SELECT ? selectedProperty.attributeCode : undefined;

  const {data: families} = useGetFamilies({
    limit: -1,
    enabled: typeSelectedProperty === PROPERTY_NAMES.FAMILY,
  });

  const {data: options} = useGetSelectOptions({
    attributeCode: attributeCode ?? '',
    enabled: typeSelectedProperty === PROPERTY_NAMES.SIMPLE_SELECT,
    limit: -1,
  });

  const getValueBeforeUserUpdate = useCallback(
    (entityCode: FamilyCode | AttributeCode) => {
      return nomenclature?.values[entityCode];
    },
    [nomenclature]
  );

  const getValueAfterUserUpdate = useCallback(
    (entityCode: FamilyCode | AttributeCode) => {
      return values?.[entityCode];
    },
    [values]
  );

  const getValueBeforeUserUpdateOrPlaceholder = useCallback(
    (entityCode: FamilyCode | AttributeCode) => {
      return getValueBeforeUserUpdate(entityCode) || getPlaceholder(entityCode);
    },
    [getValueBeforeUserUpdate, getPlaceholder]
  );

  const getValueAfterUserUpdateOrPlaceholder = useCallback(
    (entityCode: FamilyCode | AttributeCode) => {
      return getValueAfterUserUpdate(entityCode) || getPlaceholder(entityCode);
    },
    [getValueAfterUserUpdate, getPlaceholder]
  );

  const getEntityLabel = useCallback(
    (entity: Family | SimpleSelect) => {
      return getLabel(entity.labels, userContext.get('catalogLocale'), entity.code);
    },
    [userContext]
  );

  const entityMatchSearch = useCallback(
    (entity: Family | SimpleSelect) => {
      return (
        (getValueAfterUserUpdate(entity.code)?.toLowerCase() || '').includes(lowerCaseSearch) ||
        (entity.code.toLowerCase() || '').includes(lowerCaseSearch) ||
        (getEntityLabel(entity).toLowerCase() || '').includes(lowerCaseSearch)
      );
    },
    [getEntityLabel, getValueAfterUserUpdate, lowerCaseSearch]
  );

  const matchFilter = useCallback(
    (code: FamilyCode | AttributeCode) => {
      switch (filter) {
        case 'all':
          return true;
        case 'error':
          return !isValid(getValueBeforeUserUpdateOrPlaceholder(code));
        case 'empty':
          return !getValueBeforeUserUpdate(code);
        case 'filled':
        default:
          return getValueBeforeUserUpdate(code) && getValueBeforeUserUpdate(code) !== '';
      }
    },
    [filter, isValid, getValueBeforeUserUpdate, getValueBeforeUserUpdateOrPlaceholder]
  );

  const getLineValue = useCallback(
    (entity: Family | SimpleSelect): NomenclatureLineEditProps => ({
      code: entity.code,
      label: getEntityLabel(entity),
      value: getValueAfterUserUpdate(entity.code) || '',
    }),
    [getEntityLabel, getValueAfterUserUpdate]
  );

  const data = useMemo(() => {
    if (!families && !options) return [];

    let filteredButNotDisplayedDataCount = 0;
    let filteredValuesCount = 0;
    let hasNomenclatureValueInvalid = false;
    const filteredData: NomenclatureLineEditProps[] = [];
    const firstIndexToDisplay = (page - 1) * itemsPerPage;

    const addFilteredData = (family: Family) => {
      filteredValuesCount++;
      const currentIndex = filteredButNotDisplayedDataCount + filteredData.length;

      if (currentIndex >= firstIndexToDisplay && currentIndex < firstIndexToDisplay + itemsPerPage) {
        filteredData.push(getLineValue(family));
      } else {
        filteredButNotDisplayedDataCount++;
      }
    };

    if (families && selectedProperty.type === PROPERTY_NAMES.FAMILY) {
      for (const family of families) {
        hasNomenclatureValueInvalid =
          hasNomenclatureValueInvalid || !isValid(getValueAfterUserUpdateOrPlaceholder(family.code));

        if (entityMatchSearch(family) && matchFilter(family.code)) addFilteredData(family);
      }
    }

    if (options && selectedProperty.type === PROPERTY_NAMES.SIMPLE_SELECT) {
      for (const option of options) {
        hasNomenclatureValueInvalid =
          hasNomenclatureValueInvalid || !isValid(getValueAfterUserUpdateOrPlaceholder(option.code));

        if (entityMatchSearch(option) && matchFilter(option.code)) addFilteredData(option);
      }
    }

    setFilteredValuesCount(filteredValuesCount);
    setHasValueInvalid(hasNomenclatureValueInvalid);

    return filteredData;
  }, [
    families,
    options,
    page,
    itemsPerPage,
    getLineValue,
    isValid,
    getValueAfterUserUpdateOrPlaceholder,
    entityMatchSearch,
    matchFilter,
    selectedProperty,
  ]);

  const innerSetSearch = (search: string) => {
    setPage(1);
    setSearch(search);
  };

  return {
    data,
    page,
    setPage,
    search,
    setSearch: innerSetSearch,
    filteredValuesCount,
    totalValuesCount:
      (selectedProperty?.type === PROPERTY_NAMES.SIMPLE_SELECT ? options?.length : families?.length) ?? 0,
    hasValueInvalid,
  };
};

export {useGetNomenclatureValues};
