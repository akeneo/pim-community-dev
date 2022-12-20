import {CONDITION_NAMES} from './conditions';
import {FamilyCode} from '../family';
import {Operator} from './operator';

type FamilyCondition = {
  type: CONDITION_NAMES.FAMILY;
  operator: Operator.IN | Operator.NOT_IN | Operator.EMPTY | Operator.NOT_EMPTY;
  value?: FamilyCode[];
} & (
  | {
      type: CONDITION_NAMES.FAMILY;
      operator: Operator.IN | Operator.NOT_IN;
      value: FamilyCode[];
    }
  | {
      type: CONDITION_NAMES.FAMILY;
      operator: Operator.EMPTY | Operator.NOT_EMPTY;
    }
);

const FamilyOperators: Operator[] = [Operator.IN, Operator.NOT_IN, Operator.EMPTY, Operator.NOT_EMPTY];

export {FamilyOperators};
export type {FamilyCondition};
