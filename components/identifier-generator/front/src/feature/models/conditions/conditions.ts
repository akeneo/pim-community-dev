import {EnabledCondition} from './enabledCondition';
import {FamilyCondition} from './familyCondition';
import {SimpleSelectCondition} from './simpleSelectCondition';
import {IdentifierCondition} from './identifierCondition';

enum CONDITION_NAMES {
  ENABLED = 'enabled',
  FAMILY = 'family',
  SIMPLE_SELECT = 'simple_select',
  IDENTIFIER = 'identifier',
}

type Condition = {
  type: CONDITION_NAMES;
  auto: boolean;
} & (EnabledCondition | FamilyCondition | SimpleSelectCondition | IdentifierCondition);

type Conditions = Condition[];

const ALLOWED_CONDITION_NAMES = [CONDITION_NAMES.ENABLED, CONDITION_NAMES.FAMILY, CONDITION_NAMES.SIMPLE_SELECT];

export {CONDITION_NAMES, ALLOWED_CONDITION_NAMES};
export type {Conditions, Condition};
