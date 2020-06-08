import { Router } from '../../dependenciesTools';
import {
  FallbackCondition,
  FamilyCondition,
  MultiOptionsAttributeCondition,
  PimCondition,
  TextAttributeCondition,
  CategoryCondition,
} from './';
import { ConditionLineProps } from '../../pages/EditRules/components/conditions/ConditionLineProps';

export type Condition =
  | CategoryCondition
  | FallbackCondition
  | PimCondition
  | FamilyCondition
  | TextAttributeCondition
  | MultiOptionsAttributeCondition;

export type ConditionModuleGuesser = (
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps & { condition: Condition }> | null>;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
