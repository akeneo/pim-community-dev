import {AbbreviationType} from './abbreviationType';
import {PROPERTY_NAMES} from '../structure';
import {Operator} from '../conditions/operator';

type FamilyCodeProperty = {
  type: PROPERTY_NAMES.FAMILY_CODE,
  abbreviation_type: AbbreviationType | null,
  operator: Operator | null,
  charsNumber: number | null
};

const FamilyCodeOperators: Operator[] = [Operator.EQUAL, Operator.EQUAL_OR_LESS];

export {FamilyCodeOperators};
export type {FamilyCodeProperty};
