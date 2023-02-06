import {Operator} from './conditions/operator';

type NomenclatureValues = {[code: string]: string};

type Nomenclature = {
  propertyCode: 'family';
  operator: Operator.EQUALS | Operator.LOWER_OR_EQUAL_THAN | null;
  generate_if_empty: boolean;
  value: number | null;
  values: NomenclatureValues;
};

export type {Nomenclature, NomenclatureValues};
