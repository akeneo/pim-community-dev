import { Router } from '../../dependenciesTools';
import {
  FallbackCondition,
  FamilyCondition,
  MultiOptionsAttributeCondition,
  PimCondition,
  TextAttributeCondition,
} from './';

export type Condition =
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition
  | MultiOptionsAttributeCondition;

export type ConditionDenormalizer = (
  json: any,
  router: Router
) => Promise<Condition | null>;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
