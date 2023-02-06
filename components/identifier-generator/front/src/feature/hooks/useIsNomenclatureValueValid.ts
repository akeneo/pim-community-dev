import {useCallback} from 'react';
import {Nomenclature, Operator} from '../models';

const usePlaceholder: (nomenclature?: Nomenclature) => (code: string) => string = nomenclature => {
  return useCallback(
    (code: string) => {
      if (nomenclature && nomenclature.generate_if_empty) {
        return code.substr(0, nomenclature.value || 0);
      }
      return '';
    },
    [nomenclature]
  );
};

// TODO Merge these 2 methods in once
const useIsNomenclatureValueValid: (nomenclature?: Nomenclature) => (value: string) => boolean = nomenclature => {
  return useCallback(
    (value: string) => {
      if (!nomenclature || null === nomenclature.value || null === nomenclature.operator) {
        return true;
      }
      if (nomenclature.generate_if_empty && value === '') {
        return true;
      }
      if (nomenclature.operator === Operator.EQUALS && value.length !== nomenclature.value) {
        return false;
      }
      if (nomenclature.operator === Operator.LOWER_OR_EQUAL_THAN && value.length > nomenclature.value) {
        return false;
      }

      return true;
    },
    [nomenclature]
  );
};

export {useIsNomenclatureValueValid, usePlaceholder};
