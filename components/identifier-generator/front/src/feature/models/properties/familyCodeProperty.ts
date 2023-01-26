import {AbbreviationType} from './abbreviationType';
import {PROPERTY_NAMES} from '../structure';
import {Operator} from '../conditions/operator';

type FamilyCodeProperty = {
  type: PROPERTY_NAMES.FAMILY_CODE,
  abbreviation_type: AbbreviationType | null,
  operator: Operator | null
};

const FamilyCodeOperators: Operator[] = [Operator.IN, Operator.NOT_IN, Operator.EMPTY, Operator.NOT_EMPTY];

export {FamilyCodeOperators};
export type {FamilyCodeProperty};
