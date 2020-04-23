import { FallbackCondition } from './FallbackCondition';
import { FamilyCondition } from './FamilyCondition';
import { PimCondition } from './PimCondition';
import { TextAttributeCondition } from './TextAttributeCondition';

export type Condition =
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition;
