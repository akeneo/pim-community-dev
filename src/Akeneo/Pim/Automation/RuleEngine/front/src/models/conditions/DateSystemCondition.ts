import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import {
  DateValue,
  DateOperator,
} from '../../pages/EditRules/components/conditions/DateConditionLines/dateConditionLines.type';
import { DateSystemConditionLine } from '../../pages/EditRules/components/conditions/DateConditionLines';

const CREATED_FIELD = 'created';
const UPDATED_FIELD = 'updated';

const fields = [CREATED_FIELD, UPDATED_FIELD];

const dateSystemOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.GREATER_THAN,
  Operator.BETWEEN,
  Operator.NOT_BETWEEN,
];

type DateSystemCondition = {
  field: 'created' | 'updated';
  operator: DateOperator;
  value: DateValue;
};

const isDateSystemOperatorValid = (operator: any): operator is DateOperator =>
  dateSystemOperators.includes(operator);

const jsonValueIsValid = (value: any): boolean =>
  typeof value === 'string' || Array.isArray(value) || !value;

const dateSystemConditionPredicate = (json: any): boolean => {
  return (
    fields.includes(json.field) &&
    isDateSystemOperatorValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getDateSystemConditionModule: ConditionModuleGuesser = async (
  json: any
) => {
  if (!dateSystemConditionPredicate(json)) {
    return Promise.resolve(null);
  }
  return Promise.resolve(DateSystemConditionLine);
};

const createDateSystemCondition: ConditionFactory = async (
  fieldCode: string
): Promise<DateSystemCondition | null> => {
  if (!fields.includes(fieldCode)) {
    return Promise.resolve(null);
  }
  return Promise.resolve({
    field: fieldCode as 'created' | 'updated',
    operator: Operator.EQUALS,
    value: '',
  });
};

export {
  createDateSystemCondition,
  DateSystemCondition,
  dateSystemOperators,
  getDateSystemConditionModule,
};
