import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { IdentifierConditionLine } from '../../pages/EditRules/components/conditions/IdentifierConditionLine';
import { Identifier } from '../../components/Selectors/IdentifiersSelector';

const FIELD = 'identifier';

const IdentifierOperators = [
  Operator.STARTS_WITH,
  Operator.CONTAINS,
  Operator.DOES_NOT_CONTAIN,
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
];

type IdentifierCondition = {
  field: string;
  operator: Operator;
  value: string | Identifier[];
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    IdentifierOperators.includes(operator as Operator)
  );
};

const jsonValueIsValid = (value: any): boolean => {
  return (
    typeof value === 'undefined' ||
    value === null ||
    Array.isArray(value) ||
    typeof value === 'string'
  );
};

const identifierConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getIdentifierConditionModule: ConditionModuleGuesser = json => {
  if (!identifierConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(IdentifierConditionLine);
};

const createIdentifierCondition: ConditionFactory = (
  fieldCode: any
): Promise<IdentifierCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<IdentifierCondition>({
    field: FIELD,
    operator: IdentifierOperators[0],
    value: '',
  });
};

export {
  IdentifierCondition,
  createIdentifierCondition,
  IdentifierOperators,
  getIdentifierConditionModule,
};
