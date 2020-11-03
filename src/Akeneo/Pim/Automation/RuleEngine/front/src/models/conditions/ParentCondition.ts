import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {ParentConditionLine} from '../../pages/EditRules/components/conditions/ParentConditionLine';
import {ProductModelCode} from '../ProductModel';

const FIELD = 'parent';

const ParentOperators = [
  Operator.IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type ParentCondition = {
  field: string;
  operator: Operator;
  value: ProductModelCode[];
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    ParentOperators.includes(operator as Operator)
  );
};

const jsonValueIsValid = (value: any): boolean => {
  return typeof value === 'undefined' || value === null || Array.isArray(value);
};

const parentConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getParentConditionModule: ConditionModuleGuesser = json => {
  if (!parentConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(ParentConditionLine);
};

const createParentCondition: ConditionFactory = (
  fieldCode: any
): Promise<ParentCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<ParentCondition>({
    field: FIELD,
    operator: ParentOperators[0],
    value: [],
  });
};

export {
  ParentCondition,
  createParentCondition,
  ParentOperators,
  getParentConditionModule,
};
