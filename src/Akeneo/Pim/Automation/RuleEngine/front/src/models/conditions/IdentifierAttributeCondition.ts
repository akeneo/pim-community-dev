import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';
import { AttributeType } from '../Attribute';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { IdentifierAttributeConditionLine } from '../../pages/EditRules/components/conditions/IdentifierAttributeConditionLine';

const IdentifierAttributeOperators = [
  Operator.STARTS_WITH,
  Operator.CONTAINS,
  Operator.DOES_NOT_CONTAIN,
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
];

type IdentifierAttributeCondition = {
  field: string;
  operator: Operator;
  value?: string | string[];
};

const createIdentifierAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.IDENTIFIER],
    IdentifierAttributeOperators[0]
  );
};

const getIdentifierAttributeCondtionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    IdentifierAttributeOperators,
    [AttributeType.IDENTIFIER],
    IdentifierAttributeConditionLine
  );
};

export {
  IdentifierAttributeOperators,
  IdentifierAttributeCondition,
  getIdentifierAttributeCondtionModule,
  createIdentifierAttributeCondition,
};
