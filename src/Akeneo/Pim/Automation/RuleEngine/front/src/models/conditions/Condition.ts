import { Router } from '../../dependenciesTools';
import {
  FallbackCondition,
  FamilyCondition,
  MultiOptionsAttributeCondition,
  PimCondition,
  TextAttributeCondition,
  CategoryCondition,
} from './';

export type Condition =
  | CategoryCondition
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition
  | MultiOptionsAttributeCondition;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
