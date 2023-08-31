import {useCallback, useMemo, useState} from 'react';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {
  AttributeCode,
  CanUseNomenclatureProperty,
  Family,
  FamilyCode,
  Nomenclature,
  NomenclatureFilter,
  NomenclatureLineEditProps,
  NomenclatureValues,
  PROPERTY_NAMES,
  SimpleSelect,
} from '../models';
import {useNomenclatureDisplay} from './useNomenclatureDisplay';
import {useGetSelectOptions} from './useGetSelectOptions';
import {useGetFamilies} from './useGetFamilies';
import {useGetReferenceEntitiesRecord} from './useGetReferenceEntitiesRecord';

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
  selectedProperty: CanUseNomenclatureProperty
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
    selectedProperty.type === PROPERTY_NAMES.SIMPLE_SELECT || selectedProperty.type === PROPERTY_NAMES.REF_ENTITY
      ? selectedProperty.attributeCode
      : undefined;

  const {data: families = []} = useGetFamilies({
    limit: -1,
    enabled: typeSelectedProperty === PROPERTY_NAMES.FAMILY,
  });

  const {data: options = []} = useGetSelectOptions({
    attributeCode,
    enabled: typeSelectedProperty === PROPERTY_NAMES.SIMPLE_SELECT,
    limit: -1,
  });

  const {data: records} = useGetReferenceEntitiesRecord({
    attributeCode,
    enabled: typeSelectedProperty === PROPERTY_NAMES.REF_ENTITY,
    page,
    search,
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

  const items = useMemo(() => {
    if (selectedProperty.type === PROPERTY_NAMES.FAMILY) {
      return families;
    } else if (selectedProperty.type === PROPERTY_NAMES.REF_ENTITY) {
      return records?.items ?? [];
    } else {
      return options;
    }
  }, [selectedProperty, families, options, records]);

  const data = useMemo(() => {
    if (items.length === 0 && !records?.total_count) return [];

    let filteredButNotDisplayedDataCount = 0;
    let filteredValuesCount = 0;
    let hasNomenclatureValueInvalid = false;
    const filteredData: NomenclatureLineEditProps[] = [];
    const firstIndexToDisplay = (page - 1) * itemsPerPage;

    const addFilteredData = (entity: Family | SimpleSelect) => {
      filteredValuesCount++;
      const currentIndex = filteredButNotDisplayedDataCount + filteredData.length;

      if (currentIndex >= firstIndexToDisplay && currentIndex < firstIndexToDisplay + itemsPerPage) {
        filteredData.push(getLineValue(entity));
      } else {
        filteredButNotDisplayedDataCount++;
      }
    };

    for (const item of items) {
      hasNomenclatureValueInvalid =
        hasNomenclatureValueInvalid || !isValid(getValueAfterUserUpdateOrPlaceholder(item.code));

      if (entityMatchSearch(item) && matchFilter(item.code)) {
        addFilteredData(item);
      }
    }

    setFilteredValuesCount(filteredValuesCount);
    setHasValueInvalid(hasNomenclatureValueInvalid);

    return filteredData;
  },
  // eslint-disable-next-line react-hooks/exhaustive-deps
  [
    items,
    page,
    itemsPerPage,
    getLineValue,
    isValid,
    getValueAfterUserUpdateOrPlaceholder,
    entityMatchSearch,
    matchFilter,
    typeSelectedProperty,
    records,
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
    totalValuesCount: items.length,
    hasValueInvalid,
  };
};

export {useGetNomenclatureValues};
