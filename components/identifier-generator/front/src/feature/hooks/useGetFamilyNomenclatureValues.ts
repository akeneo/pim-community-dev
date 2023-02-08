import {useGetFamilies} from './useGetFamilies';
import {useCallback, useMemo, useState} from 'react';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {
  Family,
  FamilyCode,
  Nomenclature,
  NomenclatureFilter,
  NomenclatureLineEditProps,
  NomenclatureValues,
} from '../models';
import {useIsNomenclatureValueValid, usePlaceholder} from './useIsNomenclatureValueValid';

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

const useGetFamilyNomenclatureValues = (
  nomenclature?: Nomenclature,
  filter?: NomenclatureFilter,
  values?: NomenclatureValues,
  itemsPerPage: number
): HookResult => {
  const [page, setPage] = useState<number>(1);
  const [search, setSearch] = useState<string>('');
  const isValid = useIsNomenclatureValueValid(nomenclature);
  const getPlaceholder = usePlaceholder(nomenclature);
  const lowerCaseSearch = useMemo(() => search.toLowerCase(), [search]);
  const [filteredValuesCount, setFilteredValuesCount] = useState<number>(0);
  const userContext = useUserContext();
  const [hasValueInvalid, setHasValueInvalid] = useState<boolean>(false);

  const {data: families} = useGetFamilies({
    limit: -1,
  });

  const getValueBeforeUserUpdate = useCallback(
    (familyCode: FamilyCode) => {
      return nomenclature?.values[familyCode];
    },
    [nomenclature]
  );

  const getValueAfterUserUpdate = useCallback(
    (familyCode: FamilyCode) => {
      return values?.[familyCode];
    },
    [values]
  );

  const getValueBeforeUserUpdateOrPlaceholder = useCallback(
    (familyCode: FamilyCode) => {
      return getValueBeforeUserUpdate(familyCode) || getPlaceholder(familyCode);
    },
    [getValueBeforeUserUpdate, getPlaceholder]
  );

  const getFamilyLabel = useCallback(
    (family: Family) => {
      return getLabel(family.labels, userContext.get('catalogLocale'), family.code);
    },
    [userContext]
  );

  const familyMatchSearch = useCallback(
    (family: Family) => {
      return (
        (getValueAfterUserUpdate(family.code)?.toLowerCase() || '').includes(lowerCaseSearch) ||
        (family.code.toLowerCase() || '').includes(lowerCaseSearch) ||
        (getFamilyLabel(family).toLowerCase() || '').includes(lowerCaseSearch)
      );
    },
    [getFamilyLabel, getValueAfterUserUpdate, lowerCaseSearch]
  );

  const familyMatchFilter = useCallback(
    (familyCode: FamilyCode) => {
      switch (filter) {
        case 'all':
          return true;
        case 'error':
          return !isValid(getValueBeforeUserUpdateOrPlaceholder(familyCode));
        case 'empty':
          return !getValueBeforeUserUpdate(familyCode);
        case 'filled':
        default:
          return getValueBeforeUserUpdate(familyCode) && getValueBeforeUserUpdate(familyCode) !== '';
      }
    },
    [filter, isValid, getValueBeforeUserUpdate, getValueBeforeUserUpdateOrPlaceholder]
  );

  const getLineFromFamily = useCallback(
    (family: Family): NomenclatureLineEditProps => ({
      code: family.code,
      label: getFamilyLabel(family),
      value: getValueAfterUserUpdate(family.code) || '',
    }),
    [getFamilyLabel, getValueAfterUserUpdate]
  );

  const data = useMemo(() => {
    if (!families) return [];

    let filteredButNotDisplayedDataCount = 0;
    let filteredValuesCount = 0;
    let hasNomenclatureValueInvalid = false;
    const filteredData: NomenclatureLineEditProps[] = [];
    const firstIndexToDisplay = (page - 1) * itemsPerPage;

    const addFilteredData = (family: Family) => {
      filteredValuesCount++;
      const currentIndex = filteredButNotDisplayedDataCount + filteredData.length;

      if (currentIndex >= firstIndexToDisplay && currentIndex < firstIndexToDisplay + itemsPerPage) {
        filteredData.push(getLineFromFamily(family));
      } else {
        filteredButNotDisplayedDataCount++;
      }
    };

    for (const family of families) {
      hasNomenclatureValueInvalid =
        hasNomenclatureValueInvalid || !isValid(getValueBeforeUserUpdateOrPlaceholder(family.code));

      if (familyMatchSearch(family) && familyMatchFilter(family.code)) addFilteredData(family);
    }

    setFilteredValuesCount(filteredValuesCount);
    setHasValueInvalid(hasNomenclatureValueInvalid);

    return filteredData;
  }, [
    families,
    getLineFromFamily,
    page,
    itemsPerPage,
    isValid,
    getValueBeforeUserUpdateOrPlaceholder,
    familyMatchSearch,
    familyMatchFilter,
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
    totalValuesCount: families?.length || 0,
    hasValueInvalid,
  };
};

export {useGetFamilyNomenclatureValues};
