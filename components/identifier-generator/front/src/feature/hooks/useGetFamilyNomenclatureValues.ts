import {useGetFamilies} from './useGetFamilies';
import {useCallback, useMemo, useState} from 'react';
import {getLabel} from '@akeneo-pim-community/shared';
import {Family, Nomenclature, NomenclatureFilter, NomenclatureLineEditProps, NomenclatureValues} from '../models';
import {useIsNomenclatureValueValid, usePlaceholder} from './useIsNomenclatureValueValid';

const ITEM_PER_PAGE = 25;

const useGetFamilyNomenclatureValues = (
  nomenclature?: Nomenclature,
  filter?: NomenclatureFilter,
  values?: NomenclatureValues
): {
  data: NomenclatureLineEditProps[];
  page: number;
  setPage: (page: number) => void;
  search: string;
  setSearch: (search: string) => void;
} => {
  const [page, setPage] = useState<number>(1);
  const [search, setSearch] = useState<string>('');
  const isValid = useIsNomenclatureValueValid(nomenclature);
  const getPlaceholder = usePlaceholder(nomenclature);
  const lowerCaseSearch = useMemo(() => search.toLowerCase(), [search]);

  const {data: families} = useGetFamilies({
    page: 1,
    search: '',
    limit: 8000, // TODO
  });

  const getLineFromFamily = useCallback(
    (family: Family): NomenclatureLineEditProps => ({
      code: family.code,
      label: getLabel(family.labels, 'en_US', family.code), // TODO: remove en_US
      value: values?.[family.code] || '',
    }),
    [values]
  );

  const data = useMemo(() => {
    if (!families) return [];

    const filteredData: NomenclatureLineEditProps[] = [];
    let filteredButNotDisplayedDataCount = 0;
    const firstIndexToDisplay = (page - 1) * ITEM_PER_PAGE;

    const addData = (family: Family) => {
      const value = values?.[family.code];
      if (
        (value?.toLowerCase() || '').includes(lowerCaseSearch) ||
        (family.code.toLowerCase() || '').includes(lowerCaseSearch) ||
        (getLabel(family.labels, 'en_US', family.code) || '').includes(lowerCaseSearch) // TODO
      ) {
        const currentIndex = filteredButNotDisplayedDataCount + filteredData.length;
        if (currentIndex >= firstIndexToDisplay) {
          filteredData.push(getLineFromFamily(family));
        } else {
          filteredButNotDisplayedDataCount++;
        }
      }
    };

    for (const family of families) {
      if (filteredButNotDisplayedDataCount + filteredData.length >= page * ITEM_PER_PAGE) {
        break;
      }

      switch (filter) {
        case 'all':
          addData(family);
          break;
        case 'error': {
          if (!isValid(nomenclature?.values[family.code] || getPlaceholder(family.code))) addData(family);
          break;
        }
        case 'empty':
          if (!nomenclature?.values[family.code]) addData(family);
          break;
        case 'filled':
          if (nomenclature?.values[family.code] && nomenclature?.values[family.code] !== '') addData(family);
          break;
      }
    }

    return filteredData;
  }, [families, filter, getLineFromFamily, nomenclature, page, isValid, lowerCaseSearch, values, getPlaceholder]);

  const mySetSearch = (search: string) => {
    setPage(1);
    setSearch(search);
  };

  return {data, page, setPage, search, setSearch: mySetSearch};
};

export {useGetFamilyNomenclatureValues};
