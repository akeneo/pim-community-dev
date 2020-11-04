import {Operator} from '../Operator';
import {DateAttributeConditionLine} from '../../pages/EditRules/components/conditions/DateConditionLines';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {LocaleCode} from '../Locale';
import {ScopeCode} from '../Scope';
import {AttributeCode, AttributeType} from '../Attribute';
import {DateOperator} from '../../pages/EditRules/components/conditions/DateConditionLines/dateConditionLines.type';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';

const DateAttributeOperators = [
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.GREATER_THAN,
  Operator.BETWEEN,
  Operator.NOT_BETWEEN,
];

type DateValue = string | string[];

type DateAttributeCondition = {
  field: AttributeCode;
  locale?: LocaleCode;
  operator: DateOperator;
  scope?: ScopeCode;
  value: DateValue;
};

const createDateAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.DATE],
    Operator.IS_EMPTY,
    ''
  );
};

const getDateAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (!(typeof json.value === 'string' || Array.isArray(json.value))) {
    return null;
  }

  return getAttributeConditionModule(
    json,
    router,
    DateAttributeOperators,
    [AttributeType.DATE],
    DateAttributeConditionLine
  );
};

export {
  DateAttributeCondition,
  createDateAttributeCondition,
  DateOperator,
  getDateAttributeConditionModule,
  DateAttributeOperators,
};
