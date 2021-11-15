import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {SimpleMultiOptionsAttributeConditionLine} from '../../pages/EditRules/components/conditions/SimpleMultiOptionsAttributeConditionLine';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const TYPES: AttributeType[] = [
  AttributeType.OPTION_SIMPLE_SELECT,
  AttributeType.OPTION_MULTI_SELECT,
];

const SimpleMultiOptionsAttributeOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type SimpleMultiOptionsAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createSimpleMultiOptionsAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(fieldCode, router, TYPES, Operator.IS_EMPTY);
};

const getSimpleMultiOptionsAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    SimpleMultiOptionsAttributeOperators,
    TYPES,
    SimpleMultiOptionsAttributeConditionLine
  );
};

export {
  SimpleMultiOptionsAttributeOperators,
  SimpleMultiOptionsAttributeCondition,
  getSimpleMultiOptionsAttributeConditionModule,
  createSimpleMultiOptionsAttributeCondition,
};
