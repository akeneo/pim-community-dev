import {Operator} from './conditions/operator';

type Nomenclature = {
  propertyCode: 'family',
  operator: Operator.EQUALS|Operator.LOWER_OR_EQUAL_THAN,
  generate_if_empty: boolean,
  value?: number,
  values: {[code: string]: string},
}

export type {Nomenclature};
