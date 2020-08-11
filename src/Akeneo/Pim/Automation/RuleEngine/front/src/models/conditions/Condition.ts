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
  | DateAttributeCondition;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
