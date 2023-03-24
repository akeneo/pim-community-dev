import {AbbreviationType} from './abbreviationType';
import {PROPERTY_NAMES} from '../structure';
import {Operator} from '../conditions/operator';

type FamilyProperty = {
  type: PROPERTY_NAMES.FAMILY;
  process:
    | {
        type: AbbreviationType.NO;
      }
    | {
        type: AbbreviationType.TRUNCATE;
        operator: Operator | null;
        value: number | null;
      }
    | {
        type: AbbreviationType.NOMENCLATURE;
      }
    | {type: null};
};

const FamilyPropertyOperators: Operator[] = [Operator.EQUALS, Operator.LOWER_OR_EQUAL_THAN];

export {FamilyPropertyOperators};
export type {FamilyProperty};
