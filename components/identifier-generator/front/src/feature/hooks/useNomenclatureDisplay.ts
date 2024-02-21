import {useCallback} from 'react';
import {FamilyCode, Nomenclature, Operator, AttributeCode} from '../models';

const useNomenclatureDisplay: (nomenclature?: Nomenclature) => {
  getPlaceholder: (entityCode: FamilyCode | AttributeCode) => string;
  isValid: (nomenclatureValue: string) => boolean;
} = nomenclature => {
  const getPlaceholder = useCallback(
    (familyCode: string) => {
      if (nomenclature && nomenclature.generate_if_empty) {
        return familyCode.substr(0, nomenclature.value || 0);
      }
      return '';
    },
    [nomenclature]
  );

  const isValid = useCallback(
    (nomenclatureValue: string) => {
      return (
        !nomenclature ||
        null === nomenclature.value ||
        null === nomenclature.operator ||
        (nomenclature.generate_if_empty && nomenclatureValue === '') ||
        (!(nomenclature.operator === Operator.EQUALS && nomenclatureValue.length !== nomenclature.value) &&
          !(nomenclature.operator === Operator.LOWER_OR_EQUAL_THAN && nomenclatureValue.length > nomenclature.value))
      );
    },
    [nomenclature]
  );

  return {
    getPlaceholder,
    isValid,
  };
};

export {useNomenclatureDisplay};
