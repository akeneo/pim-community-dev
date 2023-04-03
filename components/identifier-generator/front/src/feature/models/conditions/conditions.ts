import {EnabledCondition} from './enabledCondition';
import {FamilyCondition} from './familyCondition';
import {SimpleOrMultiSelectCondition} from './simpleOrMultiSelectCondition';
import {CategoriesCondition} from './categoriesCondition';

enum CONDITION_NAMES {
  ENABLED = 'enabled',
  FAMILY = 'family',
  SIMPLE_SELECT = 'simple_select',
  MULTI_SELECT = 'multi_select',
  CATEGORIES = 'category',
}

type Condition = {
  type: CONDITION_NAMES;
} & (EnabledCondition | FamilyCondition | CategoriesCondition | SimpleOrMultiSelectCondition);

type Conditions = Condition[];

const ALLOWED_CONDITION_NAMES = [
  CONDITION_NAMES.ENABLED,
  CONDITION_NAMES.FAMILY,
  CONDITION_NAMES.SIMPLE_SELECT,
  CONDITION_NAMES.MULTI_SELECT,
  CONDITION_NAMES.CATEGORIES,
];

export {CONDITION_NAMES, ALLOWED_CONDITION_NAMES};
export type {Conditions, Condition};
