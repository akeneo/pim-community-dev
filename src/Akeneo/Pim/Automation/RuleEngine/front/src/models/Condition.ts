import { FallbackCondition } from './FallbackCondition';
import { FamilyCondition } from './FamilyCondition';
import { PimCondition } from './PimCondition';
import { TextAttributeCondition } from './TextAttributeCondition';
import { Router } from '../dependenciesTools';

export type Condition =
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition;

export type ConditionFactoryType = (
  json: any,
  router: Router
) => Promise<Condition | null>;
