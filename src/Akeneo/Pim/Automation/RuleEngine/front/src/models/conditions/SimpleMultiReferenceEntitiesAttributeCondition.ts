import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {SimpleMultiReferenceEntitiesAttributeConditionLine} from '../../pages/EditRules/components/conditions/SimpleMultiReferenceEntitiesAttributeConditionLine';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const TYPES: AttributeType[] = [
  AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
  AttributeType.REFERENCE_ENTITY_COLLECTION,
];

const SimpleMultiReferenceEntitiesAttributeOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type SimpleMultiReferenceEntitiesAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createSimpleMultiReferenceEntitiesAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(fieldCode, router, TYPES, Operator.IS_EMPTY);
};

const getSimpleMultiReferenceEntitiesAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    SimpleMultiReferenceEntitiesAttributeOperators,
    TYPES,
    SimpleMultiReferenceEntitiesAttributeConditionLine
  );
};

export {
  SimpleMultiReferenceEntitiesAttributeOperators,
  SimpleMultiReferenceEntitiesAttributeCondition,
  getSimpleMultiReferenceEntitiesAttributeConditionModule,
  createSimpleMultiReferenceEntitiesAttributeCondition,
};
