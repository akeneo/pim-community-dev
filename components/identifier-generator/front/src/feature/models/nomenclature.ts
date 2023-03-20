import {Operator} from './conditions/operator';
import {AttributeCode} from './attribute';

type NomenclatureValues = {[code: string]: string};

type Nomenclature = {
  propertyCode: 'family' | AttributeCode;
  operator: Operator.EQUALS | Operator.LOWER_OR_EQUAL_THAN | null;
  generate_if_empty: boolean;
  value: number | null;
  values: NomenclatureValues;
};

export type {Nomenclature, NomenclatureValues};
