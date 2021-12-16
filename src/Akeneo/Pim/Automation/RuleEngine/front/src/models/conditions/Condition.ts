import {Router} from '../../dependenciesTools';
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
  MeasurementAttributeCondition,
  NumberAttributeCondition,
  StatusCondition,
  TableAttributeCondition,
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
  | MeasurementAttributeCondition
  | NumberAttributeCondition
  | TableAttributeCondition
  | StatusCondition;

export type ConditionFactory = (
  fieldCode: string,
  router: Router
) => Promise<Condition | null>;
