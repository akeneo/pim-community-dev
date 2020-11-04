import {NumberAttributeConditionLine} from '../../pages/EditRules/components/conditions/NumberAttributeConditionLine';
import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {AttributeType} from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const NumberAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.LOWER_OR_EQUAL_THAN,
  Operator.GREATER_THAN,
  Operator.GREATER_OR_EQUAL_THAN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type NumberAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createNumberAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.NUMBER],
    Operator.IS_EMPTY
  );
};

const getNumberAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    NumberAttributeOperators,
    [AttributeType.NUMBER],
    NumberAttributeConditionLine
  );
};

export {
  NumberAttributeOperators,
  NumberAttributeCondition,
  getNumberAttributeConditionModule,
  createNumberAttributeCondition,
};
