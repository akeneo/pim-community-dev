import {useGetFamilies} from './useGetFamilies';
import {useCallback, useMemo, useState} from 'react';
import {getLabel} from '@akeneo-pim-community/shared';
import {
  Family,
  Nomenclature,
  NomenclatureFilter,
  NomenclatureLineEditProps,
  NomenclatureValues,
  Operator
} from '../models';

const useGetFamilyNomenclatureValues = (
  nomenclature?: Nomenclature,
  filter?: NomenclatureFilter,
  values?: NomenclatureValues
  ): { data: NomenclatureLineEditProps[] } => {
  const [offset, setOffset] = useState(0);
  const [limit, setLimit] = useState(25);
  console.log('filter', filter);

  const {data: families} = useGetFamilies({
    page: 1,
    search: '',
    limit: 8000
  });

  const getIsValid = useCallback((value: string) => {
    if (nomenclature?.value && nomenclature?.operator) {
      if (nomenclature.generate_if_empty && value === '') {
        return true;
      }
      if (nomenclature.operator === Operator.EQUALS && value.length !== nomenclature.value) {
        return false;
      }
      if (nomenclature.operator === Operator.LOWER_OR_EQUAL_THAN && value.length > nomenclature.value) {
        return false;
      }
    }
    return true;
  }, [nomenclature]);

  const getLineFromFamily = useCallback((family: Family): NomenclatureLineEditProps => ({
    code: family.code,
    label: getLabel(family.labels, 'en_US', family.code), // TODO: remove en_US
    value: values?.[family.code] || '',
    isValid: getIsValid(family.code)
  }), [getIsValid, values]);

  const data = useMemo(() => {
    if (!families) return [];

    const filteredData: NomenclatureLineEditProps[] = [];

    for (const family of families) {
      if (filteredData.length >= 25) {
        break;
      }
      switch (filter) {
        case 'all':
          filteredData.push(getLineFromFamily(family));
          break;
        case 'error': {
          const line = getLineFromFamily(family);
          if (!line.isValid) filteredData.push(line);
          break;
        }
        case 'empty':
          if (!nomenclature?.values[family.code]) filteredData.push(getLineFromFamily(family));
          break;
        case 'filled':
          if (nomenclature?.values[family.code] && nomenclature?.values[family.code] !== '') filteredData.push(getLineFromFamily(family));
          break;
      }
    }

    return filteredData;
  }, [families, filter, getLineFromFamily, nomenclature]);

  return {data};
};

export {useGetFamilyNomenclatureValues};
