import {AbbreviationType} from './abbreviationType';
import {PROPERTY_NAMES} from '../structure';
import {Operator} from '../conditions/operator';

type FamilyProperty = {
  type: PROPERTY_NAMES.FAMILY;
  process: {
    type: AbbreviationType.NO;
  } | {
    type: AbbreviationType.TRUNCATE;
    operator: Operator | null;
    value: number;
} | { type: null};
};

const FamilyPropertyOperators: Operator[] = [Operator.EQUAL, Operator.EQUAL_OR_LESS];

export {FamilyPropertyOperators};
export type {FamilyProperty};
