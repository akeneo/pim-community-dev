import {useGetFamilies} from './useGetFamilies';
import {useCallback, useMemo, useState} from 'react';
import {getLabel} from '@akeneo-pim-community/shared';
import {
  Family,
  Nomenclature,
  NomenclatureFilter,
  NomenclatureLineEditProps,
  NomenclatureValues,
  Operator,
} from '../models';
import {useIsNomenclatureValueValid} from './useIsNomenclatureValueValid';

const ITEM_PER_PAGE = 25;

const useGetFamilyNomenclatureValues = (
  nomenclature?: Nomenclature,
  filter?: NomenclatureFilter,
  values?: NomenclatureValues
): {
  data: NomenclatureLineEditProps[];
  page: number;
  setPage: (page: number) => void;
} => {
  const [page, setPage] = useState<number>(1);
  const isValid = useIsNomenclatureValueValid(nomenclature);
  // const [offset, setOffset] = useState(0);
  // const [limit, setLimit] = useState(ITEM_PER_PAGE);

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

    const toto = (family: Family) => {
      const currentIndex = filteredButNotDisplayedDataCount + filteredData.length;
      if (currentIndex >= firstIndexToDisplay) {
        filteredData.push(getLineFromFamily(family));
      } else {
        filteredButNotDisplayedDataCount++;
      }
    };

    for (const family of families) {
      if (filteredButNotDisplayedDataCount + filteredData.length >= page * ITEM_PER_PAGE) {
        break;
      }

      switch (filter) {
        case 'all':
          toto(family);
          break;
        case 'error': {
          if (!isValid(nomenclature?.values[family.code] || '')) toto(family);
          break;
        }
        case 'empty':
          if (!nomenclature?.values[family.code]) toto(family);
          break;
        case 'filled':
          if (nomenclature?.values[family.code] && nomenclature?.values[family.code] !== '') toto(family);
          break;
      }
    }

    return filteredData;
  }, [families, filter, getLineFromFamily, nomenclature, page]);

  return {data, page, setPage};
};

export {useGetFamilyNomenclatureValues};
