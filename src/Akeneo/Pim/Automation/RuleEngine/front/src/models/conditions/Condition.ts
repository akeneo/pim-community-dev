import { Router } from '../../dependenciesTools';
import {
  FallbackCondition,
  FamilyCondition,
  SimpleMultiOptionsAttributeCondition,
  PimCondition,
  TextAttributeCondition,
  CategoryCondition,
  CompletenessCondition,
  DateAttributeCondition,
  DateSystemCondition,
  SimpleMultiReferenceEntitiesAttributeCondition,
  TextareaAttributeCondition,
  BooleanAttributeCondition,
  GroupsCondition,
  NumberAttributeCondition,
  StatusCondition,
} from './';

export type Condition =
  | CategoryCondition
  | CompletenessCondition
  | DateSystemCondition
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition
  | SimpleMultiOptionsAttributeCondition
  | DateAttributeCondition
  | SimpleMultiReferenceEntitiesAttributeCondition
  | TextareaAttributeCondition
  | BooleanAttributeCondition
  | GroupsCondition
  | NumberAttributeCondition
  | StatusCondition;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
