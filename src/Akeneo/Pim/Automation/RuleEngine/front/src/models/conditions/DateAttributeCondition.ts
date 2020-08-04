import { Operator } from '../Operator';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { DateAttributeConditionLine } from '../../pages/EditRules/components/conditions/DateConditionLines';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { LocaleCode } from '../Locale';
import { ScopeCode } from '../Scope';
import { Router } from '../../dependenciesTools';
import { AttributeCode } from '../Attribute';
import { DateOperator } from '../../pages/EditRules/components/conditions/DateConditionLines/dateConditionLines.type';

const TYPE = 'pim_catalog_date';

const dateAttributeOperators = [
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

const isDateAttributeOperatorValid = (
  operator: any
): operator is DateOperator => dateAttributeOperators.includes(operator);

const jsonValueIsValid = (value: any): boolean =>
  typeof value === 'string' || Array.isArray(value) || !value;

const dateAttributeConditionPredicate = (json: any): boolean => {
  return (
    typeof json.field === 'string' &&
    isDateAttributeOperatorValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getDateAttributeConditionModule: ConditionModuleGuesser = async (
  json: any,
  router: Router
) => {
  if (!dateAttributeConditionPredicate(json)) {
    return null;
  }
  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }
  return DateAttributeConditionLine;
};

const createDateAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<DateAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (!attribute || attribute.type !== TYPE) {
    return null;
  }
  return {
    field: fieldCode,
    operator: Operator.IS_EMPTY,
    value: '',
  };
};

export {
  DateAttributeCondition,
  createDateAttributeCondition,
  DateOperator,
  getDateAttributeConditionModule,
  dateAttributeOperators,
};
