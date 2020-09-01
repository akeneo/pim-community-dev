import { TextAttributeConditionLine } from '../../pages/EditRules/components/conditions/TextAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { AttributeType } from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const TextAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.STARTS_WITH,
  Operator.DOES_NOT_CONTAIN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type TextAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createTextAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.TEXT],
    Operator.IS_EMPTY
  );
};

const getTextAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    TextAttributeOperators,
    [AttributeType.TEXT],
    TextAttributeConditionLine
  );
};

export {
  TextAttributeOperators,
  TextAttributeCondition,
  getTextAttributeConditionModule,
  createTextAttributeCondition,
};
