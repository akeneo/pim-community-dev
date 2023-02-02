import {Nomenclature, Operator} from '../models';

const nomenclature: Nomenclature = {
  operator: Operator.EQUALS,
  propertyCode: 'family',
  generate_if_empty: false,
  value: 3,
  values: {},
};

const useGetNomenclature = () => {
  return {nomenclature}
}

export {useGetNomenclature};
