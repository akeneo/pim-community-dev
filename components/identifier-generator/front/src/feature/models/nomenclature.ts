import {Operator} from './conditions/operator';

type NomenclatureValues = {[code: string]: string};

type Nomenclature = {
  propertyCode: 'family',
  operator: Operator.EQUALS|Operator.LOWER_OR_EQUAL_THAN,
  generate_if_empty: boolean,
  value?: number,
  values: NomenclatureValues,
}

export type {Nomenclature, NomenclatureValues};
