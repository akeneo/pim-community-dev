import { BooleanAttributeConditionLine } from '../../pages/EditRules/components/conditions/BooleanAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { AttributeType } from '../Attribute';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const BooleanAttributeOperators = [Operator.EQUALS, Operator.NOT_EQUAL];

type BooleanAttributeCondition = {
  field: string;
  operator: Operator;
  value: boolean;
  scope?: string;
  locale?: string;
};

const createBooleanAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.BOOLEAN],
    Operator.EQUALS,
    false
  );
};

const getBooleanAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    BooleanAttributeOperators,
    [AttributeType.BOOLEAN],
    BooleanAttributeConditionLine
  );
};

export {
  BooleanAttributeOperators,
  BooleanAttributeCondition,
  getBooleanAttributeConditionModule,
  createBooleanAttributeCondition,
};
