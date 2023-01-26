import {AbbreviationType} from './abbreviationType';
import {PROPERTY_NAMES} from '../structure';
import {Operator} from '../conditions/operator';

type FamilyCodeProperty = {
  type: PROPERTY_NAMES.FAMILY;
  process: {
    type: AbbreviationType | null;

    operator?: Operator | null;
    value?: number | null;
  };
};

const FamilyCodeOperators: Operator[] = [Operator.EQUAL, Operator.EQUAL_OR_LESS];

export {FamilyCodeOperators};
export type {FamilyCodeProperty};
