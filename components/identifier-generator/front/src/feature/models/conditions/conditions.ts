import {EnabledCondition} from './enabledCondition';
import {FamilyCondition} from './familyCondition';
import {SimpleSelectCondition} from './simpleSelectCondition';

enum CONDITION_NAMES {
  ENABLED = 'enabled',
  FAMILY = 'family',
  SIMPLE_SELECT = 'simple_select',
}

type Condition = {
  type: CONDITION_NAMES;
} & (EnabledCondition | FamilyCondition | SimpleSelectCondition);

type Conditions = Condition[];

const ALLOWED_CONDITION_NAMES = [CONDITION_NAMES.ENABLED, CONDITION_NAMES.FAMILY];

export {CONDITION_NAMES, ALLOWED_CONDITION_NAMES};
export type {Conditions, Condition};
