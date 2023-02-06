import {useCallback} from 'react';
import {Nomenclature, Operator} from '../models';

const useIsNomenclatureValueValid: (nomenclature?: Nomenclature) => ((value: string) => boolean) = (nomenclature) => {
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
}

export {useIsNomenclatureValueValid};
