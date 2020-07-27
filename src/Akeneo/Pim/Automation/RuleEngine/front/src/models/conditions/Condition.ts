import { Router } from '../../dependenciesTools';
import {
  FallbackCondition,
  FamilyCondition,
  SimpleMultiOptionsAttributeCondition,
  PimCondition,
  TextAttributeCondition,
  CategoryCondition,
  CompletenessCondition,
} from './';

export type Condition =
  | CategoryCondition
  | CompletenessCondition
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition
  | SimpleMultiOptionsAttributeCondition;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
